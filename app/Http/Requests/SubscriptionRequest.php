<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SubscriptionRequest
{
    /**
     * Validate the schedule subscription cancellation
     * for a student
     * 
     * @param Request $request (customerId, stripeToken)
     * @return Request
     */
    public function validateScheduleSubscriptionCancellation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'student_course_id' => 'required|integer|exists:student_courses,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    /**
     * Validate the update subscription request
     * 
     * @param Request $request (student_id, availability_slots, new_plan_id)
     * @return Request
     */
    public function validateUpdateSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'availability_slot_ids' => 'required|array|exists:availability_slots,id',
            'subscription_id' => 'required|string|exists:subscriptions,sub_id',
            'course_id' => 'required|integer|exists:courses,id',
            'new_plan_id' => 'nullable|string|exists:subscription_plans,stripe_plan_id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateSingleInvoiceDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }
}
