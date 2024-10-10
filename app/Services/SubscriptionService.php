<?php
namespace App\Services;

use App\Jobs\SendMailToCustomerOnsubscriptionUpdate;
use App\Models\Course;
use App\Models\Student;
use App\Models\StudentCourse;
use App\Models\SubscriptionPlan;
use Stripe\Customer;
use App\Repository\Interfaces\InvoicesRepositoryInterface;
use App\Repository\Interfaces\StripeRepositoryInterface;
use App\Repository\Interfaces\SubscriptionRepositoryInterface;
use Illuminate\Http\Request;

class SubscriptionService
{
    private $invoiceRepository;
    private $subscriptionRepository;
    private $stripeRespository;
    private $stripeService;

    public function __construct(
        InvoicesRepositoryInterface $invoiceRepositoryInterface, 
        SubscriptionRepositoryInterface $subscriptionRepositoryInterface,
        StripeRepositoryInterface $stripeRepositoryInterface,
        StripeService $stripeService
    )
    {
        $this->invoiceRepository = $invoiceRepositoryInterface;
        $this->subscriptionRepository = $subscriptionRepositoryInterface;
        $this->stripeRespository = $stripeRepositoryInterface;
        $this->stripeService = $stripeService;
    }

    /**
     * Get all subscription plans
     * we are converting the price to cents by multiplying by 100 
     * as stripe requires the price in cents
     * 
     * @return array
     */
    public function subscriptionPlans() {
        $plans = SubscriptionPlan::orderBy('id')->get();
        $data = array();
        foreach($plans as $p) {
            $data[] = array(
                'id' => $p->id,
                'title' => $p->title,
                'description' => $p->description,
                'us_price' => $p->us_price * 100,
                'stripe_plan_id' => $p->stripe_plan_id
            );
        }
        return $data;
    }

    /**
     * Get the transaction history of a customer
     * 
     * @param int $customerId
     * @return array
     */
    public function transactionHistory($customerId) {
        return $this->invoiceRepository->getTransactionHistory($customerId);
    }

    /**
     * Get the enrollment plans of a customer
     * 
     * @param int $customerId
     * @return array
     */
    public function enrollmentPlans($customerId) {
        return $this->subscriptionRepository->enrollmentPlans($customerId);
    }

    /**
     * Update the subscription plan for a student' course
     * 
     * @param int $studentId
     * @param array $studentSlots
     * @param int $courseId
     * @param int $subscriptionId
     * @param int $newPlanId
     * 
     */
    public function updateSubscription($studentId, $studentSlots, $courseId, $subscriptionId, $newPlanId) 
    {
        $student = Student::find($studentId);
        $studentCourse  = StudentCourse::where(['student_id' => $studentId, 'course_id' => $courseId])->first();
        $teacherId = $studentCourse->teacher_id;

        $details = [
            'customerName' => $student->user->name,
            'customerEmail' => $student->user->email,
            'student' => $student->name,
            'subscriptionPlan' => $studentCourse->subscription->subscriptionPlan->title,
            'course' => $studentCourse->course->title,
        ];

        if ($newPlanId) 
        {
            // upgrade/downgrade subscription
            $this->stripeRespository->upgradeDowngradeSubscription($subscriptionId, $newPlanId);

            // update subscription in db
            $this->subscriptionRepository->updateSubscription($subscriptionId, $newPlanId);
        }

        // remove previous routine, weekly classes
        $this->subscriptionRepository->previousClassSchedule($studentId, $studentSlots);

        // create new routine, weekly classes
        $this->stripeService->createClassSchedule($studentSlots, $studentId, $teacherId, $studentCourse->id);

        // send email and notify customer about subscription update
        dispatch(new SendMailToCustomerOnsubscriptionUpdate($details));

        return true;
    }

    public function singleInvoiceDetails($customerId, $invoiceId) {
        return $this->invoiceRepository->getSingleInvoiceDetails($customerId, $invoiceId);
    }
    
}
