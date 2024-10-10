<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AvailabilityRequest
{
    public function validateGetTeachersForStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|integer|exists:courses,id',
            'teacher_preference' => 'required|in:male,female',
            'shift_id' => 'required|integer|exists:shifts,id',
            'student_timezone' => 'required|string'
        ]);

        if ($validator->fails()) {
            // Simply throw the ValidationException. The Handler will take care of the response format.
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateGetCurrentTeacherForStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'course_id' => 'required|integer|exists:courses,id',
            'change_plan' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            // Simply throw the ValidationException. The Handler will take care of the response format.
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateGetTeacherAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'teacher_id' => 'required|integer|exists:users,id',
            'student_id' => 'required|integer|exists:students,id',
            'course_id' => 'required|integer|exists:courses,id',
            'shift_id' => 'required|integer|exists:shifts,id',
            'day_id' => 'required|integer|in:1,2,3,4,5,6,7',
        ]);

        if ($validator->fails()) {
            // Simply throw the ValidationException. The Handler will take care of the response format.
            throw new ValidationException($validator);
        }

        return $request;
    }
}