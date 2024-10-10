<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AgoraRequest
{
    public function validateGenerateToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'student_id' => 'required|integer|exists:students,id',
            //'teacher_id' => 'required|integer|exists:users,id',
            'user_id' => 'required|integer',
            'class_id' => 'required|integer',
            //'class_type' => 'required|in:trial,weekly',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if ($request->class_type === 'weekly') {
            $this->validateGenerateTokenforWeeklyClass($request);
        }

        if ($request->class_type === 'trial') {
            $this->validateGenerateTokenforTrialClass($request);
        }

        return $request;
    }

    public function validateGenerateTokenforTrialClass(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|integer|exists:trial_classes,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateGenerateTokenforWeeklyClass(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|integer|exists:weekly_classes,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateStartStopScreenShare(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }
}