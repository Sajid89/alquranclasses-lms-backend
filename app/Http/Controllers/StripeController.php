<?php

namespace App\Http\Controllers;

use App\Http\Requests\StripeRequest;
use App\Models\Coupon;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\StripeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    protected $stripeService;
    protected $stripeRequest;

    public function __construct(
        StripeService $stripeService,
        StripeRequest $stripeRequest
    )
    {
        $this->stripeService = $stripeService;
        $this->stripeRequest = $stripeRequest;
    }

    /**
     * Add card to stripe
     * 
     * @param Request $request
     * @param StripeRequest $stripeRequest(customerId, stripeToken)
     * @return JsonResponse
     */
    public function addCard(Request $request)
    {
        $this->stripeRequest->validateAddCard($request);

        $customerId = Auth::id();
        $token = $request->input('stripe_token');

        DB::beginTransaction();

        try
        {
            $card = $this->stripeService->addCard($customerId, $token);
            DB::commit();

            return $this->success($card, 'Card added successfully', 201);
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            Log::error('Exception at ' . $request->url() . ': ' . $e);
            return $this->error('An error while adding new card ', 400);
        }
    }

    /**
     * Check if coupon is valid or not.
     * Check if user already used coupon.
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function validateCoupon(Request $request)
    {
        $this->stripeRequest->validateCoupon($request);

        //$studentId = $request->input('student_id');
        $couponCode = $request->input('coupon_code');
        
        $coupon = $this->stripeService->validateCoupon($couponCode);

        if (isset($coupon['success']) && $coupon['success'] === false) {
            return $this->error($coupon['message'], 400);
        }

        return $this->success($coupon, 'Coupon validated successfully', 200);
    }

    /**
     * Create customer and subscription
     * 
     * @param Request $request
     * @param StripeRequest $stripeRequest(plan_id, user_id, student_id)
     * @return JsonResponse
     */
    public function createCustomerAndSubscription(Request $request)
    {
        $this->stripeRequest->validateCreateCustomerAndSubscription($request);

        try
        {
            DB::beginTransaction();
        
            $planId = $request->input('plan_id');
            $userId = $request->input('user_id');
            $studentId = $request->input('student_id');
            $couponCode = $request->input('coupon_code');
            $studentCourseId = $request->input('student_course_id');
            $availability_slot_ids = $request->input('availability_slot_ids');
            $teacherId = $request->input('teacher_id');
            $stripePlan = $request->input('stripe_plan');

            $subscription = $this->stripeService->createCustomerAndSubscription(
                $userId, $planId, $studentId, $couponCode, $studentCourseId,
                $availability_slot_ids, $teacherId, $stripePlan
            );
            DB::commit();

            return $this->success($subscription, 'Subscription created successfully', 200);
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            return $this->error('Exception while creating new subscription '.$e->getMessage(), 500);
        }
    }

    /**
     * Get stripe card list for a customer
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function stripeCardList(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $customerId = $user->id;
            $cards = $this->stripeService->stripeCardList($customerId);
            return $this->success($cards, 'Card list fetched successfully', 200);
        }

        return $this->error('User not found', 404);
    }

    /**
     * Make card default
     * 
     * @param Request $request
     * @param StripeRequest $stripeRequest(card_id)
     * @return JsonResponse
     */
    public function makeCardDefault(Request $request)
    {
        $this->stripeRequest->validateMakeCardDefault($request);

        $user = Auth::user();

        if ($user) {
            $customerId = $user->id;
            $customerStripeId = $user->stripe_id;
            $cardId = $request->card_id;
            $card = $this->stripeService->makeCardDefault($customerId, $cardId, $customerStripeId);
            
            if (isset($card['error']))
            {
                return $this->error($card['error'], 400);
            }

            return $this->success($card, 'Card made default successfully', 201);
        }

        return $this->error('User not found', 404);
    }

    /**
     * Delete stripe card
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteStripeCard(Request $request) 
    {
        $this->stripeRequest->validateDeleteStripeCard($request);
        $user = Auth::user();

        if ($user) {
            $stripeCustomerId = $user->stripe_id;
            $cardId = $request->card_id;
            $data = $this->stripeService->deleteStripeCard($cardId, $stripeCustomerId);
            
            if (isset($data['error']))
            {
                return $this->error($data['error'], 400);
            }
            
            return $this->success($data, 'Card deleted successfully', 200);
        }

        return $this->error('User not found', 404);
    }

    public function applyCoupon(Request $request)
    {
        $this->stripeRequest->validateCoupon($request);

        $couponCode = $request->input('coupon_code');
        $subscriptionId = $request->input('subscription_id');

        $coupon = $this->stripeService->applyCoupon($subscriptionId, $couponCode);

        if ($coupon['error']) {
            return $this->error($coupon['error'], 400);
        }

        return $this->success([], 'Coupon applied successfully.', 200);
    }
 
    public function getCoupons(Request $request)
    {
        $coupons = $this->stripeService->getCoupons();
        return $this->success($coupons, 'Coupons fetched successfully', 200);
    }

    public function getCouponCancelSubscription(Request $request) {
        $coupons = $this->stripeService->getCouponCancelSubscription();
        return $this->success($coupons, 'Coupon in case of cancel subscription fetched successfully', 200);
    }
}