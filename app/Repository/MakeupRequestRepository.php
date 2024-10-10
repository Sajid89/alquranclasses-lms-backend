<?php

namespace App\Repository;

use App\Models\MakeupRequest;

class MakeupRequestRepository
{
    private $model;

    public function __construct(MakeupRequest $model)
    {
        $this->model = $model;
    }

    public function create($data) {
        return $this->model->create($data);
    }

    public function getByClassIdAndType($classId, $classType)
    {
        return $this->model->where('class_id', $classId)
            ->where('class_type', $classType)
            ->first();
    }

    public function getAllRequestsByStudentCourseId($studentCourseIds, $offset, $limit)
    {
        return $this->model->whereIn('student_course_id', $studentCourseIds)
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    public function getAllRequestsCountByStudentCourseId($studentCourseIds)
    {
        return $this->model->whereIn('student_course_id', $studentCourseIds)->count();
    }

    public function findById($id)
    {
        return $this->model->find($id);
    }

    public function update($id, $data) {
        return $this->model->where('id', $id)->update($data);
    }

    public function getUpcomingMakeupRequestsForAllTeachers($teacherIds, $currentDateTime) {
        $upcomingMakeupRequests = array();
        
        $makeupRequests = $this->model->select('class_id', 'class_type', 'status')
            ->where('makeup_date_time', '>', $currentDateTime)
            ->where('status', 'pending')
            ->whereHas('studentCourse', function ($query) use ($teacherIds) {
                $query->whereIn('teacher_id', $teacherIds);
            })->with('studentCourse')->get();
            foreach($makeupRequests as $makeupRequest) {
                $upcomingMakeupRequests[] = array(
                    'class_id' => $makeupRequest->class_id,
                    'class_type' => $makeupRequest->class_type,
                    'status' => $makeupRequest->status
                );
            }

        return $upcomingMakeupRequests;
    }

    /**
     * the below function will return a total count of all makeup requests for all teachers
     * coordinated by teacher coordinator
     */
    public function getTotalMakeupRequests($teacherIds) {
        return $this->model->whereHas('studentCourse', function ($query) use ($teacherIds) {
            $query->whereIn('teacher_id', $teacherIds);
        })->count();
    }

    /**
     * the below function will return all makeup requests for all teachers
     * coordinated by teacher coordinator
     */
    public function getAllTeachersMakeupRequests($teacherIds, $offset, $limit) {
        return $this->model->whereHas('studentCourse', function ($query) use ($teacherIds) {
            $query->whereIn('teacher_id', $teacherIds);
        })->orderBy('created_at', 'desc')
        ->offset($offset)
        ->limit($limit)
        ->get();
    }
}