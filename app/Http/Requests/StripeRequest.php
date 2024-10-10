<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StripeRequest
{
    /**
     * Validate the add new card request
     * 
     * @param Request $request (customerId, stripeToken)
     * @return Request
     */
    public function validateAddCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'customer_id' => 'required|integer|exists:users,id',
            'stripe_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    /**
     * Validate the create customer and subscription request
     * 
     * @param Request $request (plan_id, user_id, student_id)
     * @return Request
     */
    public function validateCreateCustomerAndSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|string|exists:subscription_plans,stripe_plan_id',
            'user_id' => 'required|integer|exists:users,id',
            'student_id' => 'required|integer|exists:students,id',
            'coupon_code' => 'nullable|string',
            'student_course_id' => 'required|integer|exists:student_courses,id',
            'teacher_id' => 'required|integer|exists:users,id',
            'availability_slot_ids' => 'required|array|exists:availability_slots,id',
            'stripe_plan' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    /**
     * Validate the validate coupon request
     * 
     * @param Request $request (coupon_code, student_id)
     * @return Request
     */
    public function validateCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|string|exists:coupons,code',
            //'student_id' => 'required|integer|exists:students,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    /**
     * Validate the make card default request
     * 
     * @param Request $request (card_id)
     * @return Request
     */
    public function validateMakeCardDefault(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_id' => 'required|string|exists:stripe_cards,stripe_card_id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    /**
     * Validate the delete stripe card request
     * 
     * @param Request $request (card_id)
     * @return Request
     */
    public function validateDeleteStripeCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_id' => 'required|string|exists:stripe_cards,stripe_card_id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateApplyCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscription_id' => 'required|string|exists:subscriptions,id',
            'coupon_code' => 'required|string|exists:coupons,code',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }
}
