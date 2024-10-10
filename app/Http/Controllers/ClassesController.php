<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClassScheduleRequest;
use App\Http\Requests\TeacherCoordinatorRequest;
use App\Repository\AttendanceRepository;
use App\Repository\Interfaces\TrialClassRepositoryInterface;
use App\Repository\UserRepository;
use App\Repository\WeeklyClassRepository;
use App\Services\ClassesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassesController extends Controller
{
    private $weeklyClassRepository;
    private $trialClassRepository;
    private $classScheduleRequest;
    private $teacherCoordinatorRequest;

    private $userRepository;
    private $classesService;

    public function __construct(
        WeeklyClassRepository $weeklyClassRepository,
        TrialClassRepositoryInterface $trialClassRepository,
        ClassScheduleRequest $classScheduleRequest,
        UserRepository $userRepository,
        TeacherCoordinatorRequest $teacherCoordinatorRequest,
        ClassesService $classesService
    )
    {
        $this->weeklyClassRepository = $weeklyClassRepository;
        $this->trialClassRepository = $trialClassRepository;
        $this->classScheduleRequest = $classScheduleRequest;
        $this->userRepository = $userRepository;
        $this->teacherCoordinatorRequest = $teacherCoordinatorRequest;
        $this->classesService = $classesService;
    }

    /**
     * Get all class schedules for a customer
     * 
     * @return mixed
     */
    public function getAllClassSchedulesForCustomer(Request $request)
    {
        $customerId = Auth::user()->id;
        $this->classScheduleRequest->getAllClassSchedulesForCustomerOrStudent($request);
        
        if (!$customerId) {
            return $this->error('Customer not found', 404);
        }

        $studentId = $request->has('student_id') ? $request->student_id : null;
        $teacherId = $request->has('teacher_id') ? $request->teacher_id : null;
        $todayWeeklyClasses = $this->weeklyClassRepository->getTodaysClassesForCustomer($customerId, $studentId, $teacherId);
        $todayTrialClasses = $this->trialClassRepository->getTodaysClassesForCustomer($customerId, $studentId, $teacherId);

        $upcomingWeeklyClasses = $this->weeklyClassRepository->getUpcomingCustomerClasses($customerId, $studentId, $teacherId);
        $upcomingTrialClasses = $this->trialClassRepository->getUpcomingCustomerClasses($customerId, $studentId, $teacherId);

        $classes = [
            'today'    => $todayTrialClasses->concat($todayWeeklyClasses),
            'upcoming' => $upcomingTrialClasses->concat($upcomingWeeklyClasses)
        ];

        return $this->success($classes, 'Classes fetched successfully', 200);
    }

    /**
     * Get all class schedules(trial+weekly) for a student
     * 2 weeks before and after the given date
     * 
     * @param Request $request
     * @return mixed
     */
    public function getStudentClassSchedulesForCourse(Request $request)
    {
        $this->classScheduleRequest->validateStudentClassSchedulesForCourse($request);

        $studentId = $request->student_id;
        $courseId = $request->course_id;
        $date = $request->date;
        $customerId = Auth::user()->id;
        
        $weeklyClasses = $this->weeklyClassRepository->getStudentClassSchedulesForCourse($customerId, $studentId, $courseId, $date);
        $trialClasses  = $this->trialClassRepository->getStudentClassSchedulesForCourse($customerId, $studentId, $courseId, $date);

        $mergedClasses = [
            'today'    => $trialClasses['today']->concat($weeklyClasses['today']),
            'upcoming' => $trialClasses['upcoming']->concat($weeklyClasses['upcoming']),
            'previous' => $trialClasses['previous']->concat($weeklyClasses['previous']),
        ]; 

        return $this->success($mergedClasses, 'Classes fetched successfully', 200);
    }

    /**
     * Get all class schedules for a student
     * 
     * @param Request $request: student_id
     * @return mixed
     */
    public function studentClassesSchedule(Request $request)
    {
        $this->classScheduleRequest->validateStudendClassesSchedule($request);
        $studentId = $request->student_id;
        $data = $this->weeklyClassRepository->studentClassesSchedule($studentId);
        
        return $this->success($data, 'Classes fetched successfully', 200);
    }

    /**
     * Get all previous classes for a student
     * 
     * @param Request $request: student_id, page, limit
     * @return mixed
     */
    public function studentPreviousClassesSchedule(Request $request) 
    {
        $this->classScheduleRequest->validatestudentPreviousClassesSchedule($request);
        $studentId = $request->student_id;
        $page = $request->page;
        $limit = $request->limit;
        $offset = ($page - 1) * $limit;
        $data = $this->weeklyClassRepository->studentPreviousClassesSchedule($studentId, $offset, $limit);
        
        return $this->success($data, 'Previous Classes fetched successfully', 200);
    }

    /**
     * Get all upcoming classes for a student
     * 
     * @param Request $request: student_id, page, limit
     * @return mixed
     */
    public function studentUpcomingClassesSchedule(Request $request) 
    {
        $this->classScheduleRequest->validatestudentUpcomingClassesSchedule($request);
        $studentId = $request->student_id;
        $page = $request->page;
        $limit = $request->limit;
        $offset = ($page - 1) * $limit;
        $data = $this->weeklyClassRepository->studentUpcomingClassesSchedule($studentId, $offset, $limit);
        
        return $this->success($data, 'Upcoming Classes fetched successfully', 200);
    }

    /**
     * Cancel a class for a student
     * 
     * @param Request $request: class_id
     * @return mixed
     */
    public function cancelClass(Request $request) 
    {
        $this->classScheduleRequest->validateCancelClass($request);
        $classId = $request->class_id;
        $classType = $request->class_type;

        $data = $this->classesService->cancelClass($classId, $classType);
        
        return $this->success($data, 'Class cancelled successfully', 200);
    }

    /**
     * Get course activity for a student
     * 
     * @param Request $request: course_id, student_id, page, limit
     * @return mixed
     */
    public function courseActivity(Request $request) 
    {
        $this->classScheduleRequest->validateCourseActivity($request);
        $courseId = $request->course_id;
        $studentId = $request->student_id;
        $page = $request->page;
        $limit = $request->limit;
        $offset = ($page - 1) * $limit;
        $data = $this->weeklyClassRepository->courseActivity($courseId, $studentId, $offset, $limit);
        
        return $this->success($data, 'Course activity fetched successfully', 200);
    }

    /**
     * Get all class schedules for a customer
     * 
     * @return mixed
     */
    public function teacherPreviousClasses(Request $request)
    {
        $user = Auth::user();
        if($user) {
            $this->classScheduleRequest->validateTeacherPreviousClasses($request);
            $page = $request->page;
            $limit = $request->limit;
            $teacherId = $user->id;
            $offset = ($page - 1) * $limit;
            $data = $this->weeklyClassRepository->teacherPreviousClasses($teacherId, $offset, $limit);
            
            return $this->success($data, 'Previous Classes fetched successfully', 200);
        } else {
            return $this->error('Unauthenticated', 404);
        }
    }

    /**
     * Get all class schedules for a customer
     * 
     * @return mixed
     */
    public function teacherUpcomingClasses(Request $request)
    {
        $user = Auth::user();
        if($user) {
            $this->classScheduleRequest->validateTeacherUpcomingClasses($request);
            $page = $request->page;
            $limit = $request->limit;
            $teacherId = $user->id;
            $offset = ($page - 1) * $limit;
            $data = $this->weeklyClassRepository->teacherUpcomingClasses($teacherId, $offset, $limit);
            
            return $this->success($data, 'Upcoming Classes fetched successfully', 200);
        } else {
            return $this->error('Unauthenticated', 404);
        }

    }

    public function getCoordinatedTeachersClassSchedule(Request $request)
    {
        $teacherCoordinatorId = Auth::user()->id;
        $teacherIds = $this->userRepository->getCoordinatedTeachers($teacherCoordinatorId);
        
        $todayWeeklyClasses = $this->weeklyClassRepository->getTodaysClassesForCoordinator($teacherIds);
        $todayTrialClasses = $this->trialClassRepository->getTodaysClassesForCoordinator($teacherIds);

         $upcomingWeeklyClasses = $this->weeklyClassRepository->getUpcomingClassesForCoordinator($teacherIds);
         $upcomingTrialClasses = $this->trialClassRepository->getUpcomingClassesForCoordinator($teacherIds);
       
        $classes = [
            'today'    => $todayTrialClasses->concat($todayWeeklyClasses),
            'upcoming' => $upcomingTrialClasses->concat($upcomingWeeklyClasses)
        ];

        return $this->success($classes, 'Classes fetched successfully', 200);
    }

    public function coordinatedTeacherPreviousClasses(Request $request) {
        $teacherCoordinatorId = Auth::user()->id;
        $this->teacherCoordinatorRequest->validateAllTeachersRequest($request);
        $teacherIds = $this->userRepository->getCoordinatedTeachers($teacherCoordinatorId);

        $previousWeeklyClasses = $this->weeklyClassRepository->getPreviousClassesForCoordinator($teacherIds);
        $previousTrialClasses = $this->trialClassRepository->getPreviousClassesForCoordinator($teacherIds);

        $classes = [
            'previous' => $previousTrialClasses->concat($previousWeeklyClasses)
        ];

        return $this->success($classes, 'Previous Classes fetched successfully', 200);

    }
    
    /**
     * upcoming classes for all teachers 
     */
    public function coordinatedTeacherUpcomingClasses(Request $request) {
        $this->teacherCoordinatorRequest->validateAllTeachersRequest($request);
        $teacherCoordinatorId = Auth::user()->id;
        $teacherIds = $this->userRepository->getCoordinatedTeachers($teacherCoordinatorId);
        $page = $request->page;
        $limit = $request->limit;
        $offset = ($page - 1) * $limit;

        //to call service method
        $upcomingWeeklyClasses = $this->classesService->coordinatedTeacherUpcomingClasses($teacherIds, $offset, $limit);
        
        return $this->success($upcomingWeeklyClasses, 'Upcoming Classes fetched successfully', 200);

    }
    
    /**
     * previous classes for all teachers 
     */
   public function coordinatedAllTeacherPreviousClasses(Request $request) {
    $this->teacherCoordinatorRequest->validateAllTeachersRequest($request);
    $teacherCoordinatorId = Auth::user()->id;
    $teacherIds = $this->userRepository->getCoordinatedTeachers($teacherCoordinatorId);
    $page = $request->page;
    $limit = $request->limit;
    $offset = ($page - 1) * $limit;

    //to call service method
    $previousClasses = $this->classesService->coordinatedTeacherPreviousClasses($teacherIds, $offset, $limit);
    
    return $this->success($previousClasses, 'Previous Classes fetched successfully', 200);

   }


}