<?php

namespace App\Repository;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Student;
use App\Models\StudentCourse;
use App\Models\User;
use App\Models\WeeklyClass;
use Carbon\Carbon;

class AttendanceRepository
{
    private $model;

    public function __construct(Attendance $model)
    {
        $this->model = $model;
    }

    public function create($data)
    {
        return $this->model->create($data);
    }

    public function findByPersonAndClass($personId, $classId)
    {
        return $this->model->where('person_id', $personId)
            ->where('class_id', $classId)
            ->first();
    }

    /**
     * Get attendance for a student in a course for a given month
     *
     * @param int $studentId
     * @param int $courseId
     * @param int $month
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAttendanceForCourse($studentId, $courseId, $month)
    {
        $startOfMonth = Carbon::createFromFormat('Y-m', $month, 'UTC')->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $month, 'UTC')->endOfMonth();
        $studentCourseId = StudentCourse::where(
            ['student_id' => $studentId, 'course_id' => $courseId])
            ->first()->id;

        $statuses = [
            'attended'  => 0,
            'absent'    => 0,
            'cancelled' => 0,
            'declined'  => 0,
            'makeup'    => 0,
        ];

        $counts = WeeklyClass::whereHas('routineClass', 
            function ($query) use ($studentId, $studentCourseId) {
                $query->where('student_id', $studentId)
                ->where('student_course_id', $studentCourseId);
            }
        )
        ->whereBetween('class_time', [$startOfMonth, $endOfMonth])
        ->get()
        ->map(function ($class) {
            if ($class->student_presence == 1) {
                return 'attended';
            } elseif ($class->student_presence == 0) {
                return 'absent';
            } elseif ($class->student_status == 'cancelled') {
                return 'cancelled';
            } elseif ($class->student_status == 'declined') {
                return 'declined';
            } else {
                return 'Unknown';
            }
        })
        ->countBy()
        ->all();

        return array_merge($statuses, $counts);
    }

    /**
     * Get attendance for a teacher for a given month
     *
     * @param int $teacherId
     * @param int $month
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAttendanceForTeacher($teacherId, $month)
    {
        $startOfMonth = Carbon::createFromFormat('Y-m', $month, 'UTC')->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $month, 'UTC')->endOfMonth();
    
        $statuses = [
            'attended'  => 0,
            'absent'    => 0,
            'cancelled' => 0,
            'declined'  => 0,
            'makeup'    => 0,
        ];
    
        $counts = WeeklyClass::whereHas('routineClass', 
            function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            }
        )
        ->whereBetween('class_time', [$startOfMonth, $endOfMonth])
        ->get()
        ->map(function ($class) {
            if ($class->teacher_status == 'present') {
                return 'attended';
            } elseif ($class->teacher_presence == 0 || $class->teacher_status == 'absent') {
                return 'absent';
            } elseif ($class->status == 'cancelled') {
                return 'cancelled';
            } elseif ($class->status == 'declined') {
                return 'declined';
            } elseif($class->status == 'makeup') {
                return 'makeup';
            }
        })
        ->countBy()
        ->all();
    
        return array_merge($statuses, $counts);
    }
}