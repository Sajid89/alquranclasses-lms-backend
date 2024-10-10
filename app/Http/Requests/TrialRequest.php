<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TrialRequest
{
    /**
     * Validate the add new card request
     * 
     * @param Request $request (customerId, stripeToken)
     * @return Request
     */
    public function validateCreateTrial(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'teacher_id' => 'required|integer|exists:users,id',
            'student_id' => 'required|integer|exists:students,id',
            'availability_slot_id' => 'required|integer|exists:availability_slots,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }
}