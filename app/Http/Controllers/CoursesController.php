<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CourseService;

class CoursesController extends Controller
{
    private $courseService;
    private $courseRequest;
    public function __construct(CourseService $courseService, CourseRequest $courseRequest) {
        $this->courseService = $courseService;
        $this->courseRequest = $courseRequest;
    }

    public function getAllCourses() {
        $user = Auth::id();
        if($user) {
            $data = $this->courseService->getAllCourses();
            return $this->success($data, 'Courses fetched successfully');
        } else {
            return $this->error('Unauthorized', 401);
        }
    }

    public function addUpdateCourse(Request $request) {
        $user = Auth::user();
        $this->courseRequest->validateAddUpdateCourse($request);

        $courseTitle = $request->title;
        $courseDescription = $request->description;
        $isCustom = $request->is_custom;
        $isLocked = $request->is_locked;
        $courseId = $request->input('id', null);

        if($user) {
            $data = $this->courseService->addUpdateCourse($courseTitle, $courseDescription, $isCustom, $isLocked, $courseId, $user->id);
            return $this->success($data, 'Course added/updated successfully');
        } else {
            return $this->error('Unauthorized', 401);
        }
    }
}