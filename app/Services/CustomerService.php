<?php
namespace App\Services;

use App\Classes\Enums\CommonEnum;
use App\Classes\Enums\UserTypesEnum;
use App\Helpers\GeneralHelper;
use App\Http\Resources\CustomerResource;
use App\Models\ParentalPinToken;
use App\Repository\Interfaces\CustomerRepositoryInterface;
use App\Repository\UserRepository;
use App\Repository\CustomerRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerService
{
    private $userRepository;
    private $customerRepository;
    public function __construct(UserRepository $userRepository, CustomerRepositoryInterface $customerRepositoryInterface)
    {
        $this->userRepository = $userRepository;
        $this->customerRepository = $customerRepositoryInterface;
    }

    /**
     * This method is used to update all fields of the authenticated customer if not empty
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateCustomerProfile(Request $request)
    {
        $data = [
            'name' => trim($request->name),
            'phone' => trim($request->phone)
        ];

        if(!empty($request->secondary_phone)) {
            $data['secondary_phone'] = $request->secondary_phone;
        }

        if(!empty($request->secondary_email)) {
            $data['secondary_email'] = $request->secondary_email;
        }

        if ($request->hasFile('profile_photo_path')) {
            $file = $request->file('profile_photo_path');
            $path = 'images/customer/profile';
            $data['profile_photo_path'] = GeneralHelper::uploadProfileImage($file, $path);
        }
        
        if($request->has('parental_lock')) {
            $data['parental_lock'] = $request->parental_lock;
        }
        
        if($request->has('parental_lock_pin')) {
            $data['parental_lock_pin'] = $request->parental_lock_pin;
        }

        
        if(!empty($request->new_password) && !empty($request->confirm_new_password)) {
            if($request->new_password == $request->confirm_new_password) {
                $data['password'] = Hash::make($request->new_password);
            }
        }

        $this->userRepository->updateUser($data);

        $user = Auth::user()->refresh();
        $customer = new CustomerResource($user);
        
        return $customer;

    }

    /**
     * This method is used to get the student profiles of the authenticated customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getStudentProfiles($customerId) {
        return $this->customerRepository->getStudentProfiles($customerId);
    }
    
    /**
     * This method is used to get the customer notifications.
     * If student_id is passed in the request, it will return the notifications for that student.
     *
     * @param int $userId
     * @param int $page
     * @param int $limit
     * @param int $studentId
     * @return array
     */
    public function getCustomerNotifications($userId, $page, $limit, $studentId)
    {
        $limit = $limit;
        $offset = ($page - 1) * $limit;
        return $this->customerRepository->getCustomerNotifications($userId, $limit, $offset, $studentId);
    }
    
    /**
     * This method is used to reset the parental pin of the authenticated customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resetParentalPin($newParentalLockPin, $token, $userId) {
        $currentDateTime = Carbon::now('UTC')->format('Y-m-d H:i:s');
        $result = ParentalPinToken::where('user_id', $userId)
            ->where('expired_at', '>', $currentDateTime)
            ->where('token', $token)
            ->get();
        if (sizeof($result) > 0) {
            $data = [
                'parental_lock_pin' => $newParentalLockPin,
                'parental_lock' => 1
            ];
            return $this->userRepository->updateUser($data);
        } else {
            return array('message' => 'Invalid token');
        }
    }
}