<?php
namespace App\Services;

use App\Classes\Enums\StatusEnum;
use App\Events\SubscriptionCreated;
use App\Events\TrialClassCreated;
use App\Helpers\GeneralHelper;
use App\Http\Resources\CourseResource;
use App\Http\Resources\StudentCourseAcitivityResource;
use App\Http\Resources\StudentResource;
use App\Jobs\CreateOneTimeWeeklyClasses;
use App\Jobs\SendCustomerMailOnNewStudentAdded;
use App\Jobs\SendEmailOnTeacherChanged;
use App\Jobs\SendSchedulingTeamMailOnNewStudentAdded;
use App\Jobs\SendTrialClassCreatedEmail;
use App\Models\Notification;
use App\Models\RoutineClass;
use App\Models\Student;
use App\Models\StudentCourse;
use App\Models\User;
use App\Repository\Interfaces\AvailabilityRepositoryInterface;
use App\Repository\Interfaces\AvailabilitySlotRepositoryInterface;
use App\Repository\Interfaces\RoutineClassRepositoryInterface;
use App\Repository\Interfaces\StripeRepositoryInterface;
use App\Repository\Interfaces\SubscriptionRepositoryInterface;
use App\Repository\NotificationRepository;
use App\Repository\StudentChangeTeacherHistoryRepository;
use App\Repository\StudentCourseActivityRepository;
use App\Repository\StudentCoursesRepository;
use App\Repository\StudentRepository;
use App\Repository\WeeklyClassRepository;
use Carbon\Carbon;
use App\Services\StripeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentService
{
    protected $availabilitySlotRepository;
    protected $availabilityRepository;
    protected $studentRepository;
    private $routineClassRepository;
    private $stripeService;
    private $trialService;
    private $notificationRepository;
    private $studentCourseRepository;
    private $weeklyClassRepository;
    private $stripeRepository;
    private $subscriptionRepository;
    private $studentChangeTeacherHistoryRepository;
    private $studentCourseActivityRepository;

    public function __construct(
        AvailabilitySlotRepositoryInterface $availabilitySlotRepository, 
        AvailabilityRepositoryInterface $availabilityRepository,
        StudentRepository $studentRepository,
        RoutineClassRepositoryInterface $routineClassRepository,
        StripeService $stripeService,
        TrialService $trialService,
        NotificationRepository $notificationRepository,
        StudentCoursesRepository $studentCourseRepository,
        WeeklyClassRepository $weeklyClassRepository,
        StripeRepositoryInterface $stripeRepository,
        SubscriptionRepositoryInterface $subscriptionRepository,
        StudentChangeTeacherHistoryRepository $studentChangeTeacherHistoryRepository,
        StudentCourseActivityRepository $studentCourseActivityRepository
    )
    {
        $this->availabilitySlotRepository = $availabilitySlotRepository;
        $this->availabilityRepository = $availabilityRepository;
        $this->studentRepository = $studentRepository;
        $this->routineClassRepository = $routineClassRepository;
        $this->stripeService = $stripeService;
        $this->trialService = $trialService;
        $this->notificationRepository = $notificationRepository;
        $this->studentCourseRepository = $studentCourseRepository;
        $this->weeklyClassRepository = $weeklyClassRepository;
        $this->stripeRepository = $stripeRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->studentChangeTeacherHistoryRepository = $studentChangeTeacherHistoryRepository;
        $this->studentCourseActivityRepository = $studentCourseActivityRepository;
    }

    /**
     * Create a new student
     * 
     * @param $request
     * @return mixed
     */
    public function createStudent($request)
    {
        $userId = Auth::id();

        if (!$request->has('student_id')) 
        {
            $studentData = [
                'name' => $request->name,
                'age' => $request->age,
                'gender' => $request->gender,
                'timezone' => $request->timezone,
                'user_id' => $userId,
            ];
    
            // upload profile image if provided
            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $path = 'images/student/profile';
                $studentData['profile_photo_url'] = GeneralHelper::uploadProfileImage($file, $path);
            }
    
            $student = $this->studentRepository->create($studentData);
    
            // check if student already exists
            if (isset($student['error'])) {
                return $student;
            }
    
            // send email & notification on student creation
            $this->sendEmailNotificationOnStudentCreation($student);
        }
        else {
            $student = Student::find($request->student_id);
        }

        if (!$request->has('change_teacher'))
        {
            $courseData = [
                'student_id' => $student->id,
                'course_id' => $request->course_id,
                'course_level' => $request->course_level,
                'teacher_id' => $request->teacher_id,
                'teacher_preference' => $request->teacher_preference,
                'shift_id' => $request->shift_id,
            ];
    
            // create a new student course
            $studentCourseRepository = new StudentCoursesRepository(new StudentCourse());
            $studentCourse = $studentCourseRepository->addStudentCourse($courseData);
        }
        else {
            $studentCourse = $this->studentCourseRepository->getStudentCourseByCourseId($student->id, $request->course_id);
        }

        // if student wants a trial class
        if ($request->is_trial_required) 
        {
            $trialData = [
                'customer_id' => $userId,
                'teacher_id' => $request->teacher_id,
                'student_id' => $student->id,
                'availability_slot_id' => intval($request->availability_slot_ids[0]),
                'student_course_id' => $studentCourse->id,
            ];

            $trialClass = $this->trialService->createTrialClass($trialData);

            if (isset($trialClass['error'])) {
                return $trialClass;
            }

            $this->sendEmailNotificationOnTrialClassCreation($trialClass);

            return $student;
        }

        // create subscription
        $planId = $request->stripe_plan_id;
        $studentId = $student->id;
        $couponCode = $request->coupon_code;
        $studentCourseId = $studentCourse->id;

        // create customer and subscription
        // create one-time weekly classes
        // send email: customer,teacher,scheduling,support,coordinator
        $this->stripeService->createCustomerAndSubscription(
            $userId, $planId, $studentId, $couponCode, $studentCourseId,
            $request->availability_slot_ids, $request->teacher_id, $request->stripe_plan
        );

        return $student;
    }

    /**
     * Send emails & notification on student creation
     * 
     * @param $student
     */
    public function sendEmailNotificationOnStudentCreation($student)
    {
        // Dispatch the job to send the emails: customer, scheduling team
        dispatch(new SendCustomerMailOnNewStudentAdded($student->user, $student->name));
        dispatch(new SendSchedulingTeamMailOnNewStudentAdded($student->user, $student->name));

        // Create a new notification
        $notification = [
            'user_id' => $student->user->id,
            'student_id' => $student->id,
            'type' => 'student',
            'read' => false,
            'message' => "A new student {$student->name} has been added to your account."
        ];

        $this->notificationRepository->create($notification);
    }

    /**
     * Send email notification on trial class creation
     * 
     * @param $trialClass
     */
    public function sendEmailNotificationOnTrialClassCreation($trialClass)
    {
        // Dispatch the job to send the emails
        dispatch(new SendTrialClassCreatedEmail($trialClass));

        // convert the class time to the student's timezone
        $trialClass->class_time = GeneralHelper::convertTimeToUserTimezone($trialClass->class_time, $trialClass->student->timezone);

        // Create a new notification
        $notification = [
            'user_id' => $trialClass->student->user->id,
            'student_id' => $trialClass->student->id,
            'type' => 'trial_class',
            'read' => false,
            'message' => "A trial class has been scheduled for {$trialClass->student->name} on {$trialClass->class_time} with {$trialClass->teacher->name}."
        ];

        $this->notificationRepository->create($notification);
    }

    /**
     * Get all students of a customer with their courses, teachers, attendance
     * @param $userId
     * @return mixed
     */
    public function getAllCustomerStudents($userId)
    {
        $user = User::find($userId);
        $students = $user->customerStudents()->get();

        $students->each(function ($student) {
            
            // Get subscription plans
            $student->subscription_plans = $student->subscriptions()
                ->where('payment_status', 'succeeded')
                ->count();

            // Get courses with subscription status
            $student->courses = $student->courses()->get()
                ->map(function ($course) use ($student)
                {
                    $student->teacher_name = $course->teacher->name;
                    $hasHistory = $course->studentChangeTeacherHistory()->count() > 0;

                    // Retrieve the latest trial class
                    $latestTrialClass = $course->trialClass()->latest()->first();
                    $trialStatus = $latestTrialClass ? $latestTrialClass->status : null;

                    // Determine the status
                    $status = StatusEnum::NotSubscribed;
                    if ($course->subscription_id) {
                        if ($course->subscription->payment_status === 'succeeded') {
                            $status = StatusEnum::SubscriptionActive;
                        } else {
                            $status = StatusEnum::PaymentFailed;
                        }
                    } else {
                        if ($trialStatus === StatusEnum::TrialScheduled) {
                            $status = StatusEnum::TrialScheduled;
                        } else if ($trialStatus === StatusEnum::TrialSuccessful) {
                            $status = StatusEnum::TrialSuccessful;
                        } else if ($trialStatus === StatusEnum::TrialUnSuccessful) {
                            $status = StatusEnum::TrialUnSuccessful;
                        }
                    }

                    return [
                        'id' => $course->course_id,
                        'student_course_id' => $course->id,
                        'name' => $course->course->title,
                        'level' => $course->course_level,
                        'subscription_id' => $course->subscription_id ? 
                        $course->subscription->sub_id : null,
                        'stripe_plan_id' => $course->subscription_id ? 
                        $course->subscription->planID : null,
                        'stripe_plan' => $course->subscription_id ? 
                        $course->subscription->subscriptionPlan->description : '',
                        'status' => $status,
                        'is_teacher_changed' => $hasHistory ? true : false,
                        'teacher_preference' => $course->teacher_preference,
                        'shift_id' => $course->shift_id,
                    ];
                });

            // Get teachers
            $student->teachers = $student->courses()->with('teacher')->get()
                ->map(function ($course) {
                    return [
                        'id' => $course->teacher->id,
                        'name' => $course->teacher->name,
                        'gender' => $course->teacher->gender,
                        'email' => $course->teacher->email,
                        'course_id' => $course->course_id,
                        'course' => $course->course->title,
                        'profile_photo_url' => $course->teacher->profile_photo_path,
                    ];
                });

            // Calculate attendance percentage
            $totalClasses = $student->weeklyClasses()->count();
            $attendedClasses = $student->weeklyClasses()->where('student_presence', 1)->count();
            $student->attendance = $totalClasses > 0 ? ($attendedClasses / $totalClasses) * 100 : 0;
        });

        return $students; 
    }


    /**
     * Cancel student subscription
     * remove upcoming weekly classes
     * charge $5 fee for the teacher change process
     * remove subscription from db of that course
     * update student course with new teacher and shift
     * email to customer, previous teacher and customer support
     * 
     * @param $request
     */
    public function changeTeacher($request)
    {
        DB::transaction(function () use ($request) {

            $studentCourse = $this->studentCourseRepository->getStudentCourseByCourseId($request->student_id, $request->course_id);
            $routineClassesIds = $studentCourse->routineClasses->pluck('id')->toArray();
            $this->weeklyClassRepository->removeStudentUpcomingClasses($routineClassesIds);
            
            // cancel student subscription and charge $5 fee for the teacher change process.
            $this->stripeRepository->cancelSubscriptionImmediately($studentCourse->subscription->sub_id);
            
            $this->subscriptionRepository->removeSubscription($studentCourse->subscription->sub_id);
                        
            //to store student changed history
            $this->studentChangeTeacherHistoryRepository->store([
                'student_course_id' => $studentCourse->id,
                'change_teacher_reason_id' => $request->change_teacher_reason_id
            ]);

            // update student course with new teacher and shift
            $studentCourse->update(['shift_id' => $request->shift_id, 'teacher_id' => $request->teacher_id]);

            // email to customer, Notify the previous teacher and customer support about the change.
            $data = [
                'customer_email' => $studentCourse->student->user->email,
                'teacher_email' => $studentCourse->teacher->email,
                'student_name' => $studentCourse->student->name,
                'customer_name' => $studentCourse->student->user->name,
                'teacher_name' => $studentCourse->teacher->name
            ];
            dispatch(new SendEmailOnTeacherChanged($data));
            
            // to generate a notification on student timeline
            $notification = [
                'user_id' => $studentCourse->student->user->id,
                'student_id' => $studentCourse->student->id,
                'type' => 'teacher_change',
                'read' => false,
                'message' => "Teacher has been changed against your student {$studentCourse->student->name}."
            ];
            $this->notificationRepository->create($notification);
            
        });
    }

    /**
     * Get courses for a student in which he has active
     * subscription.
     * @param int $studentId
     * @return array
     */
    public function getStudentCourses($studentId)
    {
        $courses = $this->studentCourseRepository->getStudentCourses($studentId);

        return CourseResource::collection($courses);
    }

    /**
     * Get all students for a teacher
     * @param int $teacherId
     * @return array
     */
    public function getStudentsForTeacher($teacherId)
    {
        $students = $this->studentCourseRepository->getStudentsForATeacher($teacherId);

        return StudentResource::collection($students);
    }

    /**
     * Create a makeup request for a student
     * @param int $studentId
     * @param int $classId
     * @param int $availabilitySlotId
     * @param string $makeupDateTime
     * @param string $classType
     * @return mixed
     */
    public function createMakeupRequest($studentId, $classId, $availabilitySlotId, $makeupDateTime, $classType) {
        $status = 'pending';
        $isTeacher = 0;
        $createdAt = Carbon::now('UTC')->format('Y-m-d H:i:s');

        $query = "";
        if($classType == "trial_class") {
            $query = "select `class_time` as `old_class_time`, `student_course_id` 
            from `trial_classes` where `id` = $classId;";
        } else {
            $query = "select `w`.`class_time` as `old_class_time`, 
            `w`.`student_id`, `r`.`student_course_id` 
            from `weekly_classes` as `w`, `routine_classes` as `r` 
            where `w`.`id` = $classId and 
            `w`.`routine_class_id` = `r`.`id`";
        }

        $resultSet = DB::select($query);
        $studentCourseId = $resultSet[0]->student_course_id;
        $oldClassTime = $resultSet[0]->old_class_time;
        
        $makeupRequest = array(
            'student_course_id' => $studentCourseId,
            'class_type' => $classType,
            'class_id' => $classId,
            'availability_slot_id' => $availabilitySlotId,
            'makeup_date_time' => $makeupDateTime,
            'class_old_date_time' => $oldClassTime,
            'status' => $status,
            'created_at' => $createdAt,
            'is_teacher' => $isTeacher
        );
        return $this->studentCourseRepository->createMakeupRequest($makeupRequest, $studentId, $classId, $classType);
    }

    /**
     * Accept or reject a makeup request by a student
     * 
     * @param int $makeupRequestId
     * @param string $status
     * @return mixed
     */
    public function acceptRejectMakeupRequest($makeupRequestId, $status) 
    {
        return $this->studentCourseRepository
        ->acceptRejectMakeupRequest($makeupRequestId, $status);
    }

    public function makeupRequests($studentId) {
        return $this->studentCourseRepository->makeupRequests($studentId);
    }

    /**
     * Get student course activity
     * @param int $studentId
     * @return array
     */
    public function getStudentCourseActivity($studentId, $courseId, $limit, $offset) 
    {
        $studentCourse = $this->studentCourseRepository->getStudentCourseByCourseId($studentId, $courseId);
        
        $studentCourseActivity = $this->studentCourseActivityRepository
            ->getStudentCourseActivity($studentCourse->id, $limit, $offset);
        
        $studentCourseActivityCount = $this->studentCourseActivityRepository
            ->getStudentCourseActivityCount($studentCourse->id);

        $data = StudentCourseAcitivityResource::collection($studentCourseActivity)
            ->additional(['timezone' => $studentCourse->student->timezone]);

        return [
            'activities' => $data,
            'total' => $studentCourseActivityCount
        ];
    }
}