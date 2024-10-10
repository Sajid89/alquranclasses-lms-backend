<?php

namespace App\Repository;

use App\Classes\Enums\StatusEnum;
use App\Helpers\GeneralHelper;
use App\Models\MakeupRequest;
use App\Models\Student;
use App\Models\User;
use App\Models\WeeklyClass;
use App\Traits\DecryptionTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WeeklyClassRepository
{
    private $model;
    private $makeupRequestRepository;
    private $trialClassRepository;
    use DecryptionTrait;

    public function __construct(WeeklyClass $model, 
    MakeupRequestRepository $makeupRequestRepository,
    TrialClassRepository $trialClassRepository)
    {
        $this->model = $model;
        $this->makeupRequestRepository = $makeupRequestRepository;
        $this->trialClassRepository = $trialClassRepository;
    }

    /**
     * Get all weekly classes for a customer/teacher
     * 
     * @param $customer_id
     * @return mixed
     */
    public function getTodaysClassesForCustomer($customerId = null, $studentId, $teacherId = null)
    {
        $columnName = $teacherId ? 'teacher_id' : 'customer_id';
        $userId = $teacherId ? $teacherId : $customerId;
        
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
            });
    }

    /**
     * Get all upcoming weekly classes for a customer
     * if student_id is null, get all upcoming classes for a student
     * if student_id is provided.
     * 
     * @param $customer_id
     * @return mixed
     */
    public function getUpcomingCustomerClasses($customerId = null, $studentId, $teacherId = null)
    {
        $columnName = $teacherId ? 'teacher_id' : 'customer_id';
        $userId = $teacherId ? $teacherId : $customerId;
        
        if ($studentId) {
            $timezone = Student::find($studentId)->timezone;
        } else {
            $timezone = User::find($userId)->timezone;
        }

        $today = Carbon::now($timezone);
        $oneMonthLater = $today->copy()->addDays(30)->setTimezone('UTC');

        return $this->model::where($columnName, $userId)
            ->when($studentId, function ($query) use ($studentId) {
                return $query->where('student_id', $studentId);
            })
            ->whereBetween('class_time', [$today->setTimezone('UTC'), $oneMonthLater])
            ->where('status', '!=', StatusEnum::CANCELLED)
            ->orderBy('class_time', 'asc')
            ->get()
            ->map(function ($class) use ($timezone) {
                return [
                    'class_id' => $class->id,
                    'course_title' => $class->routineClass->studentCourse->course->title,
                    'class_time' => Carbon::parse($class->class_time)->setTimezone($timezone)->format('g:i A') . ' - ' . Carbon::parse($class->class_time)->addMinutes(30)->setTimezone($timezone)->format('g:i A'),
                    'teacher_name' => $class->Teacher()->first()->name,
                    'student_name' => $class->Student()->first()->name,
                    'teacher_profile_picture' => $class->Teacher()->first()->profile_photo_path,
                    'date' => Carbon::parse($class->class_time)->setTimezone($timezone)->format('d M'),
                    'class_type' => 'regular class'
                ];
            });
    }

    /**
     * Get all weekly classes for a student's course
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
            ->whereHas('routineClass', function ($query) use ($courseId) {
                $query->whereHas('studentCourse', function ($query) use ($courseId) {
                    $query->where('course_id', $courseId);
                });
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
                    'course_title' => $class->routineClass->studentCourse->course->title,
                    'class_date'   => $classTime->format('d M, Y'),
                    'class_time'   => $classTime->format('g:i A') . ' - ' . $classTime->addMinutes(30)->format('g:i A'),
                    'class_type'   => 'regular',
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

    /**
     * Get all previous classes for a student
     * 
     * @param $customerId
     * @param $studentId
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function removeStudentUpcomingClasses($routineClassesIds) {

        $now = Carbon::now();

        DB::table('weekly_classes')
            ->whereIn('routine_class_id', $routineClassesIds)
            ->where('class_time', '>', $now)
            ->delete();
    }

    /**
     * Get all weekly, trial classes for a student
     * 
     * @param $studentId
     * @return mixed
     */
    public function studentClassesSchedule($studentId) {
        dd('this api became useless, babu khan');
        $currentDateTime = Carbon::now('UTC')->format('Y-m-d H:i:s');
        $trialClasses = array('previous' => '', 'upcoming' => '');
        $weeklyClasses = array('previous' => '', 'upcoming' => '');
        $previousClasses = array();
        $upcomingClasses = array();
        
        $queryTrial = "call spPreviousAndUpcomingTrialClasses($studentId, '$currentDateTime');";
        $queryWeekly = "call spPreviousAndUpcomingWeeklyClasses($studentId, '$currentDateTime');";
        $resultTrial = DB::select($queryTrial);
        $resultWeekly = DB::select($queryWeekly);
        
        //trial classes
        //date and time are converted to student timezone
        foreach($resultTrial as $trial) {
            $timeZone = $trial->student_timezone;
            $timeStampsUnix = GeneralHelper::convertDateTimeToUnixTimestamp($trial->class_time);
            $trialStatus = $trial->status=='trial_scheduled' ? 'scheduled' : $trial->status;
            $data = array(
                'class_id' => $trial->tw_class_id,
                'course_title' => $trial->course_title,
                'class_time' => Carbon::parse($trial->class_time)->setTimezone($timeZone)->format('g:i A'),
                'date' => Carbon::parse($trial->class_time)->setTimezone($timeZone)->format('M d, Y'),
                'time' => Carbon::parse($trial->class_time)->setTimezone($timeZone)->format('g:i A'),
                'teacher_name' => $this->decryptValue($trial->teacher_name),
                'student_name' => $this->decryptValue($trial->student_name),
                'status' => $trialStatus,
                'class_time_unix' => $timeStampsUnix['classDateTimeUnix'],
                'current_time_unix' => $timeStampsUnix['currentDateTimeUnix'],
                'student_time_zone' => $timeZone
            );
            if($trial->previous_or_upcoming == 'previous')
                $previousClasses[] = $data;
            else
                $upcomingClasses[] = $data;
        }
        $trialClasses['previous'] = $previousClasses;
        $trialClasses['upcoming'] = $upcomingClasses;

        //weekly classes
        $previousClasses = array();
        $upcomingClasses = array();
        foreach($resultWeekly as $weekly) {
            $timeZone = $weekly->student_timezone;
            $timeStampsUnix = GeneralHelper::convertDateTimeToUnixTimestamp($weekly->class_time);
            $data = array(
                'class_id' => $weekly->tw_class_id,
                'course_title' => $weekly->course_title,
                'class_time' => Carbon::parse($weekly->class_time)->setTimezone($timeZone)->format('g:i A'),
                'date' => Carbon::parse($weekly->class_time)->setTimezone($timeZone)->format('M d, Y'),
                'time' => Carbon::parse($weekly->class_time)->setTimezone($timeZone)->format('g:i A'),
                'teacher_name' => $this->decryptValue($weekly->teacher_name),
                'student_name' => $this->decryptValue($weekly->student_name),
                'status' => $weekly->status,
                'class_time_unix' => $timeStampsUnix['classDateTimeUnix'],
                'current_time_unix' => $timeStampsUnix['currentDateTimeUnix'],
                'student_time_zone' => $timeZone
            );
            if($weekly->previous_or_upcoming == 'previous')
                $previousClasses[] = $data;
            else
                $upcomingClasses[] = $data;
        }
        $weeklyClasses['previous'] = $previousClasses;
        $weeklyClasses['upcoming'] = $upcomingClasses;
        $result = array(
            'trial_classes' => $trialClasses,
            'weekly_classes' => $weeklyClasses
        );
        return $result;
    }

    /**
     * Get all previous classes for a student
     * 
     * @param $studentId
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function studentPreviousClassesSchedule($studentId, $page, $limit) 
    {
        $currentDateTime = Carbon::now('UTC')->subMinutes(30)->format('Y-m-d H:i:s');
        $previousTrialClasses = array();
        $queryCredits = "call spStudentAvailableCredits($studentId, '$currentDateTime');";
        $resultCredits = DB::select($queryCredits);
        $availableCredits = array();
        foreach($resultCredits as $credit) {
            $availableCredits[] = array(
                'class_id' => $credit->class_id,
                'class_type' => $credit->class_type
            );
        }

        $totalAvailableCredits = sizeof($availableCredits);

        $queryTotalPreviousClassesCount = "call spPreviousClassesCount($studentId, '$currentDateTime');";
        $resultSet = DB::select($queryTotalPreviousClassesCount);
        $totalPreviousCount = $resultSet[0]->count;

        $query = "call spPreviousTrialClasses($studentId, '$currentDateTime', $page, $limit);";
        $result = DB::select($query);
        foreach($result as $previous) {
            $timeZone = $previous->student_timezone;
            $status = $this->checkCreditWithClass($previous->tw_class_id, $availableCredits, 'TrialClass');
            $data = array(
                'class_id' => $previous->tw_class_id,
                'course_title' => $previous->course_title,
                'class_time' => Carbon::parse($previous->class_time)->setTimezone($timeZone)->format('g:i A'),
                'date' => Carbon::parse($previous->class_time)->setTimezone($timeZone)->format('M d, Y'),
                'time' => Carbon::parse($previous->class_time)->setTimezone($timeZone)->format('g:i A'),
                'teacher_name' => $this->decryptValue($previous->teacher_name),
                'student_name' => $this->decryptValue($previous->student_name),
                'status' => $previous->status == 'trial_scheduled' ? 'scheduled' : $previous->status,
                'student_time_zone' => $timeZone,
                'can_reschedule' => $status,
                'student_id' => $previous->student_id,
                'teacher_id' => $previous->teacher_id
            );
        $previousTrialClasses[] = $data;
        }
        
        $previousWeeklyClasses = array();
        $query = "call spPreviousWeeklyClasses($studentId, '$currentDateTime', $page, $limit);";
        $result = DB::select($query);
        foreach($result as $previous) {
            $timeZone = $previous->student_timezone;
            $status = $this->checkCreditWithClass($previous->tw_class_id, $availableCredits, 'TrialClass');
            $data = array(
                'class_id' => $previous->tw_class_id,
                'course_title' => $previous->course_title,
                'class_time' => Carbon::parse($previous->class_time)->setTimezone($timeZone)->format('g:i A'),
                'date' => Carbon::parse($previous->class_time)->setTimezone($timeZone)->format('M d, Y'),
                'time' => Carbon::parse($previous->class_time)->setTimezone($timeZone)->format('g:i A'),
                'teacher_name' => $this->decryptValue($previous->teacher_name),
                'student_name' => $this->decryptValue($previous->student_name),
                'status' => $previous->status,
                'student_time_zone' => $timeZone,
                'can_reschedule' => $status,
                'student_id' => $previous->student_id,
                'teacher_id' => $previous->teacher_id
            );
            $previousWeeklyClasses[] = $data;
        }
        $data = array(
            'total_previous_classes' => $totalPreviousCount,
            'total_available_credits' => $totalAvailableCredits,
            'previous_trial_classes' => $previousTrialClasses,
            'previous_weekly_classes' => $previousWeeklyClasses
        );
        return $data;
    }
 
    /**
     * Get all upcoming classes for a student
     * 
     * @param $studentId
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function studentUpcomingClassesSchedule($studentId, $page, $limit) 
    {
        $currentDateTime = Carbon::now('UTC')->subMinutes(30)->format('Y-m-d H:i:s');
        $upcomingTrialClasses = array();

        $queryTotalPreviousClassesCount = "call spUpcomingClassesCount($studentId, '$currentDateTime');";
        $resultSet = DB::select($queryTotalPreviousClassesCount);
        $totalUpcomingCount = $resultSet[0]->count;

        $query = "call spUpcomingTrialClasses($studentId, '$currentDateTime', $page, $limit);";
        $result = DB::select($query);
        
        foreach($result as $upcoming) 
        {
            $timeZone = $upcoming->student_timezone;
            $timeStampsUnix = GeneralHelper::convertDateTimeToUnixTimestamp($upcoming->class_time);
            $data = array(
                'student_id' => $upcoming->student_id,
                'teacher_id' => $upcoming->teacher_id,
                'class_id' => $upcoming->tw_class_id,
                'course_title' => $upcoming->course_title,
                'class_time' => Carbon::parse($upcoming->class_time)->setTimezone($timeZone)->format('g:i A'),
                'date' => Carbon::parse($upcoming->class_time)->setTimezone($timeZone)->format('M d, Y'),
                'time' => Carbon::parse($upcoming->class_time)->setTimezone($timeZone)->format('g:i A'),
                'teacher_name' => $this->decryptValue($upcoming->teacher_name),
                'student_name' => $this->decryptValue($upcoming->student_name),
                'status' => $upcoming->status == 'trial_scheduled' ? 'scheduled' : $upcoming->status,
                'class_time_unix' => $timeStampsUnix['classDateTimeUnix'],
                'current_time_unix' => $timeStampsUnix['currentDateTimeUnix'],
                'student_time_zone' => $timeZone
            );
            $upcomingTrialClasses[] = $data;
        }

        $upcomingWeeklyClasses = array();
        $query = "call spUpcomingWeeklyClasses($studentId, '$currentDateTime', $page, $limit);";
        $result = DB::select($query);
        
        foreach($result as $upcoming) 
        {
            $timeZone = $upcoming->student_timezone;
            $timeStampsUnix = GeneralHelper::convertDateTimeToUnixTimestamp($upcoming->class_time);
            $data = array(
                'student_id' => $upcoming->student_id,
                'teacher_id' => $upcoming->teacher_id,
                'class_id' => $upcoming->tw_class_id,
                'course_title' => $upcoming->course_title,
                'class_time' => Carbon::parse($upcoming->class_time)->setTimezone($timeZone)->format('g:i A'),
                'date' => Carbon::parse($upcoming->class_time)->setTimezone($timeZone)->format('M d, Y'),
                'time' => Carbon::parse($upcoming->class_time)->setTimezone($timeZone)->format('g:i A'),
                'teacher_name' => $this->decryptValue($upcoming->teacher_name),
                'student_name' => $this->decryptValue($upcoming->student_name),
                'status' => $upcoming->status,
                'class_time_unix' => $timeStampsUnix['classDateTimeUnix'],
                'current_time_unix' => $timeStampsUnix['currentDateTimeUnix'],
                'student_time_zone' => $timeZone
            );
            $upcomingWeeklyClasses[] = $data;
        }
        
        $data = array(
            'total_upcoming_count' => $totalUpcomingCount,
            'upcoming_trial_classes' => $upcomingTrialClasses,
            'upcoming_weekly_classes' => $upcomingWeeklyClasses
        );
        
        return $data;
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
     * Get all activities for a course
     * 
     * @param $courseId
     * @param $studentId
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function courseActivity($courseId, $studentId, $page, $limit) 
    {
        $query = "call spCourseActivity($courseId, $studentId, $page, $limit);";
        $result = DB::select($query);
        $data = array();
        foreach($result as $activity) {
            $data[] = array(
                'activity_id' => $activity->activity_id,
                'student_name' => $this->decryptValue($activity->student_name),
                'student_time_zone' => $activity->student_time_zone,
                'teacher_name' => $this->decryptValue($activity->teacher_name),
                'teacher_time_zone' => $activity->teacher_time_zone,
                'activity_type' => $activity->activity_type,
                'description' => $activity->description,
                'file_name' => $activity->file_name,
                'file_size' => $activity->file_size,
                'course_name' => $activity->course_name,
                'created_at' => array(
                    'utc' => $activity->created_at,
                    'utc_date' => Carbon::parse($activity->created_at)->format('M d, Y'),
                    'utc_time' => Carbon::parse($activity->created_at)->format('g:i A'),
                    'student_tz_date' => Carbon::parse($activity->created_at)->setTimezone($activity->student_time_zone)->format('M d, Y'),
                    'student_tz_time' => Carbon::parse($activity->created_at)->setTimezone($activity->student_time_zone)->format('g:i A'),
                    'teacher_tz_date' => Carbon::parse($activity->created_at)->setTimezone($activity->teacher_time_zone)->format('M d, Y'),
                    'teacher_tz_time' => Carbon::parse($activity->created_at)->setTimezone($activity->teacher_time_zone)->format('g:i A')
                )
            );
        }
        return $data;
    }

    /**
     * Check if the student has credit for the class
     * 
     * @param $currentClassId
     * @param $availableCredits
     * @param $classType
     * @return mixed
     */
    private function checkCreditWithClass($currentClassId, $availableCredits, $classType) 
    {
        $status = false;
        foreach($availableCredits as $credit) {
            $type = $credit['class_type'];
            $typeArr = explode('\\', $type);
            $currentType = end($typeArr);
            if($credit['class_id'] == $currentClassId && $currentType == $classType) {
                $status = true;
                break;
            }
        }
        return $status;
    }

    /**
     * Get all previous classes for a teacher
     * 
     * @param $teacherId
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function teacherPreviousClasses($teacherId, $page, $limit) 
    {
        $currentDateTime = Carbon::now('UTC')->subMinutes(30)->format('Y-m-d H:i:s');
        $previousClasses = array();
        $previousTrialClasses = array();
        $queryTotalPreviousClassesCount = "call spTeacherPreviousClassesCount($teacherId, '$currentDateTime');";
        $resultSet = DB::select($queryTotalPreviousClassesCount);
        $totalPreviousCount = $resultSet[0]->count;

        $query = "call spTeacherPreviousTrialClasses($teacherId, '$currentDateTime', $page, $limit);";
        $result = DB::select($query);
        
        foreach($result as $previous) {
            $teacherTimeZone = $previous->teacher_timezone;
            $data = array(
                'class_id' => $previous->tw_class_id,
                'course_title' => $previous->course_title,
                'class_time' => Carbon::parse($previous->class_time)->setTimezone($teacherTimeZone)->format('g:i A'),
                'date' => Carbon::parse($previous->class_time)->setTimezone($teacherTimeZone)->format('M d, Y'),
                'time' => Carbon::parse($previous->class_time)->setTimezone($teacherTimeZone)->format('g:i A'),
                'teacher_name' => $this->decryptValue($previous->teacher_name),
                'student_name' => $this->decryptValue($previous->student_name),
                'status' => $previous->status == 'trial_scheduled' ? 'scheduled' : $previous->status,
                'teacher_time_zone' => $teacherTimeZone,
                'student_timezone' => $previous->student_timezone,
                'student_id' => $previous->student_id,
                'teacher_id' => $previous->teacher_id
            );
            $previousTrialClasses[] = $data;
        }
        
        $previousWeeklyClasses = array();
        $query = "call spTeacherPreviousWeeklyClasses($teacherId, '$currentDateTime', $page, $limit);";
        $result = DB::select($query);
        
        foreach($result as $previous) {
            $teacherTimeZone = $previous->teacher_timezone;
            $data = array(
                'class_id' => $previous->tw_class_id,
                'course_title' => $previous->course_title,
                'class_time' => Carbon::parse($previous->class_time)->setTimezone($teacherTimeZone)->format('g:i A'),
                'date' => Carbon::parse($previous->class_time)->setTimezone($teacherTimeZone)->format('M d, Y'),
                'time' => Carbon::parse($previous->class_time)->setTimezone($teacherTimeZone)->format('g:i A'),
                'teacher_name' => $this->decryptValue($previous->teacher_name),
                'student_name' => $this->decryptValue($previous->student_name),
                'status' => $previous->status,
                'teacher_time_zone' => $teacherTimeZone,
                'student_time_zone' => $previous->student_timezone,
                'student_id' => $previous->student_id,
                'teacher_id' => $previous->teacher_id
            );
            $previousWeeklyClasses[] = $data;
        }

        $previousClasses = array(
            'total_previous_classes' => $totalPreviousCount,
            'previous_trial_classes' => $previousTrialClasses,
            'previous_weekly_classes' => $previousWeeklyClasses
        );
        
        return $previousClasses;
    }

    /**
     * Get all upcoming classes for a teacher
     * 
     * @param $teacherId
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function teacherUpcomingClasses($teacherId, $page, $limit) 
    {
        $currentDateTime = Carbon::now('UTC')->subMinutes(30)->format('Y-m-d H:i:s');
        $upcomingClasses = array();
        $upcomingTrialClasses = array();
       
        $upcomingMakeupRequests = $this->getUpcomingMakeupRequests($teacherId, $currentDateTime);

        $queryTotalPreviousClassesCount = "call spTeacherUpcomingClassesCount($teacherId, '$currentDateTime');";
        $resultSet = DB::select($queryTotalPreviousClassesCount);
        $totalUpcomingCount = $resultSet[0]->count;

        $query = "call spTeacherUpcomingTrialClasses($teacherId, '$currentDateTime', $page, $limit);";
        $result = DB::select($query);
        
        foreach($result as $upcoming) 
        {
            $teacherTimeZone = $upcoming->teacher_timezone;
            $timeStampsUnix = GeneralHelper::convertDateTimeToUnixTimestamp($upcoming->class_time);
            $makeupStatus = $this->checkUpcomingClassStatusInMakeup($upcomingMakeupRequests, 'App\Models\TrialClass', $upcoming->tw_class_id);
            $data = array(
                'student_id' => $upcoming->student_id,
                'teacher_id' => $upcoming->teacher_id,
                'class_id' => $upcoming->tw_class_id,
                'course_id' => $upcoming->course_id,
                'course_title' => $upcoming->course_title,
                'class_time' => Carbon::parse($upcoming->class_time)->setTimezone($teacherTimeZone)->format('g:i A'),
                'date' => Carbon::parse($upcoming->class_time)->setTimezone($teacherTimeZone)->format('M d, Y'),
                'time' => Carbon::parse($upcoming->class_time)->setTimezone($teacherTimeZone)->format('g:i A'),
                'teacher_name' => $this->decryptValue($upcoming->teacher_name),
                'student_name' => $this->decryptValue($upcoming->student_name),
                'status' => $upcoming->status == 'trial_scheduled' ? 'scheduled' : $upcoming->status,
                'class_time_unix' => $timeStampsUnix['classDateTimeUnix'],
                'current_time_unix' => $timeStampsUnix['currentDateTimeUnix'],
                'student_time_zone' => $upcoming->student_timezone,
                'teacher_time_zone' => $teacherTimeZone,
                'makeup_request_is_in_progress' => $makeupStatus
            );
            $upcomingTrialClasses[] = $data;
        }

        $upcomingWeeklyClasses = array();
        $query = "call spTeacherUpcomingWeeklyClasses($teacherId, '$currentDateTime', $page, $limit);";
        $result = DB::select($query);
        
        foreach($result as $upcoming) 
        {
            $teacherTimeZone = $upcoming->student_timezone;
            $timeStampsUnix = GeneralHelper::convertDateTimeToUnixTimestamp($upcoming->class_time);

            $makeupStatus = $this->checkUpcomingClassStatusInMakeup($upcomingMakeupRequests, 'App\Models\WeeklyClass', $upcoming->tw_class_id);

            $data = array(
                'student_id' => $upcoming->student_id,
                'teacher_id' => $upcoming->teacher_id,
                'class_id' => $upcoming->tw_class_id,
                'course_id' => $upcoming->course_id,
                'course_title' => $upcoming->course_title,
                'class_time' => Carbon::parse($upcoming->class_time)->setTimezone($teacherTimeZone)->format('g:i A'),
                'date' => Carbon::parse($upcoming->class_time)->setTimezone($teacherTimeZone)->format('M d, Y'),
                'time' => Carbon::parse($upcoming->class_time)->setTimezone($teacherTimeZone)->format('g:i A'),
                'teacher_name' => $this->decryptValue($upcoming->teacher_name),
                'student_name' => $this->decryptValue($upcoming->student_name),
                'status' => $upcoming->status,
                'class_time_unix' => $timeStampsUnix['classDateTimeUnix'],
                'current_time_unix' => $timeStampsUnix['currentDateTimeUnix'],
                'teacher_time_zone' => $teacherTimeZone,
                'student_time_zone' => $upcoming->student_timezone,
                'makeup_request_is_in_progress' => $makeupStatus
            );
            $upcomingWeeklyClasses[] = $data;
        }
        
        $upcomingClasses = array(
            'total_upcoming_count' => $totalUpcomingCount,
            'upcoming_trial_classes' => $upcomingTrialClasses,
            'upcoming_weekly_classes' => $upcomingWeeklyClasses
        );
        
        return $upcomingClasses;
    }

    public function getById($classId) {
        return $this->model::find($classId);
    }

    private function getUpcomingMakeupRequests($teacherId, $currentDateTime) {
        $upcomingMakeupRequests = array();
        
        $makeupRequests = MakeupRequest::select('class_id', 'class_type', 'status')
            ->where('makeup_date_time', '>', $currentDateTime)
            ->where('status', 'pending')
            ->whereHas('studentCourse', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })->with('studentCourse')->get();
            foreach($makeupRequests as $makeupRequest) {
                $upcomingMakeupRequests[] = array(
                    'class_id' => $makeupRequest->class_id,
                    'class_type' => $makeupRequest->class_type,
                    'status' => $makeupRequest->status
                );
            }

        return $upcomingMakeupRequests;
    }

    private function checkUpcomingClassStatusInMakeup($upcomingMakeupRequests, $classType, $oldClassId) {
        $status = false;
        foreach($upcomingMakeupRequests as $makeupRequest) {
            if($oldClassId == $makeupRequest['class_id'] && $makeupRequest['class_type'] == $classType) {
                $status = true;
                break;
            }
        }
        return $status;
    }

    //todays weekly classes of teachers
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
        $oneMonthLater = $today->copy()->addDays(30)->setTimezone('UTC');

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
                        'course_title' => $class->routineClass->studentCourse->course->title,
                        'class_time' => Carbon::parse($class->class_time)->setTimezone($timezone)->format('g:i A') . ' - ' . Carbon::parse($class->class_time)->addMinutes(30)->setTimezone($timezone)->format('g:i A'),
                        'teacher_name' => $class->Teacher()->first()->name,
                        'student_name' => $class->Student()->first()->name,
                        'teacher_profile_picture' => $class->Teacher()->first()->profile_photo_path,
                        'date' => Carbon::parse($class->class_time)->setTimezone($timezone)->format('d M'),
                        'class_type' => 'regular class'
                    ];
                }
            });
    }

    /**
     * the total number of upcoming weekly classes count for a teacher
     * 
     */
    public function getTotalUpcomingClasses($teacherIds) {
        return $this->model::whereIn('teacher_id', $teacherIds)
            ->where('class_time', '>=', Carbon::now('UTC')->format('Y-m-d H:i:s'))
            ->count();
    }

    /**
     * Get all upcoming weekly classes for a teacher
     * with pagination
     * 
     * @param $teacherId
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function getWeeklyClassesForAllTeachers($teacherIds, $currentDateTime, $limit, $offset) {
        return $this->model::where('class_time', '>=', $currentDateTime)
            ->whereIn('teacher_id', $teacherIds)
            ->orderBy('class_time', 'asc')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    /**
     * the total number of previous weekly classes count for all teachers
     * 
     */
    public function getTotalPreviousClasses($teacherIds) {
        return $this->model::whereIn('teacher_id', $teacherIds)
            ->where('class_time', '<', Carbon::now('UTC')->format('Y-m-d H:i:s'))
            ->count();
    }

    /**
     * Get all previous weekly classes for all teachers
     * with pagination
     * 
     * @param $teacherId
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function getPreviousWeeklyClassesForAllTeachers($teacherIds, $currentDateTime, $limit, $offset) {
        return $this->model::where('class_time', '<', $currentDateTime)
            ->whereIn('teacher_id', $teacherIds)
            ->orderBy('class_time', 'asc')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

}