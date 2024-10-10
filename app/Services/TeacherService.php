<?php
namespace App\Services;

use App\Classes\Enums\CommonEnum;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\RescheduleRequest;
use App\Classes\Enums\StatusEnum;
use App\Helpers\GeneralHelper;
use App\Jobs\SendMailToCustomerOnMakeupRequest;
use App\Jobs\SendMailToTeacherOnMakeupRequest;

use App\Models\Notification;
use App\Models\RegularClassRate;
use App\Models\StudentCourse;
use App\Models\TrialClass;
use App\Models\TrialClassRate;
use App\Models\WeeklyClass;
use App\Repository\ChatRepository;
use App\Repository\MakeupRequestRepository;
use App\Repository\TeacherRepository;
use App\Repository\TrialClassRepository;
use App\Repository\WeeklyClassRepository;
use App\Traits\DecryptionTrait;
use Illuminate\Support\Facades\Auth;

class TeacherService
{
    private $teacherRepository;
    private $chatRepository;
    private $trialClassRepository;
    private $weeklyClassRepository;
    private $makeupRequestRepository;
    use DecryptionTrait;
    
    public function __construct(
        TeacherRepository $teacherRepository,
        ChatRepository $chatRepository,
        TrialClassRepository $trialClassRepository,
        WeeklyClassRepository $weeklyClassRepository,
        MakeupRequestRepository $makeupRequestRepository
    )
    {
        $this->teacherRepository = $teacherRepository;
        $this->chatRepository = $chatRepository;
        $this->trialClassRepository = $trialClassRepository;
        $this->weeklyClassRepository = $weeklyClassRepository;
        $this->makeupRequestRepository = $makeupRequestRepository;
    }

    public function getActiveStudents($teacherId, $offset, $limit)
    {
        $query = "call spTeacherActiveStudentsCount($teacherId);";

        $resultSet = DB::select($query);
        $studentsCount = $resultSet[0]->count;

        $query = "call spTeacherActiveStudents($teacherId, $offset, $limit);";
        $students = DB::select($query);
        $data = array();
        foreach ($students as $student) {
            $data[] = array(
                'student_id' => $student->student_id,
                'student_name' => $this->decryptValue($student->student_name),
                'course_id' => $student->course_id,
                'course_title' => $student->course_title,
                'course_level' => $student->course_level,
                'status' => $student->status,
                'subscription_id' => $student->subscription_id,
                'teacher_id' => $student->teacher_id
            );
        }
        $finalData = array(
            'count' => $studentsCount,
            'students' => $data
        );
        return $finalData;
    }

    public function getStudentActivities($studentId, $offset, $limit) {
        $query = "call spStudentActivitiesCount($studentId);";

        $resultSet = DB::select($query);
        $activitiesCount = $resultSet[0]->count;

        $query = "call spStudentActivities($studentId, $offset, $limit);";
        $students = DB::select($query);
        $data = array();

        $studentTimezone = '';
        $teacherTimezone = '';

        $size = sizeof($students);
        if($size > 0) {
            $studentTimezone = $students[0]->student_timezone;
            $teacherTimezone = $students[0]->teacher_timezone;
        }
        foreach ($students as $student) {
            $createdAt = $student->created_at;
            $data[] = array(
                'student_id' => $student->student_id,
                'student_name' => $this->decryptValue($student->student_name),
                'course_id' => $student->course_id,
                'course_title' => $student->course_title,
                'course_level' => $student->course_level,
                'teacher_id' => $student->teacher_id,
                'activity_type' => $student->activity_type,
                'description' => $student->description,
                'file_name' => $student->file_name,
                'file_size' => $student->file_size,
                'created_at_utc' => Carbon::parse($createdAt)->setTimezone('UTC')->format('M d, Y H:i:s'),
                'created_at_student_timezone' => Carbon::parse($createdAt)->setTimezone($studentTimezone)->format('M d, Y H:i:s'),
                'created_at_teacher_timezone' => Carbon::parse($createdAt)->setTimezone($teacherTimezone)->format('M d, Y H:i:s')
            );
        }
        
        $finalData = array(
            'count' => $activitiesCount,
            'students' => $data
        );
        return $finalData;
    }
    
