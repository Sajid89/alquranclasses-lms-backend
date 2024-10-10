<?php
namespace App\Repository;

use App\Models\Student;
use App\Models\StudentCourseActivity;

class StudentCourseActivityRepository
{
    protected $model;

    public function __construct(StudentCourseActivity $studentCourseActivity)
    {
        $this->model = $studentCourseActivity;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(array $data, $id)
    {
        $record = $this->model->find($id);
        return $record->update($data);
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    public function getStudentCourseActivity($studentCourseId, $limit, $offset)
    {
        return $this->model->where('student_course_id', $studentCourseId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    public function getStudentCourseActivityCount($studentCourseId)
    {
        return $this->model->where('student_course_id', $studentCourseId)->count();
    }
}