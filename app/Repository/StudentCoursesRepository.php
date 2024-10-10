<?php
namespace App\Repository;

use App\Classes\Enums\CommonEnum;
use App\Classes\Enums\StatusEnum;
use App\Helpers\GeneralHelper;
use App\Http\Resources\CourseResource;
use App\Jobs\MakeupRequestAcceptRejectEmails;
use App\Models\MakeupRequest;
use App\Models\Student;
use App\Models\StudentCourse;
use Carbon\Carbon;
use App\Models\CreditHistory;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentCoursesRepository
{
    protected $model;

    public function __construct(StudentCourse $studentCourse)
    {
        $this->model = $studentCourse;
    }

    public function updateStudentCourse($studentCourseId, $data)
    {
        return $this->model->where('id', $studentCourseId)->update($data);
    }

    public function addStudentCourse($data)
    {
        return $this->model->create($data);
    }

    public function getStudentCourse($studentId)
    {
        return $this->model->where('student_id', $studentId)->first();
    }

    public function getStudentCourseByCourseId($studentId, $courseId)
    {
        return $this->model->where('student_id', $studentId)->where('course_id', $courseId)->first();
    }

    public function getByTeacherId($teacherId)
    {
        return $this->model->where('teacher_id', $teacherId)->pluck('id')->toArray();
    }

    public function getStudentCourses($studentId)
    {
        return $this->model->where('student_id', $studentId)
            ->whereHas('course', function ($query) {
                $query->where('status', 'active');
            })
            ->whereHas('subscription', function ($query) {
                $query->where('payment_status', 'succeeded');
            })
            ->with('course')
            ->get()
            ->map(function ($studentCourse) {
                return $studentCourse->course;
            });
    }

    public function getStudentsForATeacher($teacherId)
    {
        return $this->model
            ->whereHas('teacher', function ($query) use ($teacherId) {
                $query->where('id', $teacherId);
            })
            ->whereHas('subscription', function ($query) {
                $query->where('payment_status', 'succeeded');
            })
            ->with('student')
            ->get()
            ->map(function ($studentCourse) {
                return $studentCourse->student;
            });
    }

    public function getStudentCourseSlotIds($studentId, $courseId)
    {
        $studentCourse = $this->model::where(['student_id' => $studentId, 'course_id' => $courseId])->first();
        return $studentCourse->routineClasses->pluck('slot_id')->toArray();
    }

    // needs discussion
    public function createMakeupRequest($makeupRequest, $studentId, $classId, $classType) {
        $message = 'Makeup request can not be created. Please try again';

        //to check credit history
        $now = Carbon::now('UTC')->format('Y-m-d H:i:s');
        $studentCourseId = $makeupRequest['student_course_id'];
        $creditHistory = CreditHistory::where('student_course_id', $studentCourseId)
        ->where('type', $classType)
        ->where('expired_at', '>', $now)
        ->orderBy('id')
        ->get();
        
        if(sizeof($creditHistory) == 0) {
            return 'No credit history found. Please demand credit from sales department first.';
        } else {
            $creditHistoryId = $creditHistory[0]->id;
            $result = MakeupRequest::create($makeupRequest);
            if($result) {
                $message = 'Makeup request created successfully';
    
                //to send emails and notifications.
                
            }
            //soft delete the credit history one row
            CreditHistory::find($creditHistoryId)->delete();

            return $message;
        }


    }

    /**
     * accept or reject a makeup request
     * 
     * @param int $makeupRequestId
     * @param int $status
     * @return string
     */
    public function acceptRejectMakeupRequest($makeupRequestId, $status) 
    {
        $message = 'Makeup request can not be updated. Please try again';
        $makeupRequest = MakeupRequest::where('id', $makeupRequestId)
            ->update(['status' => $status]);
        
        if($makeupRequest) {
            $message = 'Makeup request updated successfully';
            $userId = Auth::id();
            
            if($status == CommonEnum::MAKEUP_REQUEST_APPROVED) 
            {
                $makeupRequest->class->update(['status' => StatusEnum::MAKEUP]);
                $classTimeStdTz = GeneralHelper::convertTimeToUserTimezone($makeupRequest->class->class_time, $makeupRequest->studentCourse->student->timezone);
                $classTimeTchrTz = GeneralHelper::convertTimeToUserTimezone($makeupRequest->class->class_time, $makeupRequest->studentCourse->teacher->timezone);

                //to send emails.
                //dispatch(new MakeupRequestAcceptRejectEmails($makeupRequest));
                
                // send notification to student
                Notification::create([
                    'user_id' => $userId,
                    'student_id' => $makeupRequest->studentCourse->student->id,
                    'type' => CommonEnum::MAKEUP_REQUEST_APPROVED,
                    'message' => 'You have approved the makeup request for the class ' . $classTimeStdTz,
                ]);

                // send notification to teacher
                Notification::create([
                    'user_id' => $makeupRequest->studentCourse->teacher->id,
                    'student_id' => $makeupRequest->studentCourse->student->id,
                    'type' => CommonEnum::MAKEUP_REQUEST_APPROVED,
                    'message' => 'Your makeup request has been approved for the class ' . $classTimeTchrTz,
                ]);
            } else {
                //to send emails and notifications.

                // send notification to student
                Notification::create([
                    'user_id' => $userId,
                    'student_id' => $makeupRequest->studentCourse->student->id,
                    'type' => CommonEnum::MAKEUP_REQUEST_REJECTED,
                    'message' => 'You have rejected the makeup request for the class ' . $makeupRequest->class->class_time,
                ]);

                // send notification to teacher
                Notification::create([
                    'user_id' => $makeupRequest->studentCourse->teacher->id,
                    'student_id' => $makeupRequest->studentCourse->student->id,
                    'type' => CommonEnum::MAKEUP_REQUEST_REJECTED,
                    'message' => 'Your makeup request has been rejected for the class ' . $makeupRequest->class->class_time,
                ]);
            }
        }
        return $message;
    }

    // needs discussion
    public function makeupRequests($studentId) {
        //todo code here
        $now = Carbon::now('UTC')->format('Y-m-d H:i:s');
        $query = "select `s`.`name` as `student_name`, `s`.`timezone` as `student_timezone`,
            `c`.`title` as `course_title`, `sc`.`course_level`, 
            `u`.`name` as `teacher_name`, `u`.`timezone` as `teacher_timezone`, 
            `mr`.`availability_slot_id`, `mr`.`class_id`, 
            `mr`.`class_type`, `mr`.`class_old_date_time`, 
            `mr`.`makeup_date_time`, `mr`.`is_teacher`, 
            `mr`.`status` as `makeup_status` 
            from `students` as `s`, `courses` as `c`, 
            `student_courses` as `sc`, `users` as `u`, `makeup_requests` as `mr` 
            where `c`.`id` = `sc`.`course_id` and 
            `sc`.`student_id` = $studentId and 
            `s`.`id` = $studentId and 
            `sc`.`teacher_id` = `u`.`id` and 
            `mr`.`student_course_id` = `sc`.`id` 
            and `mr`.`makeup_date_time` > $now 
            order by `mr`.`makeup_date_time` asc;";
            $resultSet = DB::select($query);
            $data = array();
            foreach($resultSet as $result) {
                $studentTimezone = $result->student_timezone;
                $teacherTimezone = $result->teacher_timezone;
                $classOldTime = $result->class_old_date_time;
                $classOldTimeTeacherTZ = Carbon::parse($classOldTime, 'UTC')->setTimezone($teacherTimezone)->format('Y-m-d H:i:s');
                $classOldTimeStudentTZ = Carbon::parse($classOldTime, 'UTC')->setTimezone($studentTimezone)->format('Y-m-d H:i:s');
                $makeupDateTime = $result->makeup_date_time;
                $makeupDateTimeTeacherTZ = Carbon::parse($makeupDateTime, 'UTC')->setTimezone($teacherTimezone)->format('Y-m-d H:i:s');
                $makeupDateTimeStudentTZ = Carbon::parse($makeupDateTime, 'UTC')->setTimezone($studentTimezone)->format('Y-m-d H:i:s');

                $data[] = [
                    'student_name' => $result->student_name,
                    'student_timezone' => $studentTimezone,
                    'course_title' => $result->course_title,
                    'course_level' => $result->course_level,
                    'teacher_name' => $result->teacher_name,
                    'teacher_timezone' => $teacherTimezone,
                    'availability_slot_id' => $result->availability_slot_id,
                    'class_id' => $result->class_id,
                    'class_type' => $result->class_type,
                    'class_old_date_time_utc' => $classOldTime,
                    'class_old_date_time_teacher_tz' => $classOldTimeTeacherTZ,
                    'class_old_date_time_student_tz' => $classOldTimeStudentTZ,
                    'makeup_date_time_utc' => $makeupDateTime,
                    'makeup_date_time_teacher_tz' => $makeupDateTimeTeacherTZ,
                    'makeup_date_time_student_tz' => $makeupDateTimeStudentTZ,
                    'is_teacher' => $result->is_teacher,
                    'makeup_status' => $result->makeup_status
                ];
            }
            return $data;

    }
}