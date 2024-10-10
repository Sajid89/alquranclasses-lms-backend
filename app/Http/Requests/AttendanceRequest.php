<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AttendanceRequest
{
    /**
     * Validate the request for fetching class attendance 
     * for a student's course
     * 
     * @param Request $request
     * @return Request
     * @throws ValidationException
     */
    public function validateAttendanceForCourse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'course_id' => 'required|integer|exists:courses,id',
            'month' => 'required|date_format:Y-m',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    /**
     * Validate the request for creating attendance for a class
     * on joining and leavings a class
     * 
     * @param Request $request
     * @return Request
     * @throws ValidationException
     */
    public function validateCreateAttendanceForClass(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|integer',
            'class_type' => 'required|string',
            'user_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    /**
     * Validate the request for get attendance logs for a class
     * 
     * @param Request $request
     * @return Request
     * @throws ValidationException
     */
    public function validateGetClassAttendanceLogs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|integer',
            'class_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    /**
     * Validate the request for fetching class attendance 
     * for a teacher
     * 
     * @param Request $request
     * @return Request
     * @throws ValidationException
     */
    public function validateAttendanceForTeacher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'teacher_id' => 'required|integer|exists:users,id',
            'month' => 'required|date_format:Y-m',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }
}