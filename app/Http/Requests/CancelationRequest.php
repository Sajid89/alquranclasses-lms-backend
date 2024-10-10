<?php
namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CancelationRequest
{
    public function validateCancelationReasonUpdate(Request $request)
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
