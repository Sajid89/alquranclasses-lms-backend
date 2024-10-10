<?php
namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NotToCancelSubscriptionRequest
{
    public function validateNotToCancelReasonUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => [
                'required',
                'numeric'
            ],
            'reason' => [
                'required',
                'string'
            ]
        ]); 
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $request;

    }

    public function validateCancelationReasonDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => [
                'required',
                'numeric'
            ]
        ]); 
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $request;

    }
    
}