    public function withdrawMakeupRequest($teacherId, $weeklyClassId) {
    
        $rescheduleRequests = RescheduleRequest::where('teacher_id', $teacherId)
            ->where('weekly_class_id', $weeklyClassId)
            ->where('teacher_id', $teacherId)
            ->where('status', '=', StatusEnum::RESCHEDULED)
            ->where('deleted_at', '=', null)
            ->get();
        $message = 'Makeup request class not found';
        if(sizeof($rescheduleRequests) > 0) {
            $id = $rescheduleRequests[0]->id;
            $rescheduleRequest = RescheduleRequest::find($id)->update(['status' => StatusEnum::CANCELLED]);
            if($rescheduleRequest) {
                $message = 'Makeup request canceled successfully';
            } else {
                $message = 'An error occured while cancelling the Makeup request';
            }
        }
        return $message;
    }

    /**
     * Get the student's for a teacher
     * with unread messages count
     * 
     * @param $teacherId
     * @return mixed
     */
    public function getUsersWithUnreadMessagesCount($teacherId)
    {
        $students = $this->teacherRepository->getStudents($teacherId);
        
        $studentsArray = [];
        $uniqueUsers = collect();
        
        foreach ($students as $student) {
            $unreadMessagesCount = $this->chatRepository->getUnreadMessagesCount($student->id, $teacherId);
        
            $studentData = [
                'id' => $student->id,
                'name' => $this->decryptValue($student->name),
                'course' => $student->course,
                'profilePic' => $student->profilePic,
                'unreadMessagesCount' => $unreadMessagesCount,
                'active' => false,
            ];
        
            $studentsArray[] = $studentData;
        
            if ($student->id && !$uniqueUsers->contains('id', $student->id)) {
                $uniqueUsers->push($studentData);
            }
        }
        
        $otherUsers = $this->chatRepository->getChatUsersForTeacher($teacherId);
        $uniqueUsersArray = $uniqueUsers->unique('id')->values()->all();
        
        return array_merge($otherUsers, $uniqueUsersArray);
    }

    /**
     * Get the teacher's for a customer
     * with unread messages count
     * 
     * @param $teacherId
     * @return mixed
     */
    public function getTeachersWithUnreadMessagesCount($customerId)
    {
        $teachers = $this->teacherRepository->getTeachersForCustomerForChat($customerId);
        
        $teachersArray = [];
        
        foreach ($teachers as $teacher) {
            $unreadMessagesCount = $this->chatRepository
                ->getUnreadMessagesCount($teacher->id, $customerId);
        
            $teachersArray[] = [
                'id' => $teacher->id,
                'name' => $this->decryptValue($teacher->name),
                'course' => $teacher->course,
                'profilePic' => $teacher->profilePic,
                'unreadMessagesCount' => $unreadMessagesCount,
                'active' => false,
            ];
        }
        
        return $teachersArray;
    }

