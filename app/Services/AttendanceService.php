<?php

namespace App\Services;

use App\Classes\Enums\StatusEnum;
use App\Helpers\GeneralHelper;
use App\Jobs\SendMailToCustomerWhenStudentTeacherJoinTrialClass;
use App\Jobs\SendMailToSchedulingTeamWhenStudentTeacherJoinsTrialClass;
use App\Jobs\SendMailToTeacherWhenStudentJoinTrialClass;
use App\Jobs\SendTrailSuccessMailToCustomer;
use App\Models\Student;
use App\Models\TrialClass;
use App\Models\User;
use App\Models\WeeklyClass;
use App\Repository\AttendanceRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    private $attendanceRepository;

    public function __construct(AttendanceRepository $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    /**
     * Create attendance when a student or teacher joins a class
     *
     * @param int $classId
     * @param string $classType
     * @param int $userId
     * @return \App\Models\Attendance
     */
    public function createAttendanceOnClassJoin($classId, $classType, $userId)
    {
        if(auth()->user()->user_type === 'teacher')
        {
            $person = User::find($userId);
            $presence = 'teacher_presence';
            $user_status = 'teacher_status';
        } else {
            $person = Student::find($userId);
            $presence = 'student_presence';
            $user_status = 'student_status';
        }

        $class = $classType === 'regular' ?
            WeeklyClass::find($classId) : TrialClass::find($classId);
            
        $joinedAt = Carbon::now('UTC')->format('Y-m-d H:i:s');
        $attendanceData = [
            'joined_at'  => $joinedAt,
            'created_at' => $joinedAt,
        ];

        $attendance = $this->attendanceRepository->create($attendanceData);

        $person->attendance()->save($attendance);
        $class->attendance()->save($attendance);

        $data = [
            $presence => 1,
            $user_status => 'absent',
        ];

        $class->update($data);
        $class->refresh();

        // Dispatch the job to send an email to the scheduling team
        // if the class is a trial class and both the student and teacher have joined
        if (
            $classType === 'trial' && 
            $class->student_presence && $class->teacher_presence
            && !$class->email_sent_on_teacher_student_join    
        ) 
        {
            // conversion in student timezone
            $carbonInstanceStudent = GeneralHelper::convertTimeToUserTimezone($class->class_time, $class->Student->timezone);
            $studentDateTime = Carbon::parse($carbonInstanceStudent)->format('Y-m-d h:i A');

            $classData = [
                'customer_name'  => $class->Student->user->name,
                'customer_email' => $class->Student->user->email,
                'class_time'     => $studentDateTime,
                'student_name'   => $class->Student->name,
                'teacher_name'   => $class->teacher->name,
                'course'         => $class->studentCourse->course->title
            ];

            dispatch(new SendMailToSchedulingTeamWhenStudentTeacherJoinsTrialClass($classData));
            dispatch(new SendMailToCustomerWhenStudentTeacherJoinTrialClass($classData));

            $class->update(['email_sent_on_teacher_student_join' => 1]);
        }

        // Dispatch the job to send an email to the teacher if student joined and teacher is absent
        if ($classType === 'trial' && $class->student_presence && !$class->teacher_presence)
        {
            // conversion in teacher timezone
            $carbonInstanceTeacher = GeneralHelper::convertTimeToUserTimezone($class->class_time, $class->teacher->timezone);
            $teacherDateTime = Carbon::parse($carbonInstanceTeacher)->format('Y-m-d h:i A');
            
            $classData = [
                'customer_name'  => $class->Student->user->name,
                'customer_email' => $class->Student->user->email,
                'class_time'     => $teacherDateTime,
                'student_name'   => $class->Student->name,
                'teacher_name'   => $class->teacher->name,
                'course'         => $class->studentCourse->course->title
            ];

            dispatch(new SendMailToTeacherWhenStudentJoinTrialClass($classData));
        }

        return $attendance;
    }

    public function updateAttendanceOnClassLeave($classId, $classType, $userId)
    {
        $attendance = null;
        
        if ($classType === 'trial') {
            $class_time_col = 'starts_at';
            $class = TrialClass::find($classId);
        } else {
            $class_time_col = 'class_time';
            $class = WeeklyClass::find($classId);
        }

        // Record the left time
        $leftAt = Carbon::now('UTC')->format('Y-m-d H:i:s');

        // Check user type and set variables accordingly
        if(auth()->user()->user_type === 'teacher') {
            $person = User::find($userId);

            $attendance = $person->attendance()->latest()->first();

            if ($attendance) {
                $attendance->left_at = $leftAt;
                $attendance->save();

                // Calculate the duration between joined_at and left_at
                $joinTime = new Carbon($attendance->joined_at);
                $durationInClass = $joinTime->diffInMinutes($leftAt);

                // Check if teacher joined within 10 minutes of class start time
                $classStartTime = new Carbon($class->{$class_time_col});
                $joinDelay = $classStartTime->diffInMinutes($joinTime, false);

                // Determine attendance status
                $attendanceStatus = ($joinDelay < 10 && $durationInClass >= 30) ? 'present' : 'absent';
                $data = ['teacher_status' => $attendanceStatus];

                // make trial successful as per teacher's attendance
                if ($classType === 'trial' && $attendanceStatus === 'present')
                {
                    $data['status'] = StatusEnum::TrialSuccessful;
                    $class->update(['status' => StatusEnum::TrialSuccessful]);

                    $classData = [
                        'customer_name'  => $class->Student->user->name,
                        'customer_email' => $class->Student->user->email,
                        'student_name'   => $class->Student->name,
                        'teacher_name'   => $class->teacher->name,
                        'coordinator_name' => $class->teacher->teacherCoordinator->name,
                        'coordinator_email' => $class->teacher->teacherCoordinator->email,
                        'course'         => $class->studentCourse->course->title
                    ];

                    // send email
                    dispatch(new SendTrailSuccessMailToCustomer($classData));
                }

                $class->update($data);
            }
        }

        // If 'customer' user type
        if(auth()->user()->user_type === 'customer')
        {
            $person = Student::find($userId);
            $attendance = $person->attendance()->latest()->first();
            $class->update(['student_status' => 'present']);

            if ($attendance) {
                $attendance->left_at = $leftAt;
                $attendance->save();
            }
        }

        return $attendance;
    }

    public function getClassAttendanceLogs($classId, $classType)
    {
        $class = $classType === 'regular' ?
            WeeklyClass::find($classId) : TrialClass::find($classId);

        $attendanceLogs = [];

        // Map attendance records
        foreach ($class->attendance as $attendance) {
            $userType = $attendance->person_type === 'App\Models\User' ? 'teacher' : 'student';
        
            $attendanceLogs[] = [
                'user' => $userType,
                'text' => ucfirst($userType) . ' Joined the class',
                'time' => $attendance->created_at->format('h:i A'),
            ];
        
            $attendanceLogs[] = [
                'user' => $userType,
                'text' => ucfirst($userType) . ' has left the class',
                'time' => $attendance->left_at->format('h:i A'),
            ];
        }
        
        // sort the logs by time
        // usort($attendanceLogs, function ($a, $b) {
        //     return strtotime($a['time']) - strtotime($b['time']);
        // });
        
        return $attendanceLogs;
    }
}