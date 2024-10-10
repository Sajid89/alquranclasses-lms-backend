<?php

namespace App\Repository\Interfaces;

interface AvailabilityRepositoryInterface
{
    public function createStudentAvailability($id);
    public function createUserAvailability($userId);
    public function getTeacherAvailability($teacherId, $dayId);
}
