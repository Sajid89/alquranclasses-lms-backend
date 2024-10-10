<?php

namespace App\Repository\Interfaces;

interface RoutineClassRepositoryInterface
{
    public function getStudentRoutineClassesWithSlotIds($studentID);
    public function getStudentRoutineClasses($studentID);
    public function getRoutineClassesForSlotIds($slotIDS);

    public function softDeleteRoutineWeeklyClasses($slotIDS);
    public function getStudentRoutineClassesForSlotIds($studentID, $slotIDS);
}
