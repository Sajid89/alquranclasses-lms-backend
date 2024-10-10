<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SharedLibraryRequest
{
    public function validateGetLibrariesRequest(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'page' => 'required|integer',
            'limit' => 'required|integer',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateAddRequest(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'title' => [
                'required',
                'string',
                'max:190',
                Rule::unique('shared_libraries', 'title')->ignore($request->id),
            ],
            'description' => [
                'required',
                'string',
                'max:255',
            ],
            'course_id' => 'required|integer|exists:courses,id',
            'teachers' => 'required|array|min:1',
            'files' => [
                'required',
                'array',
                'min:1',
                'max:10',
                function ($attribute, $value, $fail) {
                    if (empty($value)) {
                        $fail('The ' . $attribute . ' field must not be empty.');
                    }
                },
            ]
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateDeleteRequest(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'library_id' => 'required|integer|exists:shared_libraries,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateUploadFileRequest(Request $request) 
    {
        // $file = $request->file('file');
        // $filename = $file->getClientOriginalName();
        // $fileExtension = $file->getClientOriginalExtension();

        $validator = Validator::make($request->all(), [
            'library_id' => 'required|integer|exists:shared_libraries,id',
            'file' => 'required|file|mimes:pdf,png,docx,txt,jpg,jpeg,doc, pptx, ppt',
            'title' => 'required|string|max:190',
            'slug' => 'required|string|max:190',
            'file_size' => 'required|integer',
            'file_type' => 'required|string|max:190',
            'created_by' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateDeleteFileRequest(Request $request) {
        $validator = Validator::make($request->all(), [
            'file_id' => 'required|integer|exists:library_files,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateUpdateFileRequest(Request $request) {
        $validator = Validator::make($request->all(), [
            'file_id' => 'required|integer|exists:library_files,id',
            'title' => 'required|string|max:190',
            'slug' => 'required|string|max:190',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    /**
     * send zero for new folder creation.
     * in case of update, send a valid folder id
     */
    public function validateGetAllTeachersRequest(Request $request) {
        $validator = Validator::make($request->all(), [
            'folder_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateGetLibraryDetailsRequest(Request $request) {
        $validator = Validator::make($request->all(), [
            'library_id' => 'required|integer|exists:shared_libraries,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }


    public function validateDeleteLibraryFileRequest(Request $request) {
        $validator = Validator::make($request->all(), [
            'file_id' => 'required|integer|exists:library_files,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateUpdateRequest(Request $request) {
        $validator = Validator::make($request->all(), [
            'folder_id' => 'required|integer|exists:shared_libraries,id',
            //'slug' => 'required|string|max:190',
            'description' => 'required|string|max:255',
            'title' => [
                'required',
                'string',
                'max:190',
                Rule::unique('shared_libraries', 'title')->ignore($request->folder_id),
            ],
            'teachers' => 'required',
            'course_id' => 'required|integer|exists:courses,id',
            'files' => [
                'required',
                'max:10240' // This refers to the file size in kilobytes (10 MB)
            ]
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateRemoveTeacherFolderRequest(Request $request) {
        $validator = Validator::make($request->all(), [
            'folder_id' => 'required|integer|exists:shared_libraries,id',
            'teacher_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateDeleteAwsFolderRequest(Request $request) {
        $validator = Validator::make($request->all(), [
            'folder_id' => 'required|integer|exists:shared_libraries,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateGetStudentLibrariesRequest(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'page' => 'required|integer',
            'limit' => 'required|integer',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateGetTeacherLibrariesRequest(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'page' => 'required|integer',
            'limit' => 'required|integer',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }

    public function validateAssignLibraryFileToStudentRequest(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'file_id' => 'required|integer|exists:library_files,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $request;
    }
}