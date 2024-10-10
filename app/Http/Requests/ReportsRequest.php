<?php
namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReportsRequest
{

    public function validatePrintSingleInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_id' => 'required|numeric|exists:invoices,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

}
