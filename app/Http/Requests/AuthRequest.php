<?php

namespace App\Http\Requests;

use App\Rules\UniqueEncryptedEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthRequest
{
    public function validate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z\s]*$/',
            ],
            'email' => ['required', 'email', 'unique:users'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
            'phone' => 'required|string|unique:users|min:10|max:15',
            'profile_photo_url' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'country'  => 'required|string',
            'timezone' => 'required|string|timezone',
        ]);

        if ($validator->fails()) {
            // Simply throw the ValidationException. The Handler will take care of the response format.
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateSignin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
            'device_id' => 'required|string',
            'device_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateRefreshToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateEmailVerify($token)
    {
        $validator = Validator::make(['token' => $token], [
            'token' => 'required|string|size:60',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function validateForgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateResetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }
}
