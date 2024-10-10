<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ChatRequest
{
    /**
     * Validate the Pushe auth request
     * 
     * @param Request $request
     * @return Request
     */
    public function validatePusheAuth(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'socket_id' => 'required|string',
            'channel_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    /**
     * Validate the send new message request
     * 
     * @param Request $request
     * @return Request
     */
    public function validateSendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender' => 'required|string',
            'from' => 'required|numeric',
            'to' => 'required|numeric',
            'message' => 'required|string',
            'type' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    /**
     * Validate the get messages request
     * 
     * @param Request $request
     * @return Request
     */
    public function validateGetMessages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_type' => 'required|string',
            'from' => 'required|numeric',
            'to' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    /**
     * Validate the get unread messages count and update as read request
     * 
     * @param Request $request
     * @return Request
     */
    public function validateUpdateAsRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from' => 'required|numeric',
            'to' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }
}