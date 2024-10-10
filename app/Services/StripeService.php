<?php

namespace App\Services;

use App\Classes\Enums\StatusEnum;
use App\Jobs\CreateOneTimeWeeklyClasses;
use App\Jobs\SendNewSubscriptionEmails;
use App\Models\Coupon;
use App\Models\RoutineClass;
use App\Models\StripeCard;
use App\Models\Student;
use App\Models\StudentCourse;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Repository\Interfaces\RoutineClassRepositoryInterface;
use App\Repository\Interfaces\StripeRepositoryInterface;
use App\Repository\Interfaces\SubscriptionRepositoryInterface;
use App\Repository\NotificationRepository;
use App\Repository\StudentCoursesRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StripeService
{
    private $stripeRepository;
    private $subscriptionRepository;
    private $routineClassRepository;
    private $notificationRepository;

    public function __construct(
        StripeRepositoryInterface $stripeRepository,
        RoutineClassRepositoryInterface $routineClassRepository,
        SubscriptionRepositoryInterface $subscriptionRepository,
        NotificationRepository $notificationRepository
    )
    {
        $this->stripeRepository = $stripeRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->routineClassRepository = $routineClassRepository;
        $this->notificationRepository = $notificationRepository;

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    /**
     * Add card to stripe
     * 
     * @param string $customerId
     * @param string $token
     * @return mixed
     */
    public function addCard($customerId, $token)
    {
        return $this->stripeRepository->createCard($customerId, $token);
    }

    /**
     * Create customer and subscription
     * and update student course with subscription id
     * 
     * @param int $userId
     * @param string $planId
     * @param int $studentId
     * @return mixed
     */
    public function createCustomerAndSubscription(
        $userId, $planId, $studentId, 
        $couponCode, $studentCourseId,
        $availability_slot_ids, $teacherId, 
        $stripePlan
    )
    {
        $user = User::find($userId);

        // Check if coupone code applied
        if ($couponCode) 
        {
            $coupon = Coupon::selectCoupon(['id','used'], ['code' => $couponCode])->first();
            if ($coupon) {
                $coupon->increment('used');

                $coupon->usersAndStudents()->attach($coupon->id, [
                    'user_id' => $user->id,
                    'student_id' => $studentId
                ]);
            }
        }

        // create subscription
        $subscription = $this->stripeRepository->createSubscription($user, $planId, $studentId, $couponCode);

        // update student course with subscription id
        $studentCourseRepository = new StudentCoursesRepository(new StudentCourse());
        $studentCourseRepository->updateStudentCourse(
            $studentCourseId, ['subscription_id' => $subscription->id, 'teacher_id' => $teacherId]
        );

        // create routine, weekly classes
        $availability_slot_ids = $availability_slot_ids;
        $this->createClassSchedule($availability_slot_ids, $studentId, $teacherId, $studentCourseId);

        // send email to: 
        // customer,teacher,coordinator,scheduling,support
        $teacher = User::find($teacherId);
        $course  = StudentCourse::find($studentCourseId)->course->title;
        $student = Student::find($studentId);
        $this->SendNewSubscriptionEmails($student, $teacher, $course, $stripePlan);

        return $subscription;
    }

    /**
     * Create routine, weekly classes
     * Send email to: customer,teacher,coordinator
     * @param $slots
     * @param $studentID
     * @param $teacherID
     * @param $studentCourseId
     * @return string
     */
    public function createClassSchedule($slots, $studentID, $teacherID, $studentCourseId)
    {
        $student = Student::find($studentID);

        $existingSlots = $this->routineClassRepository->getStudentRoutineClassesForSlotIds($studentID, $slots);

        $classes = [];
        $slotIDS = [];

        foreach ($slots as $slot) {
            if (!$existingSlots->contains($slot)) {
                $classData = [
                    'student_id' => $student->id,
                    'teacher_id' => $teacherID,
                    'slot_id' => $slot,
                    'student_course_id' => $studentCourseId,
                    'status' => StatusEnum::Active,
                ];
        
                RoutineClass::firstOrCreate($classData);
        
                $slotIDS[] = $slot;
            }
        }

        if (!empty($classData))
        {
            $details = [
                'user_id' => $student->user->id,
                'student_id' => $student->id,
                'slot_ids' => $slotIDS,
                'course_id' => $studentCourseId,
            ];

            CreateOneTimeWeeklyClasses::dispatch($details);
        }

        $student->update(['subscription_status' => StatusEnum::SubscriptionActive, 'is_subscribed' => 1]);
        
        return true;
    }

    /**
     * Send email : customer,teacher,coordinator,scheduling,support
     * Generate notification to student
     * 
     * @param Student $student
     * @param Teacher $teacher
     * @param string $course
     * @param string $stripePlan
     */
    public function SendNewSubscriptionEmails($student, $teacher, $course, $stripePlan)
    {
        $emailData = [
            'customer_name' => $student->user->name,
            'customer_email' => $student->user->email,
            'student_name' => $student->name,
            'course' => $course,
            'stripe_plan' => $stripePlan,
            'teacher_name' => $teacher->name,
            'teacher_email' => $teacher->email,
            'coordinator_name' => $teacher->teacherCoordinator->name,
            'coordinator_email' => $teacher->teacherCoordinator->email,
        ];

        // Send email to customer and teacher, coordinator
        dispatch(new SendNewSubscriptionEmails($emailData));

        // Create a new notification
        $notification = [
            'user_id' => $student->user->id,
            'student_id' => $student->id,
            'type' => 'subscription',
            'read' => false,
            'message' => "You have successfully subscribed to the {$course} for {$student->name}.Your classes will start soon."
        ];

        $this->notificationRepository->create($notification);
    }

    /**
     * Check if coupon is valid or not.
     * Check if user already used coupon.
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function validateCoupon($couponCode)
    {
        $coupon = Coupon::where('code', $couponCode)->first();

        // Check if the user already has an active subscription
        //$hasActiveSubscription = $this->subscriptionRepository->hasActiveSubscription($studentId);

        // if($hasActiveSubscription) {
        //     return ['success' =>  false, 'message' => 'You already have an active subscription.'];
        // }

        if (!$coupon || $coupon->isExpired() || $coupon->isUsageLimitReached()) {
            return ['success' =>  false, 'message' => 'Invalid or expired coupon code.'];
        }

        $user = Auth::user();

        if ($user->usedCoupons()->where('coupon_id', $coupon->id)->exists()) {
            return ['success' =>  false, 'message' => 'This coupon has already been used.'];
        }

        return [
            'coupon_code' => $couponCode,
            'type' => $coupon->type,
            'value' => $coupon->value,
        ];
    }

    /**
     * Get stripe card list for a customer
     * 
     * @param string $customerId
     * @return array
     */
    public function stripeCardList($customerId) {
        $cards = StripeCard::where('customer_id', $customerId)->orderByDesc('is_default')->get();
        
        $stripeCards = array();
        foreach($cards as $c) {
            $stripeCards[] = array(
                'id' => $c->id, 
                'last4' => $c->last4,
                'expiry' => $c->getExpDateAttribute(),
                'is_default' => ($c->is_default == 1? true: false),
                'stripe_card_id' => $c->stripe_card_id
            );
        }

        return $stripeCards;

    }

    /**
     * Make card default
     * 
     * @param string $customerId
     * @param string $cardId
     * @return mixed
     */
    public function makeCardDefault($customerId, $cardId, $customerStripeId) 
    {
        // Check if the card is already the default card
        $isDefault = StripeCard::where([
            'stripe_card_id' => $cardId,
            'is_default' => 1
        ]
        )->exists();

        if ($isDefault) {
            return [
                'error' => 'This card is already the default card.'
            ];
        }

        \Stripe\Customer::update(
            $customerStripeId,
            ['invoice_settings' => ['default_payment_method' => $cardId]]
        );

        $condition = array('customer_id' => $customerId, 'is_default' => 1);
        $stripeCard = StripeCard::where($condition);
        $stripeCard->update(['is_default' => 0]);

        $stripeCard = StripeCard::where('stripe_card_id', $cardId)->first();
        $stripeCard->update(['is_default' => 1]);

        return [
            'id' => $stripeCard->id, 
            'last4' => $stripeCard->last4,
            'expiry' => $stripeCard->getExpDateAttribute(),
            'is_default' => ($stripeCard->is_default == 1? true: false),
            'stripe_card_id' => $stripeCard->stripe_card_id
        ];
    }

    /**
     * Delete stripe card
     * 
     * @param string $customerId
     * @param string $cardId
     * @param string $stripeCardId
     * @return array
     */
    public function deleteStripeCard($cardId, $stripeCardId) 
    {
        $stripeCard = null;

        $paymentMethod = \Stripe\PaymentMethod::retrieve($cardId);
        $stripeCard = StripeCard::where('stripe_card_id', $cardId)->get()->first();

        /**
         * if stripe card is not default then delete it
         * otherwise customer has to make another card as default and delete this card
         */
        if($stripeCard->is_default != 1) {
            $paymentMethod->detach();
            $id = $stripeCard->id;
            StripeCard::find($id)->delete();

           return array(
                "id" => $stripeCard->id,
                "customer_id" => $stripeCard->customer_id,
                "stripe_card_id" => $stripeCard->stripe_card_id,
                "is_default" => $stripeCard->is_default
            );
        }

        return [
            'error' => 'This card is already the default card, you can not delete it.'
        ];
    }

    /**
     * Get subscription plan
     * 
     * @param string $stripe_plan_id
     * @return SubscriptionPlan
     */
    public function getSubscriptionPlan($stripe_plan_id)
    {
        return SubscriptionPlan::where('stripe_plan_id', $stripe_plan_id)->first();
    }

    public function applyCoupon($subscriptionId, $couponCode)
    {
        return $this->stripeRepository->applyCoupon($subscriptionId, $couponCode);
    }

    public function getCoupons()
    {
        return $this->stripeRepository->getCoupons();
    }

    public function getCouponCancelSubscription() {
        return $this->stripeRepository->getCouponCancelSubscription();
    }
}