    public function createMakeupRequest($teacherId, $classId, $availabilitySlotId, $makeupDateTime, $classType) {
        
        $status = 'pending';
        $isTeacher = 1;
        $createdAt = Carbon::now('UTC')->format('Y-m-d H:i:s');
        $studentCourseId = 0;
        $oldClassTime = '';
        $customerId = 0;
        $customerEmail = '';
        $studentName = '';
        $studentId = 0;
        $courseTitle = '';
        $customerName = '';
        $teacherName = Auth::user()->name;
        $teacherEmail = Auth::user()->email;
        $makeupDateTimeUTC = GeneralHelper::convertTimeToUTCzone($makeupDateTime);
        $studentTimezone = '';

        if($classType == "trial") {
            $trialClass = $this->trialClassRepository->getById($classId);
            $studentCourseId = $trialClass->student_course_id;
            $oldClassTime = $trialClass->class_time;
            $studentName = $trialClass->studentCourse->student->name;
            $studentTimezone = $trialClass->studentCourse->student->timezone;
            $courseTitle = $trialClass->studentCourse->course->title;
            $customerEmail = $trialClass->studentCourse->student->user->email;
            $customerName = $trialClass->studentCourse->student->name;
        } else {
            $weeklyClass = $this->weeklyClassRepository->getById($classId);
            $studentCourseId = $weeklyClass->routineClass->student_course_id;
            $oldClassTime = $weeklyClass->class_time;
            $studentName = $weeklyClass->routineClass->studentCourse->student->name;
            $studentTimezone = $weeklyClass->routineClass->studentCourse->student->timezone;
            $courseTitle = $weeklyClass->routineClass->studentCourse->course->title;
            $customerEmail = $weeklyClass->routineClass->studentCourse->student->user->email;
            $customerName = $weeklyClass->routineClass->studentCourse->student->name;
        }
        
        $makeupDateTimeStudentTZ = GeneralHelper::convertTimeToUserTimezone($makeupDateTime, $studentTimezone);
        $makeupDateTimeTeacherTZ = GeneralHelper::convertTimeToUserTimezone($makeupDateTime, Auth::user()->timezone);
        $oldClassTimeStudentTZ = GeneralHelper::convertTimeToUserTimezone($oldClassTime, $studentTimezone);
        $oldClassTimeTeacherTZ = GeneralHelper::convertTimeToUserTimezone($oldClassTime, Auth::user()->timezone);

        $makeupRequest = array(
            'student_course_id' => $studentCourseId,
            'class_type' => $classType === 'trial' ? 'App\Model\TrialClass' : 'App\Model\WeeklyClass',
            'class_id' => $classId,
            'availability_slot_id' => $availabilitySlotId,
            'makeup_date_time' => $makeupDateTimeUTC,
            'class_old_date_time' => $oldClassTime,
            'status' => $status,
            'created_at' => $createdAt,
            'is_teacher' => $isTeacher
        );
        
        $this->makeupRequestRepository->create($makeupRequest);

        //student notification
        Notification::create([
            'user_id' => $customerId,
            'student_id' => $studentId,
            'type' => CommonEnum::MAKEUP_REQUEST_BY_TEACHER,
            'message' => "A makeup request has been generated for {$studentName} on {$makeupDateTimeStudentTZ} with {$teacherName}."
        ]);

            //teacher notification
            Notification::create([
                'user_id' => $teacherId,
                'student_id' => $studentId,
                'type' => CommonEnum::MAKEUP_REQUEST_BY_TEACHER,
                'message' => "Your makeup request has been generated for {$studentName} on {$makeupDateTimeTeacherTZ}."
            ]);

            //send email to customer and notify about the makeup request
            $details = array();
            $details['customerEmail'] = $customerEmail;
            $details['customerName'] = $customerName;
            $details['student'] = $studentName;
            $details['course'] = $courseTitle;
            $details['teacherName'] = $teacherName;
            $details['classType'] = $classType;
            $details['makeupDateTimeStudentTZ'] = $makeupDateTimeStudentTZ;
            $details['oldDateTimeStudentTZ'] = $oldClassTimeStudentTZ;

            dispatch(new SendMailToCustomerOnMakeupRequest($details));
        
            //send email to teacher
            $details = array();
            $details['teacherEmail'] = $teacherEmail;
            $details['customerName'] = $customerName;
            $details['student'] = $studentName;
            $details['course'] = $courseTitle;
            $details['classType'] = $classType;
            $details['teacherName'] = $teacherName;
            $details['makeupDateTimeTeacherTZ'] = $makeupDateTimeTeacherTZ;
            $details['oldDateTimeTeacherTZ'] = $oldClassTimeTeacherTZ;

            dispatch(new SendMailToTeacherOnMakeupRequest($details));

        return $makeupRequest;
    }

    /**
     * Get the makeup requests for a teacher
     * 
     * @param $teacherId
     * @return mixed
     */
    public function makeupRequests($teacherId) {
        return $this->makeupRequestRepository->getAllRequestsByTeacherId($teacherId);
    }

    public function getProfile($teacherId) {
        $teacher = $this->teacherRepository->getTeacherById($teacherId);
        $data = array(
            'id' => $teacher->id,
            'name' => $teacher->name,
            'email' => $teacher->email,
            'phone' => $teacher->phone,
            'timezone' => $teacher->timezone,
            'country' => $teacher->country,
            'city' => $teacher->city,
            'address' => $teacher->address,
            'postalCode' => $teacher->postal_code,
            'regularClassRate' => 0,
            'trialClassRate' => 0,
            'teacherCoordinator' => 'John Doe'

        );

        $trialClassRate = TrialClassRate::orderBy('id', 'desc')->limit(1)->get();
        if(sizeof($trialClassRate) > 0) {
            $data['trialClassRate'] = $trialClassRate[0]->rate;
        }

        $regularClassRate = RegularClassRate::where('teacher_id', $teacherId)->get();
        if(sizeof($regularClassRate) > 0) {
            $data['regularClassRate'] = $regularClassRate[0]->rate;
        }

        return $data;
    }

    public function updatePassword($teacherId, $newPassword) {
        return $this->teacherRepository->updatePassword($teacherId, ['password' => $newPassword]);
    }
}