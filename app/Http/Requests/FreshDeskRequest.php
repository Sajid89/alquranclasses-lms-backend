<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FreshDeskRequest
{
    
    /**
     * Validate the create ticket request
     * 
     */
    public function validateCreateTicket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'subject' => 'required|string',
            'type' => 'required|string',
            'student_name' => 'required|string',
            'course_name' => 'required|string',
            'attachments.*' => 'nullable|file',
            'attachments.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    /**
     * Validate the reply ticket request
     * 
     */
    public function validateReplyTicket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required|numeric',
            'description' => 'required|string',
            'attachments.*' => 'nullable|array',
            'attachments.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    /**
     * Validate the ticket id
     * 
     * @param int $ticket_id
     */
    public function validateTicketId($ticket_id)
    {
        $validator = Validator::make(['ticket_id' => $ticket_id], [
            'ticket_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
