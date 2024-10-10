<?php

namespace App\Services;

use App\Http\Resources\CourseResource;
use App\Repository\CourseRepository;

class CourseService
{
    private $courseRepository;

    public function __construct(
        CourseRepository $courseRepository
    )
    {
        $this->courseRepository = $courseRepository;
    }

    public function getAllCourses()
    {
        $courses = $this->courseRepository->getAllCourses();
        return CourseResource::collection($courses);

    }

    public function addUpdateCourse($courseTitle, $courseDescription, $isCustom, $isLocked, $courseId, $userId) {
        $data = array(
            'title' => $courseTitle,
            'description' => $courseDescription,
            'is_custom' => $isCustom,
            'is_locked' => $isLocked,
            'created_by' => $userId
        );
        return $this->courseRepository->addUpdateCourse($courseId, $data);
    }
}