<?php

namespace App\Http\Controllers;

use App\Classes\Enums\UserTypesEnum;
use App\Http\Requests\AttendanceRequest;
use App\Models\Student;
use App\Models\TrialClass;
use App\Models\WeeklyClass;
use App\Repository\AttendanceRepository;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    private $attendanceRequest;
    private $attendanceRepository;
    private $attendanceService;

    public function __construct(
        AttendanceRequest $attendanceRequest,
        AttendanceRepository $attendanceRepository,
        AttendanceService $attendanceService
    )
    {
        $this->attendanceRequest = $attendanceRequest;
        $this->attendanceRepository = $attendanceRepository;
        $this->attendanceService = $attendanceService;
    }

    /**
     * Get attendance for a student in a course for a given month
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAttendanceForCourse(Request $request)
    {
        $this->attendanceRequest->validateAttendanceForCourse(request());

        $studentId = $request->student_id;
        $courseId = $request->course_id;
        $month = $request->month;

        $attendance = $this->attendanceRepository->getAttendanceForCourse($studentId, $courseId, $month);

        return $this->success($attendance, 'Attendance fetched successfully', 200);
    }

    /**
     * Get attendance for a teacher for a given month
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAttendanceForTeacher(Request $request)
    {
        $this->attendanceRequest->validateAttendanceForTeacher(request());

        $teacherId = $request->teacher_id;
        $month = $request->month;

        $attendance = $this->attendanceRepository
            ->getAttendanceForTeacher($teacherId, $month);

        return $this->success($attendance, 'Attendance fetched successfully', 200);
    }

    /**
     * create class attendance when user join class
     * @param AttendanceRequest $request
     * @return string
     */
    public function createAttendanceOnJoin(Request $request)
    {
        $this->attendanceRequest->validateCreateAttendanceForClass(request());
        
        $classId   = $request->class_id;
        $classType = $request->class_type;
        $userId    = $request->user_id;

        DB::beginTransaction();

        try {
            $attendance = $this->attendanceService->createAttendanceOnClassJoin($classId, $classType, $userId);

            DB::commit();

            return $this->success($attendance, 'Attendance Successfully added', 201);
        } catch (\Exception $e) 
        {
            DB::rollBack();
            return $this->error($e->getMessage(), 500);
        }
    }

    /** Update last class attendance when user leave class
     * @param AttendanceRequest $request
     * @return string
     */
    public function createAttendanceOnLeave(Request $request)
    {
        $this->attendanceRequest->validateCreateAttendanceForClass(request());

        $classId   = $request->class_id;
        $classType = $request->class_type;
        $userId    = $request->user_id;

        DB::beginTransaction();

        try {
            $attendance = $this->attendanceService->updateAttendanceOnClassLeave($classId, $classType, $userId);

            DB::commit();

            return $this->success($attendance, 'Attendance Successfully added', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * get class attendance for a class
     * @param AttendanceRequest $request
     * @return string
     */
    public function getClassAttendanceLogs(Request $request)
    {
        $this->attendanceRequest->validateGetClassAttendanceLogs(request());

        $classId   = $request->class_id;
        $classType = $request->class_type;

        $attendanceLogs = $this->attendanceService->getClassAttendanceLogs($classId, $classType);

        return $this->success($attendanceLogs, 'Attendance Successfully added', 200);
    }

    public function trialSuccessNotification($student)
    {
        // $type = StatusEnum::TrialSuccessful;
        // $msg = "Trial has been successfull agains't to";

        // $notification = [
        //     'user_id' => $student['user']['id'],
        //     'studentName' => $student['name'],
        // ];

        // generate_notification_by_type(
        //     $type,
        //     [
        //         'user_id' => $student['user']['id'],
        //         'student_id' => $student['id'],
        //         'remindable' => 1,
        //         'remind_at' => Carbon::now(),
        //     ],
        //     [
        //         'type' => $type,
        //         $type => $notification,
        //         'activity' => $msg . $student['user']['name'],
        //         'created_at' => now(),
        //     ]
        // );
    }


    /**
     * Get attendance for a student, weekly class
     * @param Student $student
     * @param User $teacher
     * @param $weeklyClassId
     * @return JsonResponse
     */
    public function studentAttendance(
        Student $student, User $teacher, 
        $classId, $trialClass = false 
    )
    {
        try {
            $classType = $trialClass ? 'App\Models\TrialClass' : 'App\Models\WeeklyClass';

            $attendances = $student->attendance()
                ->with('class:id,student_status')
                ->where('class_type', $classType)
                ->where('class_id', $classId)
                ->get(['attendances.*',
                    DB::raw("ifnull(TIMESTAMPDIFF(SECOND, attendances.created_at, attendances.left_at), 0) AS duration_seconds")]);
                   
            $timeZone = $this->setTimeZone($student, $teacher);

            $attendanceData = $attendances->map(function ($attendance) use ($timeZone) {
                return [
                    'id' => $attendance->id,
                    'join_at_time' => Carbon::createFromFormat('Y-m-d H:i:s', $attendance->created_at, 'UTC')
                        ->setTimezone($timeZone)
                        ->toDateTimeString(),
                    'leave_at_time' => $attendance->left_at == null ? 'N.A' :
                        Carbon::createFromFormat('Y-m-d H:i:s', $attendance->left_at, 'UTC')
                            ->setTimezone($timeZone)
                            ->toDateTimeString(),
                    'duration_seconds' => $attendance->duration_seconds,
                    'student_status' => optional($attendance->class)->student_status
                ];
            });

            return response()->json(['attendance' => $attendanceData]);
        } catch (\Exception $e)
        {
            Log::error('Error while get attendance for Student', ['Exception:' => $e->getMessage()]);
        }
    }

    /**
     * Get attendance for a student, weekly class
     * @param User $teacher
     * @param $student
     * @param $weeklyClassId
     * @return JsonResponse
     */
    public function teacherAttendance(
        User $teacher, Student $student, 
        $classId, $trialClass = false
    )
    {
        try {
            $classType = $trialClass ? 'App\Models\TrialClass' : 'App\Models\WeeklyClass';

            $attendances = $teacher->attendance()
                ->with('class:id,teacher_status')
                ->where('class_type', $classType)
                ->where('class_id', $classId)
                ->get(['attendances.*',
                    DB::raw("ifnull(TIMESTAMPDIFF(SECOND, attendances.created_at, attendances.left_at), 0) AS duration_seconds")]);

            $timeZone = $this->setTimeZone($student, $teacher);

            $attendanceData = $attendances->map(function ($attendance) use ($timeZone) {
                return [
                    'id' => $attendance->id,
                    'join_at_time' => Carbon::createFromFormat('Y-m-d H:i:s', $attendance->created_at, 'UTC')
                        ->setTimezone($timeZone)
                        ->toDateTimeString(),
                    'leave_at_time' => $attendance->left_at == null ? 'N.A' :
                        Carbon::createFromFormat('Y-m-d H:i:s', $attendance->left_at, 'UTC')
                            ->setTimezone($timeZone)
                            ->toDateTimeString(),
                    'duration_seconds' => $attendance->duration_seconds,
                    'teacher_status' => optional($attendance->class)->teacher_status
                ];
            });

            return response()->json(['attendance' => $attendanceData]);
        } catch (\Exception $e)
        {
            Log::error('Error while get attendance for Teacher', ['Exception:' => $e->getMessage()]);
        }
    }

    /**
     * Set the timezone based on whether the
     * logged-in user is a teacher, a student,
     * or admin
     *
     * @param $student
     * @param $teacher
     * @return string
     */
    public function setTimeZone($student, $teacher)
    {
        $user = Auth::user();
        $timeZone = 'UTC';

        switch ($user->user_type) {
            case UserTypesEnum::Customer:
                $timeZone = $student->timezone;
                break;
            case UserTypesEnum::Teacher:
                $timeZone = $teacher->timezone;
                break;
            case UserTypesEnum::Admin:
            case UserTypesEnum::Sales:
            case UserTypesEnum::CustomerSupport:
            case UserTypesEnum::TeacherCoordinator:
            case UserTypesEnum::TC:
                $timeZone = $user->timezone;
                break;
        }

        return $timeZone;
    }
}