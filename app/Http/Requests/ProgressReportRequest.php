<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProgressReportRequest
{
    /**
     * Validate the add new card request
     * 
     * @param Request $request (customerId, stripeToken)
     * @return Request
     */
    public function validateCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'course_id' => 'required|integer|exists:courses,id',
            'document' => 'required|file|mimes:pdf|max:2048',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    /**
     * Validate the delete card request
     * 
     * @param Request $request (id)
     * @return Request
     */
    public function validateDelete($request)
    {
        $validator = Validator::make(['id' => $request->route('id')], [
            'id' => 'required|numeric|exists:progress_reports,id',
        ]);
    
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validate the get progress report request
     * 
     * @param Request $request (student_id)
     * @return Request
     */
    public function validateGet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'page' => 'nullable|integer',
            'limit' => 'nullable|integer|max:20',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    /**
     * Validate the download progress report request
     * 
     * @param Request $request (id)
     * @return Request
     */
    public function validateDownload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:progress_reports,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }
}