<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ClassScheduleRequest
{
    /**
     * Validate the add new card request
     * 
     * @param Request $request (customerId, stripeToken)
     * @return Request
     */
    public function validateStudentClassSchedulesForCourse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'course_id' => 'required|integer|exists:courses,id',
            'date' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function getAllClassSchedulesForCustomerOrStudent(Request $request) {
        $validator = Validator::make($request->all(), [
            'student_id' => 'nullable|integer|exists:students,id'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateStudendClassesSchedule(Request $request) {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateStudentPreviousClassesSchedule(Request $request) {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'page' => 'required|integer|min:1',
            'limit' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateStudentUpcomingClassesSchedule(Request $request) {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'page' => 'required|integer|min:1',
            'limit' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateCancelClass(Request $request) {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|integer',
            'class_type' => 'required|string'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateCourseActivity(Request $request) {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|integer|exists:courses,id',
            'student_id' => 'required|integer|exists:students,id',
            'page' => 'required|integer|min:1',
            'limit' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateTeacherPreviousClasses(Request $request) {
        $validator = Validator::make($request->all(), [
            'page' => 'required|integer|min:1',
            'limit' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateTeacherUpcomingClasses(Request $request) {
        $validator = Validator::make($request->all(), [
            'page' => 'required|integer|min:1',
            'limit' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

}