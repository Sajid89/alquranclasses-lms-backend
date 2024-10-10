<?php

namespace App\Repository;

use App\Classes\Enums\UserTypesEnum;
use App\Http\Resources\TeacherCoordinatorResource;
use App\Models\User;
use App\Repository\Interfaces\EloquentRepositoryInterface;
use App\Repository\Interfaces\UserRepositoryInterface;
use App\Repository\BaseRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * @var Eloquent | Model
     */
    public $model;

    /**
     * @var Eloquent | Model
     */
    protected $originalModel;

    /**
     * RepositoriesAbstract constructor.
     * @param User $model
     */
    public function __construct(User $model)
    {
        $this->model = $model;
        $this->originalModel = $model;
    }

    public function scopeRoleUser()
    {
        $this->model = $this->model->RoleUser();
    }
    /**
     * update current user
     * @param $data
     * @return mixed
     */
    public function updateUser($data)
    {
        return User::whereId(Auth::user()->id)->update($data);
    }

    /**
     * update a teacher
     * @param $teacherId
     * @param $data
     * @return mixed
     */
    public function updateTeacher($teacherId, $data)
    {
        return User::whereId($teacherId)->update($data);
    }


    /**
     * To show teacher weekly routine classes
     * along with day, slot time, student and time zones
     * @return array
     */
    public function teacherWeeklyRoutineClasses()
    {
        $classData = [];
        $routineClasses = [];

        if ($this->model->availability) {
            $routineClasses = $this->model->routine_classes->load('availabilitySlot', 'student');
        }

        foreach (get_current_week() as $key => $day)
        {
            foreach ($routineClasses as $classKey => $routineClass)
            {
                $availabilitySlot = $routineClass->availabilitySlot;

                if ($availabilitySlot->day_id == $key)
                {
                    $from = Carbon::parse($availabilitySlot->slot->slot)->format('h:i A');
                    $to = Carbon::parse($from)->addMinutes(30)->format('h:i A');

                    $classData[] = array(
                        'class_id' => $routineClass->id,
                        'day_id' => $key,
                        'slot_label' => $from . ' - ' . $to,
                        'slot_label_from' => $from,
                        'course_title' => $routineClass->student->course->title,
                        'student_name' => $routineClass->student->name,
                        'routine_class_key' => $classKey,
                        'teacher_timezone' => $this->model->timezone,
                        'student_timezone' => $routineClass->student->timezone,
                        'class_link' => ''
                    );
                }
            }
        }

        return $classData;
    }

    /**
     * Get student weekly classes
     *
     * @return array
     */
    public function studentWeeklyRoutineClasses()
    {
        $classData = [];
        //$this->model->load('profiles');

        foreach (get_current_week() as $key => $day)
        {
            foreach ($this->model->profiles as $profileKey => $student)
            {
                foreach ($student->routine_classes as $classKey => $routineClass)
                {
                    $availabilitySlot = $routineClass->availabilitySlot;

                    if ($availabilitySlot->day_id == $key)
                    {
                        $from = Carbon::parse($availabilitySlot->slot->slot)->format('h:i A');
                        $to = Carbon::parse($from)->addMinutes(30)->format('h:i A');

                        $classData[] = array(
                            'class_id' => $routineClass->id,
                            'day_id' => $key,
                            'slot_label' => $from . ' - ' . $to,
                            'slot_label_from' => $from,
                            'course_title' => $routineClass->student->course->title,
                            'student_name' => $routineClass->student->name,
                            'student_id' => $routineClass->student->id,
                            'routine_class_key' => $classKey,
                            'teacher_timezone' => $routineClass->student->teacher->timezone,
                            'student_timezone' => $routineClass->student->timezone,
                            'class_link' => ''
                        );
                    }
                }

            }
        }

        return $classData;
    }

    /**
     * Get teacher class stats
     * @return mixed
     */
    public function teacherClassStats()
    {
        return User::userClassStats($this->model->id)->first();
    }

    /**
     * Get Admin ID
     * @return mixed
     */
    public function getAdminID()
    {
        return $this->model->where('user_type', UserTypesEnum::Admin)->pluck('id')->first();
    }

    /**
     * Get array of Teachers ID's coordinated by a teacher coordinator
     * 
     * @param $teacherCoordinatorId
     * @return array
     */

    public function getCoordinatedTeachers($teacherCoordinatorId)
    {
        return $this->model->where('coordinated_by', $teacherCoordinatorId)
            ->where('user_type', UserTypesEnum::Teacher)
            ->pluck('id')->toArray();
    }

    /**
     * Get all teachers for a teacher coordinator
     *
     * @param int $userId
     * @return array
     */
    public function getListOfTeachers($teacherCoordinatorId, $offset, $limit) {
        $teachers = User::orderBy('id')
            ->where('coordinated_by', $teacherCoordinatorId)
            ->where('user_type', 'teacher')
            ->get();
        $count = sizeof($teachers);

        $teachers = User::orderBy('id')
            ->where('coordinated_by', $teacherCoordinatorId)
            ->where('user_type', 'teacher')
            ->forPage($offset, $limit)
            ->paginate($limit);

        $data = TeacherCoordinatorResource::collection($teachers);
       
        return [
            'data' => $data,
            'count' => $count
        ];
     }
}


