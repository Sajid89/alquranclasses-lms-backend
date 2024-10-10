<?php

namespace App\Repository\Interfaces;

interface UserRepositoryInterface extends EloquentRepositoryInterface
{
    public function scopeRoleUser();

    public function updateUser($data);

    public function updateTeacher($teacherId, $data);

    public function teacherWeeklyRoutineClasses();
    public function studentWeeklyRoutineClasses();
    public function teacherClassStats();
    public function getAdminID();
}
