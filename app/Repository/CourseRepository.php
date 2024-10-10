<?php

namespace App\Repository;

use App\Http\Resources\CourseResource;
use App\Models\Course;

class CourseRepository
{
    private $model;

    public function __construct(Course $model)
    {
        $this->model = $model;
    }

    public function getAllCourses()
    {
        return $this->model->all();
    }

    public function addUpdateCourse($courseId, $data) {
        $course = Course::updateOrCreate(
            ['id' => $courseId],
            $data
        );
        return new CourseResource($course);
    }
}
