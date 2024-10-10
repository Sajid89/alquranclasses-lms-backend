<?php

namespace App\Repository\Interfaces;

interface TeacherRepositoryInterface
{
    public function getTeachers($teacherPreference, $courseId, $shiftSlotIds, $studentId, $teacherId);
    public function getCurrentTeacher($teacherId);
    public function getStudents($teacherId);
    public function getTeacherById($teacherId);
}