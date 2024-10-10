<?php
namespace App\Services;

use App\Classes\Enums\CommonEnum;
use Carbon\Carbon;
use App\Classes\Enums\StatusEnum;
use App\Helpers\GeneralHelper;
use App\Http\Resources\MakeupRequestResource;
use App\Jobs\SendMailToCustomerOnMakeupRequest;
use App\Jobs\SendMailToTeacherOnMakeupRequest;

use App\Models\Notification;
use App\Repository\CreditHistoryRepository;
use App\Repository\MakeupRequestRepository;
use App\Repository\StudentCoursesRepository;
use App\Repository\TrialClassRepository;
use App\Repository\WeeklyClassRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MakeupRequestService
{
    private $trialClassRepository;
    private $weeklyClassRepository;
    private $makeupRequestRepository;
    private $creditHistoryRepository;
    private $studentCourseRepository;

    public function __construct(
        TrialClassRepository $trialClassRepository,
        WeeklyClassRepository $weeklyClassRepository,
        MakeupRequestRepository $makeupRequestRepository,
        CreditHistoryRepository $creditHistoryRepository,
        StudentCoursesRepository $studentCourseRepository
    )
    {
        $this->trialClassRepository = $trialClassRepository;
        $this->weeklyClassRepository = $weeklyClassRepository;
        $this->makeupRequestRepository = $makeupRequestRepository;
        $this->creditHistoryRepository = $creditHistoryRepository;
        $this->studentCourseRepository = $studentCourseRepository;
    }

    /**
     * Create makeup request
     * 
     * @param $teacherId
     * @param $classId
     * @param $availabilitySlotId
     * @param $makeupDateTime
     * @param $classType
     * @return array
     */
    public function createMakeupRequest
    (
        $teacherId, $classId, $availabilitySlotId, 
        $makeupDateTime, $classType
    ) 
    {
        return DB::transaction(function () use 
            ($teacherId, $classId, $availabilitySlotId, $makeupDateTime, $classType) {
            $status = CommonEnum::MAKEUP_REQUEST_PENDING;
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
                $studentId = $trialClass->studentCourse->student->id;
                $studentName = $trialClass->studentCourse->student->name;
                $studentTimezone = $trialClass->studentCourse->student->timezone;
                $courseTitle = $trialClass->studentCourse->course->title;
                $customerId = $trialClass->studentCourse->student->user->id;
                $customerEmail = $trialClass->studentCourse->student->user->email;
                $customerName = $trialClass->studentCourse->student->user->name;
            } else {
                $weeklyClass = $this->weeklyClassRepository->getById($classId);
                $studentCourseId = $weeklyClass->routineClass->student_course_id;
                $oldClassTime = $weeklyClass->class_time;
                $studentId = $weeklyClass->routineClass->studentCourse->student->id;
                $studentName = $weeklyClass->routineClass->studentCourse->student->name;
                $studentTimezone = $weeklyClass->routineClass->studentCourse->student->timezone;
                $courseTitle = $weeklyClass->routineClass->studentCourse->course->title;
                $customerId = $weeklyClass->routineClass->studentCourse->student->user->id;
                $customerEmail = $weeklyClass->routineClass->studentCourse->student->user->email;
                $customerName = $weeklyClass->routineClass->studentCourse->student->user->name;
            }
            
            $makeupDateTimeStudentTZ = GeneralHelper::convertTimeToUserTimezone($makeupDateTimeUTC, $studentTimezone);
            $makeupDateTimeStudentTZ = Carbon::parse($makeupDateTimeStudentTZ)->format('Y-m-d h:i A');
            $makeupDateTimeTeacherTZ = GeneralHelper::convertTimeToUserTimezone($makeupDateTimeUTC, Auth::user()->timezone);
            $makeupDateTimeTeacherTZ = Carbon::parse($makeupDateTimeTeacherTZ)->format('Y-m-d h:i A');
            $oldClassTimeStudentTZ = GeneralHelper::convertTimeToUserTimezone($oldClassTime, $studentTimezone);
            $oldClassTimeStudentTZ = Carbon::parse($oldClassTimeStudentTZ)->format('Y-m-d h:i A');
            $oldClassTimeTeacherTZ = GeneralHelper::convertTimeToUserTimezone($oldClassTime, Auth::user()->timezone);
            $oldClassTimeTeacherTZ = Carbon::parse($oldClassTimeTeacherTZ)->format('Y-m-d h:i A');

            $makeupRequest = [
                'student_course_id' => $studentCourseId,
                'class_type' => $classType === 'trial' ? 
                    'App\Model\TrialClass' : 'App\Model\WeeklyClass',
                'class_id' => $classId,
                'availability_slot_id' => $availabilitySlotId,
                'makeup_date_time' => $makeupDateTimeUTC,
                'class_old_date_time' => $oldClassTime,
                'status' => $status,
                'created_at' => $createdAt,
                'is_teacher' => $isTeacher
            ];
            
            // save makeup request
            $this->makeupRequestRepository->create($makeupRequest);

            $creditHitoryData = [
                'student_course_id' => $studentCourseId,
                'class_type' => $classType === 'trial' ? 
                    'App\Model\TrialClass' : 'App\Model\WeeklyClass',
                'class_id' => $classId,
                'expired_at' => Carbon::now()->endOfMonth(),
            ];

            // save credit history
            $this->creditHistoryRepository->create($creditHitoryData);

            // notify student about the makeup request
            $this->sendNotification(
                $customerId, $studentId, CommonEnum::MAKEUP_REQUEST_BY_TEACHER, 
                "A makeup request has been generated for {$studentName} on {$makeupDateTimeStudentTZ} with {$teacherName}."
            );

            // notify teacher about the makeup request
            $this->sendNotification(
                $teacherId, $studentId, CommonEnum::MAKEUP_REQUEST_BY_TEACHER,
                "Your makeup request has been generated for {$studentName} on {$makeupDateTimeTeacherTZ}."
            );

            //send email to customer
            $details = [
                'customerEmail' => $customerEmail,
                'customerName' => $customerName,
                'student' => $studentName,
                'course' => $courseTitle,
                'teacherName' => $teacherName,
                'classType' => $classType,
                'makeupDateTimeStudentTZ' => $makeupDateTimeStudentTZ,
                'oldClassDateTimeStudentTZ' => $oldClassTimeStudentTZ,
            ];

            dispatch(new SendMailToCustomerOnMakeupRequest($details));
            
            //send email to teacher
            $details['teacherEmail'] = $teacherEmail;
            $details['teacherName'] = $teacherName;
            $details['makeupDateTimeTeacherTZ'] = $makeupDateTimeTeacherTZ;
            $details['oldDateTimeTeacherTZ'] = $oldClassTimeTeacherTZ;

            dispatch(new SendMailToTeacherOnMakeupRequest($details));

            return $makeupRequest;
        });
    }

    /**
     * Send notification
     * 
     * @param $userId
     * @param $studentId
     * @param $type
     * @param $message
     * @return void
     */
    public function sendNotification(
        $userId, $studentId, $type, $message
    ) 
    {
        Notification::create([
            'user_id' => $userId,
            'student_id' => $studentId,
            'type' => $type,
            'message' => $message
        ]);
    }

    /**
     * Get the makeup requests for a teacher
     * 
     * @param $teacherId
     * @return mixed
     */
    public function teacherMakeupRequests($teacherId, $teacherTimezone, $offset, $limit) 
    {
        $studentCourseIds = $this->studentCourseRepository
            ->getByTeacherId($teacherId);

        $totalRequests = $this->makeupRequestRepository
            ->getAllRequestsCountByStudentCourseId($studentCourseIds);

        $teacherMakeupRequests = $this->makeupRequestRepository
            ->getAllRequestsByStudentCourseId($studentCourseIds, $offset, $limit);

        return [
            'total' => $totalRequests,
            'requests' => MakeupRequestResource::collection($teacherMakeupRequests->map(function ($request) use ($teacherTimezone) {
                return new MakeupRequestResource($request, $teacherTimezone);
            }))
        ];
    }

    /**
     * Withdraw makeup request
     * 
     * @param $teacherId
     * @param $weeklyClassId
     * @return string
     */
    public function withdrawMakeupRequest($id) 
    {
        $makeupRequest = $this->makeupRequestRepository
            ->findById($id);

        if($makeupRequest) {
            $this->makeupRequestRepository->update($makeupRequest->id, [
                'status' => StatusEnum::CANCELLED
            ]);

            return 'Makeup request has been withdrawn';
        } else {
            return 'Makeup request not found';
        }
    }

    public function getAllTeachersMakeupRequests($teacherIds, $offset, $limit)
    {
        $totalRequests = $this->makeupRequestRepository->getTotalMakeupRequests($teacherIds);

        $teacherMakeupRequests = $this->makeupRequestRepository
            ->getAllTeachersMakeupRequests($teacherIds, $offset, $limit);
        $data = array();
        foreach($teacherMakeupRequests as $makeupRequest) {
            $classType = $makeupRequest->class_type;
            $classOldDateTime = $makeupRequest->class_old_date_time;
            $newClassDateTime = $makeupRequest->makeup_date_time;
            $previousTimeStudentTimezone = GeneralHelper::convertTimeToUserTimezone($classOldDateTime, $makeupRequest->studentCourse->student->timezone);
            $newTimeStudentTimezone = GeneralHelper::convertTimeToUserTimezone($newClassDateTime, $makeupRequest->studentCourse->student->timezone);
            $previousTimeTeacherTimezone = GeneralHelper::convertTimeToUserTimezone($classOldDateTime, $makeupRequest->studentCourse->teacher->timezone);
            $newTimeTeacherTimezone = GeneralHelper::convertTimeToUserTimezone($newClassDateTime, $makeupRequest->studentCourse->teacher->timezone);

            $previousTimeStudentTimezone = $this->foramatMakeupDateTime($previousTimeStudentTimezone);
            $newTimeStudentTimezone = $this->foramatMakeupDateTime($newTimeStudentTimezone);

            $previousTimeTeacherTimezone = $this->foramatMakeupDateTime($previousTimeTeacherTimezone);
            $newTimeTeacherTimezone = $this->foramatMakeupDateTime($newTimeTeacherTimezone);

            $classTypeLabel = 'Trial Class';
            if($classType == 'App\Models\WeeklyClass') {
                $classTypeLabel = 'Weekly Class';
            }
            $data[] = array(
                'class_id' => $makeupRequest->class_id,
                'class_type' => $classTypeLabel,
                'status' => $makeupRequest->status,
                'student_name' => $makeupRequest->studentCourse->student->name,
                'customer_name' => $makeupRequest->studentCourse->student->user->name,
                'teacher_name' => $makeupRequest->studentCourse->teacher->name,
                'course_title' => $makeupRequest->studentCourse->course->title,
                'previous_time_student_timezone' => $previousTimeStudentTimezone,
                'new_time_student_timezone' => $newTimeStudentTimezone,
                'previous_time_teacher_timezone' => $previousTimeTeacherTimezone,
                'new_time_teacher_timezone' => $newTimeTeacherTimezone,
            );
        }

        return [
            'total' => $totalRequests,
            'makeup_requests' => $data
        ];
    }

    private function foramatMakeupDateTime($datetime) {
        $carbonStart = Carbon::createFromFormat('Y-m-d H:i:s', $datetime);
        // Add 30 minutes to the start time
        $carbonEnd = $carbonStart->copy()->addMinutes(30);
        $formattedRange = $carbonStart->format('F j, Y,, g:i') . ' - ' . $carbonEnd->format('g:i A');
        return $formattedRange;
    }
}