<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TeacherPaymentMethodRequest
{
    /**
     * Validate the add new banke details request
     * 
     * @param Request $request
     * @return Request
     */
    public function validateCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:100|regex:/^[a-zA-Z ]+$/',
            'account_title' => 'required|string|max:100|regex:/^[a-zA-Z ]+$/',
            'account_number' => 'required|numeric|digits_between:8,20',
            'id_card_no' => 'required|string|max:20|regex:/^[0-9A-Za-z]+$/',
            'attachments' => 'required|array|min:2',
            'attachments.*' => 'file|mimes:jpg,jpeg,png',
            'iban' => 'required|string|max:34|regex:/^[a-zA-Z0-9]+$/',
            'dob' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    /**
     * Validate the update banke details request
     * 
     * @param Request $request
     * @return Request
     */
    public function validateUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:100|regex:/^[a-zA-Z ]+$/',
            'account_title' => 'required|string|max:100|regex:/^[a-zA-Z ]+$/',
            'account_number' => 'required|numeric|digits_between:8,20',
            'id_card_no' => 'required|string|max:20|regex:/^[0-9A-Za-z]+$/',
            'attachments.*' => 'file|mimes:jpg,jpeg,png',
            'iban' => 'required|string|max:34|regex:/^[a-zA-Z0-9]+$/',
            'dob' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }
}