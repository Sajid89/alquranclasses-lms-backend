<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CourseRequest
{
    public function validateAddUpdateCourse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:2|max:255',
            'description' => 'required|string',
            'is_custom' => 'required|in:0,1',
            'is_locked' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }
}
