<?php
namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomerRequest
{
    public function validateSMS(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'to' => 'required|regex:/^\+[1-9]\d{1,14}$/',
            //'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'to' => 'required|regex:/^\+[1-9]\d{1,14}$/',
            'code' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    /**
     * Validate the customer profile request
     */
    public function validateCustomerProfile(Request $request, $customerId)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z\s]*$/',
            ],
            'phone' => [
                'required',
                'string',
                'min:10',
                'max:15',
                Rule::unique('users')->ignore($customerId)
            ],
            'profile_photo_path' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'parental_lock' => 'required|boolean',
            'parental_lock_pin' => 'nullable|string|min:4|max:4',
            'old_password' => 'nullable|string|min:8',
            'new_password' => 'nullable|string|min:8',
            'confirm_new_password' => 'nullable|string|min:8'
        ]); 
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $request;

    }
    
    /**
     * Validate the get customer notifications request
     */
    public function validateGetCustomerNotifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'required|numeric',
            'limit' => 'required|numeric|min:1|max:100',
            'student_id' => 'nullable|numeric|exists:students,id'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateResetParentalPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_parental_lock_pin' => 'required|string|min:4|max:4',
            'pin_token' => 'required|string|min:60|max:255'
            // 'pin_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }
}
