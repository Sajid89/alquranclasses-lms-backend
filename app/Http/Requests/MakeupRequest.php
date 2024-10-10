<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MakeupRequest
{
    public function validateWithdrawRequest(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
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
            'availability_slot_id' => 'required|integer|exists:availability_slots,id',
            'makeup_date_time' => 'required|date_format:Y-m-d H:i:s',
            'class_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateTeacherMakeupRequests(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'page' => 'required|integer',
            'limit' => 'required|integer'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }


}