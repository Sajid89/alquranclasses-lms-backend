<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TeacherRequest
{
    public function validateActiveStudentsRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'required|integer',
            'limit' => 'required|integer'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateStudentActivities(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'course_id' => 'required|integer|exists:courses,id',
            'page' => 'required|integer',
            'limit' => 'required|integer'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateWithdrawRequest(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'weekly_class_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateGetStudentsWithUnreadMessagesCount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateCreateMakeupRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|integer',
            'availability_slot_id' => 'required|integer',
            'makeup_date_time' => 'required|date_format:Y-m-d H:i:s',
            'class_type' => 'required|string',
            'student_id' => 'required|integer',
            'course_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validatePasswordUpdateRequest(Request $request)
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
            'confirm_password' => 'required|string|same:password'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

}