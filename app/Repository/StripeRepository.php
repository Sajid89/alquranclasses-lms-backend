<?php
namespace App\Repository;

use App\Models\Coupon;
use App\Models\StripeCard;
use App\Models\Subscription as ModelsSubscription;
use App\Models\User;
use App\Repository\Interfaces\StripeRepositoryInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\AssignOp\Mod;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;

class StripeRepository implements StripeRepositoryInterface
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    /**
     * Create a new customer
     * 
     * @param string $name
     * @param string $email
     * @return Customer
     */
    public function createCustomer($name, $email)
    {
        $customer = Customer::create([
            'name' => $name,
            'email' => $email,
        ]);

        return $customer;
    }
    
    /**
     * Check if customer exists in stripe
     * if no create a new customer
     * Create a new card
     * 
     * @param string $customerId
     * @param string $token
     * @return StripeCard
     */
    public function createCard($customerId, $token)
    {
        $user = User::find($customerId);

        if (!$user->stripe_id) {
            $customer = $this->createCustomer($user->name, $user->email);

            $user->stripe_id = $customer->id;
            $user->save();
        }
        else {
            $customer = \Stripe\Customer::retrieve($user->stripe_id);
        }

        $paymentMethod = \Stripe\PaymentMethod::create([
            'type' => 'card',
            'card' => ['token' => $token],
        ]);

        $paymentMethod->attach(['customer' => $customer->id]);

        // Check if the customer already has a card
        $isDefault = 1;
        if (StripeCard::where('customer_id', $customerId)->exists()) {
            $isDefault = 0;
        }

        $newCard = new StripeCard([
            'customer_id' => $customerId,
            'stripe_card_id' => $paymentMethod->id,
            'brand' => $paymentMethod->card->brand,
            'last4' => $paymentMethod->card->last4,
            'exp_month' => $paymentMethod->card->exp_month,
            'exp_year' => $paymentMethod->card->exp_year,
            'is_default' => $isDefault,
        ]);

        $newCard->save();

        // If this is the user's first card, make it the default
        if ($isDefault == 1) {
            \Stripe\Customer::update($customer->id, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethod->id,
                ],
            ]);
        }

        return $newCard;
    }

    /**
     * Create a new subscription
     * 
     * @param string $customerId
     * @param string $planId
     * @param string $trialEnd
     * @return Subscription
     */
    public function createSubscription($user, $planId, $studentId, $couponCode, $trialEnd = null)
    {
        // create subscription in stripe
        $subscriptionData = [
            'customer' => $user->stripe_id,
            'items' => [
                [
                    'price' => $planId,
                    'quantity' => 1,
                ],
            ],
            //'trial_end' => $trialEnd,
        ];

        // Add the coupon to the subscription if provided
        if ($couponCode) {
            $subscriptionData['coupon'] = $couponCode;
        }

        $subscription = Subscription::create($subscriptionData);

        // create subscription in the database
        return ModelsSubscription::create([
            'user_id' => $user->id,
            'student_id' => $studentId,
            'planID' => $planId,
            'payment_status' => env('APP_ENV') === 'local' ? 'succeeded' : 'pending',
            'price' => $subscription->plan->amount,
            'quantity' => $subscription->quantity,
            'start_at' => $subscription->current_period_start,
            'ends_at' => $subscription->current_period_end,
            'payer_id' => $user->stripe_id,
            'sub_id' => $subscription->id,
        ]);
    }

    /**
     * Cancel a subscription
     * 
     * @param string $subscriptionId
     * @return void
     */
    public function cancelSubscription($subscriptionId)
    {
        Subscription::update(
            $subscriptionId,
            ['cancel_at_period_end' => true]
        );
    }

    /**
     * Update subscription
     * 
     * @param string $subscriptionId
     * @param string $newPlanId
     * @return void
     */
    public function upgradeDowngradeSubscription($subscriptionId, $newPlanId)
    {
        $subscription = \Stripe\Subscription::retrieve($subscriptionId);

        if (count($subscription->items->data) > 0) {
            $subscriptionItemId = $subscription->items->data[0]->id;

            \Stripe\Subscription::update(
                $subscriptionId,
                [
                    'proration_behavior' => 'create_prorations',
                    'items' => [
                        [
                            'id' => $subscriptionItemId,
                            'price' => $newPlanId,
                        ],
                    ]
                ]
            );
        } else {
            return response()->json(['error' => 'No subscription item found'], 400);
        }
    }
    
    public function applyCoupon($subscriptionId, $couponCode)
    {
        try {
            $user = Auth::user();
            $studentSubscription = ModelsSubscription::where('sub_id', $subscriptionId)->firstOrFail();
            $studentId = $studentSubscription->student_id;
            $updatedSubscription = Subscription::update($subscriptionId, [
                'coupon' => $couponCode
            ]);

            // to add coupon usage
            $coupon = Coupon::selectCoupon(['id','used'], ['code' => $couponCode])->first();
            if ($coupon) {
                $coupon->increment('used');

                $coupon->usersAndStudents()->attach($coupon->id, [
                    'user_id' => $user->id,
                    'student_id' => $studentId
                ]);
            }

            return true;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getCoupons()
    {
        $date = date('Y-m-d');
        $query = "call spCouponsExceptCancellation('$date');";
        $data = [];
        try {
            $coupons = DB::select($query);
            foreach($coupons as $coupon) {
                $data[] = [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                    'usage_limit' => $coupon->usage_limit,
                    'for_subscription_cancellation' => $coupon->for_subscription_cancellation,
                    'used' => $coupon->used
                ];
            }
        } catch (QueryException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
        return $data;
    }

    public function getCouponCancelSubscription() {
        $coupons = Coupon::where('for_subscription_cancellation', 1)->get();
        return $coupons;
    }

    /**
     * Cancel a subscription immediately
     * 
     * @param string $subscriptionId  stripe subscription id
     * @return Subscription
     */
    public function cancelSubscriptionImmediately($subscriptionId)
    {
        try {
            $subscription = \Stripe\Subscription::retrieve($subscriptionId);
            
            // Create a charge for the cancellation fee
            $charge = \Stripe\Charge::create([
                'amount' => 500, // Amount is in cents
                'currency' => 'usd',
                'customer' => $subscription->customer, // Customer ID from the subscription
                'description' => 'Cancellation fee',
            ]);
            if (!$charge->paid) {
                return response()->json(['error' => 'Charge failed'], 400);
            }
            $subscription->cancel();
            return $subscription;

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

}