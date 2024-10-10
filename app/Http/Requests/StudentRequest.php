<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StudentRequest
{
    public function validateAddStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'age' => 'required|integer',
            'gender' => 'required|string',
            'timezone' => 'required|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'course_id' => 'required|integer|exists:courses,id',
            'course_level' => 'required|string',
            'teacher_id' => 'required|integer|exists:users,id',
            'teacher_preference' => 'required|string',
            'availability_slot_ids' => 'required|array|exists:availability_slots,id',
            'shift_id' => 'required|integer|exists:shifts,id',
            'stripe_plan_id' => 'nullable|string|exists:subscription_plans,stripe_plan_id',
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'is_trial_required' => 'required|boolean',
            'stripe_plan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateEnrollNewCourse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'course_id' => 'required|integer|exists:courses,id',
            'course_level' => 'required|string',
            'teacher_id' => 'required|integer|exists:users,id',
            'teacher_preference' => 'required|string',
            'availability_slot_ids' => 'required|array|exists:availability_slots,id',
            'shift_id' => 'required|integer|exists:shifts,id',
            'stripe_plan_id' => 'nullable|string|exists:subscription_plans,stripe_plan_id',
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'is_trial_required' => 'required|boolean',
            'change_teacher' => 'nullable|boolean',
            'stripe_plan' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validatechangeTeacher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'course_id' => 'required|integer|exists:courses,id',
            'change_teacher_reason_id' => 'required|integer|exists:change_teacher_reasons,id',
            'teacher_id' => 'required|integer|exists:users,id',
            'shift_id' => 'required|integer|exists:shifts,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateGetStudentCourses(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateCreateMakeupRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'weekly_class_id' => 'required|integer|exists:weekly_classes,id',
            'availability_slot_id' => 'required|integer|exists:availability_slots,id',
            'makeup_date_time' => 'required|date_format:Y-m-d H:i:s',
            'class_type' => 'required|string'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;

    }

    public function validateMakeupRequests(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateAcceptRejectMakeupRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'makeup_request_id' => 'required|integer|exists:makeup_requests,id',
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateGetStudentCourseActivity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'course_id' => 'required|integer|exists:courses,id',
            'page' => 'required|integer',
            'limit' => 'required|integer',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }
}