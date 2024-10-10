<?php

namespace App\Services;

use App\Helpers\GeneralHelper;
use App\Jobs\SendTrialCancelEmails;
use App\Repository\MakeupRequestRepository;
use App\Repository\NotificationRepository;
use App\Repository\TrialClassRepository;
use App\Repository\WeeklyClassRepository;
use App\Traits\DecryptionTrait;
use Carbon\Carbon;
use Mockery\Matcher\Not;

class ClassesService
{
    private $makeupRequestRepository;
    private $weeklyClassesRepository;
    private $trialClassesRepository;
    private $notificationRepository;

    use DecryptionTrait;

    public function __construct(
        MakeupRequestRepository $makeupRequestRepository,
        WeeklyClassRepository $weeklyClassesRepository,
        TrialClassRepository $trialClassesRepository,
        NotificationRepository $notificationRepository
    )
    {
        $this->makeupRequestRepository = $makeupRequestRepository;
        $this->weeklyClassesRepository = $weeklyClassesRepository;
        $this->trialClassesRepository = $trialClassesRepository;
        $this->notificationRepository = $notificationRepository;
    }

    public function coordinatedTeacherUpcomingClasses($teacherIds, $offset, $limit) {
        
        $currentDateTime = Carbon::now('UTC')->format('Y-m-d H:i:s');
        $upcomingClasses = array();
        $upcomingTrialClasses = array();
        
        $totalWeeklyCount = $this->weeklyClassesRepository->getTotalUpcomingClasses($teacherIds);
        $totalTrialCount = $this->trialClassesRepository->getTotalUpcomingClasses($teacherIds);

        $totalUpcomingCount = $totalWeeklyCount + $totalTrialCount;
        $result = $this->trialClassesRepository->getUpcomingClassesForAllTeachers($teacherIds, $currentDateTime, $limit, $offset);
        
        foreach($result as $upcoming) 
        {
            $teacherTimeZone = $upcoming->teacher->timezone;
            $timeStampsUnix = GeneralHelper::convertDateTimeToUnixTimestamp($upcoming->class_time);
            // $makeupStatus = $upcoming->makeupRequest->status == 'pending' ? true : false;
            $makeupStatus = $upcoming->makeupRequest?->status === 'pending' ? true : false;
            $data = array(
                'student_id' => $upcoming->student_id,
                'teacher_id' => $upcoming->teacher_id,
                'class_id' => $upcoming->id,
                'course_id' => $upcoming->studentCourse->course->id,
                'course_title' => $upcoming->studentCourse->course->title,
                'class_time' => Carbon::parse($upcoming->class_time)->setTimezone($teacherTimeZone)->format('g:i A'),
                'date' => Carbon::parse($upcoming->class_time)->setTimezone($teacherTimeZone)->format('M d, Y'),
                'time' => Carbon::parse($upcoming->class_time)->setTimezone($teacherTimeZone)->format('g:i A'),
                'teacher_name' => $this->decryptValue($upcoming->teacher->name),
                'student_name' => $this->decryptValue($upcoming->student->name),
                'customer_name' => $this->decryptValue($upcoming->student->user->name),
                'status' => $upcoming->status === 'trial_scheduled' ? 'scheduled' : $upcoming->status,
                'class_time_unix' => $timeStampsUnix['classDateTimeUnix'],
                'current_time_unix' => $timeStampsUnix['currentDateTimeUnix'],
                'student_time_zone' => $upcoming->student->timezone,
                'teacher_time_zone' => $teacherTimeZone,
                'makeup_request_is_in_progress' => $makeupStatus
            );
            $upcomingTrialClasses[] = $data;
        }

        $upcomingWeeklyClasses = array();
        $result = $this->weeklyClassesRepository->getWeeklyClassesForAllTeachers($teacherIds, $currentDateTime, $limit, $offset);
        
        foreach($result as $upcoming) 
        {
            $timeStampsUnix = GeneralHelper::convertDateTimeToUnixTimestamp($upcoming->class_time);
            // $makeupStatus = $upcoming->makeupRequest->status == 'pending' ? true : false;
            $makeupStatus = $upcoming->makeupRequest?->status === 'pending' ? true : false;
            $data = array(
                'student_id' => $upcoming->student_id,
                'teacher_id' => $upcoming->teacher_id,
                'class_id' => $upcoming->id,
                'course_id' => $upcoming->routineClass->studentCourse->course->id,
                'course_title' => $upcoming->routineClass->studentCourse->course->title,
                'class_time' => Carbon::parse($upcoming->class_time)->setTimezone($teacherTimeZone)->format('g:i A'),
                'date' => Carbon::parse($upcoming->class_time)->setTimezone($teacherTimeZone)->format('M d, Y'),
                'time' => Carbon::parse($upcoming->class_time)->setTimezone($teacherTimeZone)->format('g:i A'),
                'teacher_name' => $this->decryptValue($upcoming->Teacher->name),
                'student_name' => $this->decryptValue($upcoming->student->name),
                'customer_name' => $this->decryptValue($upcoming->user->name),
                'status' => $upcoming->status,
                'class_time_unix' => $timeStampsUnix['classDateTimeUnix'],
                'current_time_unix' => $timeStampsUnix['currentDateTimeUnix'],
                'teacher_time_zone' => $upcoming->Teacher->timezone,
                'student_time_zone' => $upcoming->student->timezone,
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

    public function coordinatedTeacherPreviousClasses($teacherIds, $offset, $limit) {
        
        $currentDateTime = Carbon::now('UTC')->format('Y-m-d H:i:s');
        $upcomingClasses = array();
        $previousTrialClasses = array();
        
        $totalWeeklyCount = $this->weeklyClassesRepository->getTotalPreviousClasses($teacherIds);
        $totalTrialCount = $this->trialClassesRepository->getTotalPreviousClasses($teacherIds);

        $totalPreviousCount = $totalWeeklyCount + $totalTrialCount;
        $result = $this->trialClassesRepository->getPreviousClassesForAllTeachers($teacherIds, $currentDateTime, $limit, $offset);
        
        foreach($result as $previous) 
        {
            $teacherTimeZone = $previous->teacher->timezone;
            $makeupStatus = $previous->makeupRequest?->status === 'pending' ? true : false;
            $data = array(
                'student_id' => $previous->student_id,
                'teacher_id' => $previous->teacher_id,
                'class_id' => $previous->id,
                'course_id' => $previous->studentCourse?->course?->id ?? null,
                'course_title' => $previous->studentCourse?->course?->title ?? null,
                'class_time' => Carbon::parse($previous->class_time)->setTimezone($teacherTimeZone)->format('g:i A'),
                'date' => Carbon::parse($previous->class_time)->setTimezone($teacherTimeZone)->format('M d, Y'),
                'time' => Carbon::parse($previous->class_time)->setTimezone($teacherTimeZone)->format('g:i A'),
                'teacher_name' => $this->decryptValue($previous->teacher->name),
                'student_name' => $this->decryptValue($previous->student->name),
                'customer_name' => $this->decryptValue($previous->student->user->name),
                'status' => $previous->status === 'trial_scheduled' ? 'scheduled' : $previous->status,
                'student_time_zone' => $previous->student->timezone,
                'teacher_time_zone' => $teacherTimeZone,
                'makeup_request_is_in_progress' => $makeupStatus
            );
            $previousTrialClasses[] = $data;
        }

        $previousWeeklyClasses = array();
        $result = $this->weeklyClassesRepository->getPreviousWeeklyClassesForAllTeachers($teacherIds, $currentDateTime, $limit, $offset);
        
        foreach($result as $previous) 
        {
            $makeupStatus = $previous->makeupRequest?->status === 'pending' ? true : false;
            $data = array(
                'student_id' => $previous->student_id,
                'teacher_id' => $previous->teacher_id,
                'class_id' => $previous->id,
                'course_id' => $previous->routineClass?->studentCourse?->course?->id ?? null,
                'course_title' => $previous->routineClass?->studentCourse?->course?->title ?? null,
                'class_time' => Carbon::parse($previous->class_time)->setTimezone($teacherTimeZone)->format('g:i A'),
                'date' => Carbon::parse($previous->class_time)->setTimezone($teacherTimeZone)->format('M d, Y'),
                'time' => Carbon::parse($previous->class_time)->setTimezone($teacherTimeZone)->format('g:i A'),
                'teacher_name' => $this->decryptValue($previous->Teacher->name),
                'student_name' => $this->decryptValue($previous->student->name),
                'customer_name' => $this->decryptValue($previous->user->name),
                'status' => $previous->status,
                'teacher_time_zone' => $previous->Teacher->timezone,
                'student_time_zone' => $previous->student->timezone,
                'makeup_request_is_in_progress' => $makeupStatus
            );
            $previousWeeklyClasses[] = $data;
        }
        
        $previousClasses = array(
            'total_previous_count' => $totalPreviousCount,
            'previous_trial_classes' => $previousTrialClasses,
            'previous_weekly_classes' => $previousWeeklyClasses
        );
        
        return $previousClasses;

    }

    public function cancelClass($classId, $classType) 
    {
        if($classType === 'trial') {
            $class = $this->trialClassesRepository->cancelClass($classId);
        } else {
            $class = $this->weeklyClassesRepository->cancelClass($classId);
        }

        // send emails to customer, teacher, coordinator and scheduling team
        $class_datetime_std_tz = GeneralHelper::convertTimeToUserTimezone($class->class_time, $class->Student->timezone);
        $class_datetime_tchr_tz = GeneralHelper::convertTimeToUserTimezone($class->class_time, $class->teacher->timezone);
        
        $emailData = [
            'customer_name' => $class->Student->user->name,
            'customer_email' => $class->Student->user->email,
            'student_name' => $class->Student->name,
            'class_datetime_std_tz' => Carbon::parse($class_datetime_std_tz)->format('Y-m-d h:i A'),
            'teacher_name' => $class->teacher->name,
            'teacher_email' => $class->teacher->email,
            'class_datetime_tchr_tz' => Carbon::parse($class_datetime_tchr_tz)->format('Y-m-d h:i A'),
            'coordinator_name' => $class->teacher->teacherCoordinator->name,
            'coordinator_email' => $class->teacher->teacherCoordinator->email,
        ];

        dispatch(new SendTrialCancelEmails($emailData));

        // create notification for student
        $notificationData = [
            'user_id' => $class->Student->user->id,
            'student_id' => $class->student_id,
            'type' => 'class_cancelled',
            'message' => 'A trial class has been cancelled for ' .$class->Student->name. ' on ' .Carbon::parse($class_datetime_std_tz)->format('Y-m-d h:i A'),
        ];
        $this->notificationRepository->create($notificationData);

        return $class;
    }
}