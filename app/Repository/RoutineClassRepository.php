<?php

namespace App\Repository;

use App\Models\RoutineClass;
use App\Models\WeeklyClass;
use App\Repository\Interfaces\RoutineClassRepositoryInterface;
use Carbon\Carbon;

class RoutineClassRepository implements RoutineClassRepositoryInterface
{
    protected $model;

    /**
     * RoutineClassRepository constructor.
     * @param RoutineClass $model
     */
    public function __construct(RoutineClass $model)
    {
        $this->model = $model;
    }

    /**
     * Get student routine classes & pluck slot_id
     * @param $studentID
     * @return array
     */
    public function getStudentRoutineClassesWithSlotIds($studentID)
    {
        return $this->model->where('student_id', $studentID)->pluck('slot_id')->toArray();
    }

    /**
     * Get student routine classes
     * @param $studentID
     * @return array
     */
    public function getStudentRoutineClasses($studentID)
    {
        return $this->model->where('student_id', $studentID)->get();
    }

    /**
     * Get routine class against slot ids
     * @param $slotIDS
     * @return mixed
     */
    public function getRoutineClassesForSlotIds($slotIDS)
    {
        return $this->model->whereIn('slot_id', $slotIDS)->get();
    }

    /**
     * Soft delete routine classes,
     * their weekly classes
     * @param $slotIDS
     * @param null $student_id
     */
    public function softDeleteRoutineWeeklyClasses($slotIDS = [], $student_id = null)
    {
        $routineClasses = $student_id ? $this->getStudentRoutineClasses($student_id)
            : $this->getRoutineClassesForSlotIds($slotIDS);

        WeeklyClass::whereIn('routine_class_id', $routineClasses->pluck('id'))
            ->where('class_time', '>', Carbon::now()->endOfDay())->delete();

        $this->model->whereIn('id', $routineClasses->pluck('id'))->delete();
    }

    /**
     * Get routine class again
     * student, slot ids
     * @param $studentID
     * @param $slotIDS
     * @return
     */
    public function getStudentRoutineClassesForSlotIds($studentID, $slotIDS)
    {
        return $this->model->where('student_id', $studentID)
            ->whereIn('slot_id', $slotIDS)
            ->pluck('slot_id');
    }
}
