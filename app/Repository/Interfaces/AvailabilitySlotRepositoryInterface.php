<?php

namespace App\Repository\Interfaces;

use App\Repository\Interfaces\EloquentRepositoryInterface;

interface AvailabilitySlotRepositoryInterface
{
    public function addStudentAvailabilitySlots($availabilityId, array $days, $shiftId);
    public function getShiftSlots($shift);

    public function getAllSlots();
    public function addTeacherAvailabilitySlots($teacherAvailabilityId, array $teacherAvailabilitySlots);
    public function getSlotsByShiftIds(array $slotIds);
    public function getSlotByTime($time);

    public function getTeacherAvailabilitySlots($day_id, $teacher_id);
    public function getTeacherAllAvailabilitySlots($teacher_id);
    public function getStudentAllAvailabilitySlots($student_id);

    public function filterCollections($array1, $array2);
    public function EagerLoadRelationships($obj);

    public function mapTeacherAvailabilitySlots($teacher_id);
    public function mapStudentAvailabilitySlots($student_id);
}
