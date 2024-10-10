<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Resources\CustomerResource;
use App\Models\ParentalPinToken;
use App\Services\CustomerService as ServicesCustomerService;
use App\Services\TeacherService;
use App\Traits\DecryptionTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    protected $customerRequest;
    protected $customerService;
    private $teacherService;
    use DecryptionTrait;

    public function __construct(
        CustomerRequest $customerRequest, 
        ServicesCustomerService $customerService,
        TeacherService $teacherService
    )
    {
        $this->customerRequest = $customerRequest;
        $this->customerService = $customerService;
        $this->teacherService = $teacherService;
    }

    
    /**
     * This method is used to get the all fields of the authenticated customer 
     * to send to frontend to display in profile page/ edit profile page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function customerProfile(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            return $this->success(new CustomerResource($user));
        } else {
            return $this->error('Unauthenticated', 401);
        }

    }

    /**
     * This method is used to update the authenticated customer's profile.
     * It returns the updated customer profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateCustomerProfile(Request $request)
    {
        $user = Auth::user();
        $customer = null;
        if ($user) {
            $customerId = $user->id;
            $this->customerRequest->validateCustomerProfile($request, $customerId);
            
            //to update the customer profile
            DB::transaction(function () use ($request, &$customer) {
                $customer = $this->customerService->updateCustomerProfile($request);
            });
            return $this->success($customer);
        }
    }

    /**
     * This method is used to get the student profiles of the authenticated customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function studentProfiles(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $students = $this->customerService->getStudentProfiles($user->id);
            return $this->success($students);
        }
    }

    /**
     * This method is used to get the customer notifications.
     * If student_id is passed in the request, it will return the notifications for that student.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getCustomerNotifications(Request $request)
    {
        $this->customerRequest->validateGetCustomerNotifications($request);

        $user = Auth::user();
        $page = $request->page;
        $limit = $request->limit;
        $studentId = $request->has('student_id') ? $request->student_id : null;
        
        if ($user) {
            $notifications = $this->customerService
                ->getCustomerNotifications($user->id, $page, $limit, $studentId);
            return $this->success($notifications, 'Notifications fetched successfully');
        }
    }

    /**
     * This method is used to reset the parental pin of the authenticated customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resetParentalPin(Request $request) {
        $this->customerRequest->validateResetParentalPin($request);
        $user = Auth::user();
        if ($user) {
            $result = $this->customerService->resetParentalPin($request->new_parental_lock_pin, $request->pin_token, $user->id);
            return $this->success($result, 'Parental pin reset successfully');
        }
    }

    /**
     * This method is used to get the latest parental token of the authenticated customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getLatestParentalToken(Request $request) {
        $user = Auth::user();
        $currentDateTime = Carbon::now('UTC')->format('Y-m-d H:i:s');
        $data = array('recordsCount' => 0);
        if ($user) {
            $result = ParentalPinToken::where('user_id', $user->id)
                    ->where('expired_at', '>', $currentDateTime)
                    ->orderByDesc('id')
                    ->limit(1)
                    ->get();
            if (sizeof($result) > 0) {
                $data = array(
                    'recordsCount' => 1,
                    'user_id' => $user->id, 
                        'token' => $result[0]->token,
                        'created_at' => $result[0]->created_at,
                        'expired_at' => $result[0]->expired_at
                    );
                return $this->success($data, 'Latest parental token fetched successfully');
            }
        }
    }

    /**
     * Get teacher's of his students with unread messages count
     * for a customer
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getTeachersWithUnreadMessagesCount()
    {
        $customerId = Auth::user()->id;
        $teachers  = $this->teacherService->getTeachersWithUnreadMessagesCount($customerId);

        return $this->success($teachers, 'Teachers with unread messages count retrieved successfully');
    }
}