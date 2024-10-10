<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscriptionRequest;
use App\Models\Subscription;
use App\Repository\Interfaces\SubscriptionRepositoryInterface;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    private $subscriptionService;
    private $subscriptionRequest;
    private $subscriptionRepository;

    public function __construct(
        SubscriptionService $subscriptionService,
        SubscriptionRequest $subscriptionRequest,
        SubscriptionRepositoryInterface $subscriptionRepository
    )
    {
        $this->subscriptionService = $subscriptionService;
        $this->subscriptionRequest = $subscriptionRequest;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * Get all subscription plans
     * 
     * @param Request $request
     * @return Response
     */
    public function subscriptionPlans(Request $request) {
        $data = $this->subscriptionService->subscriptionPlans();
        return $this->success($data);
    }

    /**
     * Get the enrollment plans of a customer
     * 
     * @param Request $request
     * @return Response
     */
    public function enrollmentPlans(Request $request) {
        $user = Auth::user();
        if ($user) {
            $customerId = $user->id;
            $data = $this->subscriptionService->enrollmentPlans($customerId);
            return $this->success($data, 'Customer/students enrollment plans', 200);
        }
    }

    /**
     * Schedule subscription cancellation at the end of the billing period
     * 
     * @param Request $request (student_id, student_course_id)
     * @return Response
     */
    public function scheduleSubscriptionCancellation(Request $request) 
    {
        $request = $this->subscriptionRequest->validateScheduleSubscriptionCancellation($request);

        $student_id = $request->student_id;
        $student_course_id = $request->student_course_id;

        $data = $this->subscriptionRepository->scheduleCancellationAtPeriodEnd($student_id, $student_course_id);
        
        // check if data has error
        if (isset($data['error'])) {
            return $this->error($data['error'], 400);
        }

        return $this->success($data, 'Subscription will be cancelled at the end of your billing period.', 200);
    }

    /**
     * Update the subscription plan for a student's course
     * 
     * @param Request $request (student_id, availability_slots, subscription_id, course_id, new_plan_id)
     * @return Response
     */
    public function updateSubscription(Request $request) 
    {
        $request = $this->subscriptionRequest->validateUpdateSubscription($request);

        $studentID = $request->student_id;
        $studentSlots = $request->availability_slot_ids;
        $courseId = $request->course_id;
        $subscriptionId = $request->subscription_id;
        $newPlanID = $request->new_plan_id;
        
        $this->subscriptionService->updateSubscription($studentID, $studentSlots, $courseId, $subscriptionId, $newPlanID);

        return $this->success([], 'Subscription updated successfully.', 201);
    }

}
