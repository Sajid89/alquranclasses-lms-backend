<?php

namespace App\Http\Controllers;

use App\Classes\Enums\StatusEnum;
use App\Jobs\SendPaymentFailedEmail;
use App\Jobs\SendPaymentUnsuccessNoitfy;
use App\Jobs\SendSubscriptionCancelledMailToCustomer;
use App\Jobs\SendSubscriptionFailedMail;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Subscription;
use App\Models\WeeklyClass;
use App\Repository\Eloquent\RoutineClassRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe;
use App\Models\Student;
use App\Models\RoutineClass;
use App\Models\User;
use App\Repository\Interfaces\RoutineClassRepositoryInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StripeWebHooksController extends Controller
{
    private $routineClassRepository;

    public function __construct(
        RoutineClassRepositoryInterface $routineClassRepository
    )
    {
        $this->routineClassRepository = $routineClassRepository;
    }

    public function GetWebHooksEvents(Request $request)
    {
        try
        {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');
            $sig_header = $request->header('stripe-signature');

            if ($sig_header) {
                $payload = $request->getContent();
                $event = \Stripe\Webhook::constructEvent(
                    $payload, $sig_header, $endpoint_secret
                );

                switch ($event->type) {
                    case 'charge.failed':
                        $this->failedSubscription($event->data->object);
                        break;

                    case 'customer.subscription.created':
                        $this->subscriptionCreated($event->data->object);
                        break;

                    case 'customer.subscription.updated':
                        $this->subscriptionUpdated($event->data->object);
                        break;

                    case 'charge.success':
                        $this->subscriptionUpdated($event->data->object, true);
                        break;

                    case 'customer.subscription.deleted':
                        $this->subscriptionCancelled($event->data->object);
                        break;

                    case 'invoice.created':
                        $this->handleInvoiceCreated($event['data']['object']);
                        break;

                    default:
                }

                return response()->json(['message' => 'Webhook handled'], 200);
            }
        }
        catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            Log::error("Stripe webhook signature verification failed: " . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }
        catch (\Exception $e) {
            // Other errors
            Log::error("Stripe webhook error: " . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Failed transaction
     *
     * @param $paymentIntent
     * @return bool
     */
    public function failedSubscription($paymentIntent)
    {
        $subscription = Subscription::where('sub_id', '=', $paymentIntent['id']);

        if (!$subscription) {
            Log::warning("From Stripe Web Hook(charge.failed) Attempted to update non-existent subscription with ID: {$paymentIntent['id']}");
            return false;
        }

        try {
            DB::transaction(function () use ($subscription) {
                $subscription->loadMissing(['student']);

                if (!$subscription->relationLoaded('student')) {
                    Log::warning("From Stripe Web Hook(charge.failed) Missing student for subscription ID: {$subscription->id}");
                    return false;
                }

                $subscriptionUpdates = [
                    'payment_status'    => "Failed",
                    'payment_failed_at' => Carbon::now(),
                ];

                $studentUpdates = [
                    'subscription_status' => StatusEnum::SubscriptionPendingPayment,
                    'is_subscribed' => 0,
                ];

                $this->updateSubscriptionAndStudent($subscription, $subscriptionUpdates, $studentUpdates);

                $emailData = [
                    'customer_name' => $subscription->course->student->user->name,
                    'customer_email' => $subscription->course->student->user->email,
                    'student_name' => $subscription->course->student->name,
                    'course_name' => $subscription->course->course->title,
                ];

                //Mail to customer
                dispatch(new SendPaymentFailedEmail($emailData));

                //Send Notification
                Notification::create([
                    'user_id' => $subscription->course->student->user->id,
                    'student_id' => $subscription->course->student->id,
                    'type' => 'payment_failed',
                    'read' => false,
                    'message' => "Payment failed for student {$subscription->course->student->name} with course {$subscription->course->course->title} on {$subscriptionUpdates['payment_failed_at']}."
                ]);

                return true;
            });
        } catch (\Exception $e) {
            Log::error("Error while handling subscription charge failed web hook: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Subscription created
     *
     * @param $stripeSubscription
     * @return bool|string
     */
    public function subscriptionCreated($stripeSubscription)
    {
        try {
            // Extract the Stripe Subscription ID
            $stripeSubscriptionId = $stripeSubscription->id ?? null;

            if (!$stripeSubscriptionId) {
                Log::error('Stripe subscription created Web Hook, Stripe Subscription ID not found in the event data.');
                return false;
            }

            // Find the corresponding subscription in your database
            $subscription = Subscription::where('sub_id', $stripeSubscriptionId)->first();

            if (!$subscription) {
                Log::error("Subscription not found for ID: {$stripeSubscriptionId}");
                return false;
            }

            DB::transaction(function () use ($stripeSubscription, $subscription) {
                $subscription->loadMissing(['student']);

                if (!$subscription->relationLoaded('student')) {
                    Log::warning("Missing student or user for subscription ID: {$subscription->id}");
                    return false;
                }

                // Update your subscription
                $subscriptionUpdates = [
                    'payment_id' => $stripeSubscription->latest_invoice,
                    'payment_status' => 'succeeded',
                ];

                $this->updateSubscriptionAndStudent($subscription, $subscriptionUpdates);

                return true;
            });
        } catch (\Exception $e) {
            Log::error("Error processing subscription payment: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Subscription updated
     *
     * @param $stripeSubscription
     * @param bool $isChargeSuccessEvent
     * @return bool|string
     */
    public function subscriptionUpdated($stripeSubscription, $isChargeSuccessEvent = false)
    {
        $stripeSubscriptionId = $stripeSubscription->id ?? null;

        if (!$stripeSubscriptionId) {
            Log::error('Stripe subscription updated Web Hook, Stripe Subscription ID not found in the event data.');
            return false;
        }

        $subscription = Subscription::where('sub_id', '=', $stripeSubscriptionId)->first();

        if (!$subscription) {
            Log::warning("From Stripe Web Hook(subscription.updated) Attempted to update non-existent subscription with ID: {$stripeSubscriptionId}");
            return false;
        }

        try {
            DB::transaction(function () use ($isChargeSuccessEvent, $stripeSubscription, $subscription) {
                $subscription->loadMissing(['student']);

                if (!$subscription->relationLoaded('student')) {
                    Log::warning("From Stripe Web Hook(subscription.updated) Missing student for subscription ID: {$subscription->id}");
                    return false;
                }

                $subscriptionUpdates = null;

                if ($isChargeSuccessEvent)
                {
                    $subscriptionUpdates = [
                        'payment_id' => $stripeSubscription->latest_invoice,
                        'payment_status' => 'succeeded',
                        'payment_failed_at' => null
                    ];
                }

                $studentUpdates = [
                    'subscription_status' => StatusEnum::SubscriptionActive,
                    'is_subscribed' => 1,
                ];

                $this->updateSubscriptionAndStudent($subscription, $subscriptionUpdates, $studentUpdates);
                return true;
            });
        } catch (\Exception $e) {
            Log::error("Error while handling subscription updated failed web hook: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Stripe cancelled Subscription
     *
     * @param $stripeObj
     * @return bool
     */
    public function subscriptionCancelled($stripeObj)
    {
        $subscription = Subscription::where('sub_id', $stripeObj['id'])->first();

        if (!$subscription) {
            Log::warning("Attempted to cancel non-existent subscription with ID: {$stripeObj['id']}");
            return false;
        }

        try {
            DB::transaction(function () use ($subscription) {
                $subscription->loadMissing(['student', 'user']);

                if (!$subscription->relationLoaded('student') || !$subscription->relationLoaded('user')) {
                    Log::warning("Missing student or user for subscription ID: {$subscription->id}");
                    return false;
                }

                $subscriptionUpdates = [
                    'status' => 'cancelled'
                ];

                $this->updateSubscriptionAndStudent($subscription, $subscriptionUpdates);
                $this->sendCancellationEmail($subscription);

                // Soft delete subscription
                $subscription->delete();

                // Soft delete routine/weekly classes
                $this->routineClassRepository->softDeleteRoutineWeeklyClasses([], $subscription->student->id);

                return true;
            });
        } catch (\Exception $e) {
            Log::error("Error processing subscription cancellation stripe: " . $e->getMessage());
            return false;
        }
    }

    private function updateSubscriptionAndStudent($subscription, $updates, $studentUpdates = null)
    {
        // Update the subscription if subscription-related updates are provided
        if ($updates)
        {
            $subscription->update($updates);
        }

        // Update the student if student-related updates are provided
        if ($studentUpdates) {
            $subscription->student->update($studentUpdates);
        }
    }

    private function sendCancellationEmail($subscription)
    {
        SendSubscriptionCancelledMailToCustomer::dispatch($subscription);
    }

    /**
     * Invoice Created
     *
     * @param $invoiceData
     */
    protected function handleInvoiceCreated($invoiceData) {
        try {
            // Extract necessary details from Stripe invoice data
            $stripeInvoiceId = $invoiceData['id'];
            $amount = $invoiceData['total'];
            $invoiceDate = Carbon::createFromTimestamp($invoiceData['created']);
            $subscriptionId = $this->getSubscriptionIdFromStripeId($invoiceData['subscription']);

            // Convert amount to a standard currency format if needed
            $formattedAmount = $amount / 100;

            // Extract line items (assuming line items are in the format you expect)
            $lineItems = json_encode($invoiceData['lines']['data']);

            // Check if the invoice already exists
            $invoice = Invoice::where('stripe_invoice_id', $stripeInvoiceId)->first();

            if ($invoice) {
                $invoice->update([
                    'amount' => $formattedAmount,
                    'invoice_date' => $invoiceDate,
                    'line_items' => $lineItems,
                ]);
            } else {
                Invoice::create([
                    'stripe_invoice_id' => $stripeInvoiceId,
                    'subscription_id' => $subscriptionId,
                    'amount' => $formattedAmount,
                    'invoice_date' => $invoiceDate,
                    'line_items' => $lineItems,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error processing StripeWebHook invoice: " . $e->getMessage());
        }
    }

    /**
     * Convert Stripe subscription ID to local subscription ID.
     * Implement this method based on your database relationships.
     * @param $stripeSubscriptionId
     * @return null
     */
    protected function getSubscriptionIdFromStripeId($stripeSubscriptionId) {
        $subscription = Subscription::where('sub_id', $stripeSubscriptionId)->first();
        return $subscription ? $subscription->id : null;
    }

}
