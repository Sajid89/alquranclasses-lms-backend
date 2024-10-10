<?php

namespace App\Repository;

use App\Classes\Enums\StatusEnum;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Repository\Interfaces\RoutineClassRepositoryInterface;
use App\Repository\Interfaces\StripeRepositoryInterface;
use App\Repository\Interfaces\SubscriptionRepositoryInterface;
use App\Traits\DecryptionTrait;
use Exception;
use Illuminate\Support\Facades\DB;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    private $model;
    private $stripeRepository;
    private $routineClassRepository;
    use DecryptionTrait;

    public function __construct(
        Subscription $subscription,
        StripeRepositoryInterface $stripeRepository,
        RoutineClassRepositoryInterface $routineClassRepository
    )
    {
        $this->model = $subscription;
        $this->stripeRepository = $stripeRepository;
        $this->routineClassRepository = $routineClassRepository;
    }

    /**
     * Get the Active subscription details of a student
     * from the database
     *
     * @param $studentId
     * @return mixed
     */
    public function hasActiveSubscription($studentId)
    {
        return $this->model::where([
            'student_id' => $studentId,
            'payment_status' => 'succeeded',
        ])->exists();
    }

    /**
     * get the enrollment plans of all students of the 
     * logged in customer with details like plan type,
     * status, start date, end date, next billing, price etc.
     */
    public function enrollmentPlans($customerId) 
    {
        $query = "CALL spEnrollmentPlan($customerId);";
        $resultSet = DB::select($query);

        $data = array();
        foreach($resultSet as $r) {
            $data[] = array(
                'student_id' => $r->id,
                'name' => $this->decryptValue($r->name),
                'gender' => $r->gender,
                'is_subscribed' => $r->is_subscribed,
                'profile_photo_url' => $r->profile_photo_url,
                'course_level' => $r->course_level,
                'subscription_id' => $r->subscription_id,
                'course_title' => $r->course_title,
                'subscription_plan_title' => $r->subscription_plan_title,
                'price' => $r->price,
                'subscription_status' => $r->subscription_status,
                'start_at' => $r->start_at,
                'ends_at' => $r->ends_at
            );
        }
        
        return $data;
    }

    /**
     * Schedule subscription cancellation
     * at the end of current billing cycle
     *
     * @param $student_id
     * @throws Stripe\Exception\ApiErrorException
     */
    public function scheduleCancellationAtPeriodEnd($studentId, $studentCourseId)
    {
        $subscription = $this->model::where('student_id', $studentId)
            ->whereHas('course', function ($query) use ($studentCourseId) {
                $query->where('id', $studentCourseId);
            })
            ->first();

        // Check if subscription is retrieved successfully
        if (!$subscription) {
            return [
                'error' => "Subscription not found for student with ID: " . $studentId . " and student course ID: " . $studentCourseId,
            ];
        }

        // Check if the subscription is already scheduled for cancellation
        if ($subscription->status === 'pending_cancellation') {
            return [
                'error' => "Subscription is already scheduled for cancellation",
            ];
        }

        // Cancel the subscription
        $this->stripeRepository->cancelSubscription($subscription->sub_id);

        // Update the subscription status in the database
        $subscription->status = 'pending_cancellation';
        $subscription->save();

        return $subscription;
    }

    /**
     * If student update any of his previous slot
     * then remove previous: classes accordingly.
     * Remove change requests, cancel subscription requests
     * @param $studentID
     * @param $studentNewSlots
     * @return string
     */
    public function previousClassSchedule($studentID, $studentNewSlots)
    {
        $studentAllSlots = $this->routineClassRepository->getStudentRoutineClassesWithSlotIds($studentID);

        $studentNewSlots = array_map('intval', $studentNewSlots);
        $slotIDS = array_diff($studentAllSlots, $studentNewSlots);

        if (count($slotIDS) > 0)
        {
            $this->routineClassRepository->softDeleteRoutineWeeklyClasses($slotIDS);
        }

        return true;
    }

    /**
     * update the subscription plan of a student
     * 
     * @param $studentID
     * @param $newPlanId
     * @return Subscription
     */
    public function updateSubscription($subscriptionId, $newPlanId)
    {
        $subscription = $this->model::where('sub_id', $subscriptionId)->first();
        $subscriptionPlan = SubscriptionPlan::where('stripe_plan_id', $newPlanId)->first();

        $subscription->planID = $newPlanId;
        $subscription->price = $subscriptionPlan->us_price;
        $subscription->save();

        return $subscription;
    }

    /**
     * Remove a subscription
     * @param $subscriptionId
     */
    public function removeSubscription($subscriptionId) {
        $subscription = $this->model::where('sub_id', $subscriptionId)->first();
        $subscription->update(['status' => StatusEnum::SubscriptionCancelled]);
        $subscription->delete();
    }
}