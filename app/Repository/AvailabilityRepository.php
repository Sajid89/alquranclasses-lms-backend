<?php

namespace App\Repository;

use App\Models\Availability;
use App\Models\Student;
use App\Models\TrialRequest;
use App\Models\User;
use App\Repository\Interfaces\AvailabilityRepositoryInterface;

class AvailabilityRepository implements AvailabilityRepositoryInterface
{
    protected $model;

    /**
     * Availability Repository constructor.
     * @param Availability $model
     */
    public function __construct(Availability $model)
    {
        $this->model = $model;
    }

    /**
     * add student availability
     * @param $id student id
     * @return mixed
     */
    public function createStudentAvailability($id)
    {
        $trialRequest = Student::find($id);
        $availability = new Availability();
        return $trialRequest->availability()->save($availability);
    }

    /**
     * add teacher availability
     * @param $userId
     * @return
     */
    public function createUserAvailability($userId)
    {
        $user = User::find($userId);
        $availability = new Availability([]);
        return $user->availability()->save($availability);
    }

    public function getTeacherAvailability($teacherId, $dayId)
    {
        $availableType = 'App\Models\User';

        return $this->model::where('available_type', $availableType)
            ->where('available_id', $teacherId)
            ->with(['availabilitySlots' => function ($query) use ($dayId) {
                $query->where('day_id', $dayId);
            }])
            ->first();
    }
}
