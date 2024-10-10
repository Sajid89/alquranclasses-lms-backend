<?php

namespace App\Repository;

use App\Classes\Enums\StatusEnum;
use App\Helpers\GeneralHelper;
use App\Models\Student;
use App\Models\StudentCourse;
use App\Models\TrialClass;
use App\Models\User;
use App\Repository\Interfaces\TrialClassRepositoryInterface;
use App\Traits\DecryptionTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TrialClassRepository implements TrialClassRepositoryInterface
{
    private $model;
    use DecryptionTrait;

    public function __construct(TrialClass $model)
    {
        $this->model = $model;
    }

    public function create(array $data)
    {
        $existingTrialClass = $this->model::where('teacher_id', $data['teacher_id'])
            ->where('availability_slot_id', $data['availability_slot_id'])
            ->first();

        if ($existingTrialClass) {
            return ['error' => 'A trial class with the same teacher and slot already exists.'];
        }

        return $this->model::create($data);
    }

    public function byTeacherAndClassTime($teacher_id, $class_time)
    {
        return $this->model::where(['teacher_id' => $teacher_id, 'class_time' => $class_time, 'status' => 'trial_scheduled']);
    }

    public function getById($ID, array $columns=['*'])
    {
        return $this->model::select($columns)->whereId($ID);
    }

    public function getPastClass($ID, array $columns=['*'])
    {
        return $this->model::select($columns)->whereId($ID)
            ->where('class_time','<=', Carbon::now()->subMinutes(30));
    }

    /**
     * Cancel a class for a student
     * 
     * @param $classId
     * @return mixed
     */
    public function cancelClass($classId) 
    {
        $class = $this->model::find($classId);
        $currentDateTime = Carbon::now('UTC')->format('Y-m-d H:i:s');

        $class->status = StatusEnum::CANCELLED;
        $class->updated_at = $currentDateTime;
        $class->save();

        return $class;
    }

    /**
     * Get all trial classes for a customer
     * 
     * @param $customer_id
     * @return mixed
     */
    public function getTodaysClassesForCustomer($customer_id = null, $studentId, $teacherId = null)
    {
        $columnName = $teacherId ? 'teacher_id' : 'customer_id';
        $userId = $teacherId ? $teacherId : $customer_id;
        
        if ($studentId) {
            $timezone = Student::find($studentId)->timezone;
        } else {
            $timezone = User::find($userId)->timezone;
        }
        
        return $this->model::where($columnName, $userId)
            ->when($studentId, function ($query) use ($studentId) {
                return $query->where('student_id', $studentId);
            })
            ->where('class_time', '>=', Carbon::now($timezone)->startOfDay()->setTimezone('UTC'))
            ->where('class_time', '<=', Carbon::now($timezone)->endOfDay()->setTimezone('UTC'))
            ->where('status', '!=', StatusEnum::CANCELLED)
            ->get()
            ->map(function ($class) use ($timezone) {
                return [
                    'class_id' => $class->id,
                    'course_title' => $class->studentCourse->course->title,
                    'class_time' => Carbon::parse($class->class_time)->setTimezone($timezone)->format('g:i A') . ' - ' . Carbon::parse($class->class_time)->addMinutes(30)->setTimezone($timezone)->format('g:i A'),
                    'teacher_name' => $class->Teacher()->first()->name,
                    'student_name' => $class->Student()->first()->name,
                    'teacher_profile_picture' => $class->Teacher()->first()->profile_photo_path,
                    'date' => Carbon::parse($class->class_time)->setTimezone($timezone)->format('d M'),
                    'class_type' => 'trial',
                    'student_id' => $class->student_id,
                    'teacher_id' => $class->teacher_id
                ];
            });
    }

    /**
     * Get all upcoming trial classes for a customer
     * 
     * @param $customer_id
     * @return mixed
     */
    public function getUpcomingCustomerClasses($customer_id = null, $studentId, $teacherId = null)
    {
        $columnName = $teacherId ? 'teacher_id' : 'customer_id';
        $userId = $teacherId ? $teacherId : $customer_id;
        
        if ($studentId) {
            $timezone = Student::find($studentId)->timezone;
        } else {
            $timezone = User::find($userId)->timezone;
        }
        
        $today = Carbon::now($timezone)->setTimezone('UTC');
        $oneMonthLater = $today->copy()->addDays(30);

        return $this->model::where($columnName, $userId)
            ->when($studentId, function ($query) use ($studentId) {
                return $query->where('student_id', $studentId);
            })
            ->whereBetween('class_time', [$today, $oneMonthLater])
            ->where('status', '!=', StatusEnum::CANCELLED)
            ->orderBy('class_time', 'asc')
            ->get()
            ->map(function ($class) use ($timezone) {
                return [
                    'class_id' => $class->id,
                    'course_title' => $class->studentCourse->course->title,
                    'class_time' => Carbon::parse($class->class_time)->setTimezone($timezone)->format('g:i A') . ' - ' . Carbon::parse($class->class_time)->addMinutes(30)->setTimezone($timezone)->format('g:i A'),
                    'teacher_name' => $class->Teacher()->first()->name,
                    'student_name' => $class->Student()->first()->name,
                    'teacher_profile_picture' => $class->Teacher()->first()->profile_photo_path,
                    'date' => Carbon::parse($class->class_time)->setTimezone($timezone)->format('d M'),
                    'class_type' => 'trial'
                ];
            });
    }

    /**
     * Get all trial classes for a student's course
     * 2 weeks before and after the given date
     * 
     * @param $customerId
     * @param $studentId
     * @param $courseId
     * @return mixed
     */
    public function getStudentClassSchedulesForCourse($customerId, $studentId, $courseId, $date)
    {
        $timezone = Student::find($studentId)->timezone;
        $twoWeeksAgo = Carbon::parse($date, $timezone)->subWeeks(2)->setTime(Carbon::now($timezone)->hour, Carbon::now($timezone)->minute, Carbon::now($timezone)->second);
        $twoWeeksLater = Carbon::parse($date, $timezone)->addWeeks(2)->setTime(Carbon::now($timezone)->hour, Carbon::now($timezone)->minute, Carbon::now($timezone)->second);

        $classes = $this->model::where('customer_id', $customerId)
            ->where('student_id', $studentId)
            ->whereHas('studentCourse', function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->get()
            ->map(function ($class) use ($timezone) {
                $classTime = Carbon::createFromFormat('Y-m-d H:i:s', $class->class_time, 'UTC')->setTimezone($timezone);
                $timeStampsUnix = GeneralHelper::convertDateTimeToUnixTimestamp($class->class_time);
                
                return [
                    'id'           => $class->id,
                    'student_id'   => $class->student_id,
                    'teacher_id'   => $class->teacher_id,
                    'student_name' => $this->decryptValue($class->student->name),
                    'teacher_name' => $this->decryptValue($class->teacher->name),
                    'course_title' => $class->studentCourse->course->title,
                    'class_date'   => $classTime->format('d M, Y'),
                    'class_time'   => $classTime->format('g:i A') . ' - ' . $classTime->addMinutes(30)->format('g:i A'),
                    'class_type'   => 'trial',
                    'class_time_in_timezone' => $classTime,
                    'class_time_unix'   => $timeStampsUnix['classDateTimeUnix'],
                    'current_time_unix' => $timeStampsUnix['currentDateTimeUnix'],
                ];
            })
            ->filter(function ($class) use ($twoWeeksAgo, $twoWeeksLater) {
                return $class['class_time_in_timezone']->between($twoWeeksAgo, $twoWeeksLater);
            })
            ->sortBy('class_time_in_timezone');

        $providedDate = Carbon::parse($date, $timezone)->startOfDay();
        $twoWeeksBefore = $providedDate->copy()->subWeeks(2);
        $twoWeeksAfter = $providedDate->copy()->addWeeks(2);

        return [
            'today' => $classes->filter(function ($class) use ($providedDate) {
                return $class['class_time_in_timezone']->isSameDay($providedDate);
            })->values(),
            'upcoming' => $classes->filter(function ($class) use ($providedDate, $twoWeeksAfter) {
                return $class['class_time_in_timezone']->copy()->startOfDay()->gt($providedDate)
                    && $class['class_time_in_timezone']->lte($twoWeeksAfter);
            })->values(),
            'previous' => $classes->filter(function ($class) use ($providedDate, $twoWeeksBefore) {
                return $class['class_time_in_timezone']->copy()->startOfDay()->lt($providedDate)
                    && $class['class_time_in_timezone']->gte($twoWeeksBefore);
            })->values(),
        ];
    }

    //todays trial classes of teachers
    public function getTodaysClassesForCoordinator($teacherIds) {
        $columnName = 'teacher_id';
        //$timezone = User::find($userId)->timezone;

        return $this->model::whereIn($columnName, $teacherIds)
            ->where('class_time', '>=', Carbon::now('UTC')->startOfDay()->format('Y-m-d H:i:s'))
            ->where('class_time', '<=', Carbon::now('UTC')->endOfDay()->format('Y-m-d H:i:s'))
            ->get()
            ->map(function ($class) {
                $teacher = $class->Teacher()->first();
                $timezone = $teacher->timezone;

                $routineClass = $class->routineClass;
                $studentCourse = $routineClass ? $routineClass->studentCourse : null;
                $course = $studentCourse ? $studentCourse->course : null;
                
                if($course != null && $studentCourse != null && $routineClass != null) {
                    return [
                        'class_id' => $class->id,
                        'course_title' => $class->routineClass->studentCourse->course->title,
                        'class_time' => Carbon::parse($class->class_time)->setTimezone($timezone)->format('g:i A') . ' - ' . Carbon::parse($class->class_time)->addMinutes(30)->setTimezone($timezone)->format('g:i A'),
                        'teacher_name' => $this->decryptValue($class->Teacher()->first()->name),
                        'student_name' => $this->decryptValue($class->Student()->first()->name),
                        'teacher_profile_picture' => $class->Teacher()->first()->profile_photo_path,
                        'date' => Carbon::parse($class->class_time)->setTimezone($timezone)->format('d M'),
                        'class_type' => 'regular class',
                        'student_id' => $class->student_id,
                        'teacher_id' => $class->teacher_id
                    ];
                }

            });
    }


    public function getUpcomingClassesForCoordinator($teacherIds) {
        $columnName = 'teacher_id';
        $today = Carbon::now('UTC');
        $oneMonthLater = $today->copy()->addDays(30);

        $todayMysql = $today->format('Y-m-d H:i:s');
        $oneMonthLaterMysql = $oneMonthLater->format('Y-m-d H:i:s');

        return $this->model::whereIn($columnName, $teacherIds)
            ->whereBetween('class_time', [$todayMysql, $oneMonthLaterMysql])
            ->orderBy('class_time', 'asc')
            ->get()
            ->map(function ($class) {
                $teacher = $class->Teacher()->first();
                $timezone = $teacher->timezone;

                $routineClass = $class->routineClass;
                $studentCourse = $routineClass ? $routineClass->studentCourse : null;
                $course = $studentCourse ? $studentCourse->course : null;
                
                if($course != null && $studentCourse != null && $routineClass != null) {
                    return [
                        'class_id' => $class->id,
                        'course_title' => $class->studentCourse->course->title,
                        'class_time' => Carbon::parse($class->class_time)->setTimezone($timezone)->format('g:i A') . ' - ' . Carbon::parse($class->class_time)->addMinutes(30)->setTimezone($timezone)->format('g:i A'),
                        'teacher_name' => $class->Teacher()->first()->name,
                        'student_name' => $class->Student()->first()->name,
                        'teacher_profile_picture' => $class->Teacher()->first()->profile_photo_path,
                        'date' => Carbon::parse($class->class_time)->setTimezone($timezone)->format('d M'),
                        'class_type' => 'trial'
                    ];
                }
            });
    }

    public function coordinatedTeacherUpcomingOrPreviousClasses($teacherIds, $offset, $limit, $decision) {
        $columnName = 'teacher_id';
        $today = Carbon::now('UTC');
        $todayMysql = $today->format('Y-m-d H:i:s');
    
        $count = 0;
        $classes = array();

        if($decision == "UPCOMING") {
            $count = $this->model::where('class_time', '>=', $todayMysql)
            ->whereIn($columnName, $teacherIds)
            ->count();
    
            $classes = $this->model::where('class_time', '>=', $todayMysql)
            ->whereIn($columnName, $teacherIds)
            ->orderBy('class_time', 'asc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        } else {
            $count = $this->model::where('class_time', '<', $todayMysql)
            ->whereIn($columnName, $teacherIds)
            ->count();
    
            $classes = $this->model::where('class_time', '<', $todayMysql)
            ->whereIn($columnName, $teacherIds)
            ->orderBy('class_time', 'asc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        }

            $data = array();
        foreach($classes as $class) {
            $teacher = $class->Teacher()->first();
            $timezone = $teacher->timezone;
            $studentCourse = $class->studentCourse;
            $course = $studentCourse ? $studentCourse->course : null;
            $customerName = $class->student->user->name;
            if($course != null && $studentCourse != null) {
                $data[] = [
                    'class_id' => $class->id,
                    'course_title' => $class->studentCourse->course->title,
                    'class_time' => Carbon::parse($class->class_time)->setTimezone($timezone)->format('g:i A') . ' - ' . Carbon::parse($class->class_time)->addMinutes(30)->setTimezone($timezone)->format('g:i A'),
                    'teacher_name' => $class->Teacher()->first()->name,
                    'student_name' => $class->Student()->first()->name,
                     'customer_name' => $customerName,
                    'teacher_profile_picture' => $class->Teacher()->first()->profile_photo_path,
                    'date' => Carbon::parse($class->class_time)->setTimezone($timezone)->format('d M'),
                    'class_type' => 'trial class'
                ];
            }
        }
        return ['trial_count' => $count, 'trial_classes' => $data]; 
    }

    public function getTotalUpcomingClasses($teacherIds) {
        return $this->model::whereIn('teacher_id', $teacherIds)
            ->where('class_time', '>=', Carbon::now('UTC')->format('Y-m-d H:i:s'))
            ->count();
    }

    public function getUpcomingClassesForAllTeachers($teacherIds, $currentDateTime, $limit, $offset) {
        return $this->model::where('class_time', '>=', $currentDateTime)
            ->whereIn('teacher_id', $teacherIds)
            ->orderBy('class_time', 'asc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        
    }

    /**
     * Get all trial classes count for all teachers
     */
    public function getTotalPreviousClasses($teacherIds) {
        return $this->model::whereIn('teacher_id', $teacherIds)
            ->where('class_time', '<', Carbon::now('UTC')->format('Y-m-d H:i:s'))
            ->count();
    }

    /**
     * Get all trial classes for all teachers
     * with pagination
     * 
     */
    public function getPreviousClassesForAllTeachers($teacherIds, $currentDateTime, $limit, $offset) {
        return $this->model::where('class_time', '<', $currentDateTime)
            ->whereIn('teacher_id', $teacherIds)
            ->orderBy('class_time', 'asc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        
    }
}