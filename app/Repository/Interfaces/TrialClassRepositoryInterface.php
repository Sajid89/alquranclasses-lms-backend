<?php

namespace App\Repository\Interfaces;

use Illuminate\Database\Eloquent\Builder;

interface TrialClassRepositoryInterface
{
    public function create(array $data);
    public function byTeacherAndClassTime($teacher_id, $class_time);
    public function getById($ID, array $columns=['*']);
    public function getPastClass($ID, array $columns=['*']);
    public function cancelClass($classId);

    public function getTodaysClassesForCustomer($customer_id, $studentId);
    public function getStudentClassSchedulesForCourse($customerId, $studentId, $courseId, $date);
    public function getUpcomingCustomerClasses($customer_id, $studentId);

    public function getTodaysClassesForCoordinator($teacherIds);
    public function getUpcomingClassesForCoordinator($teacherIds);
}