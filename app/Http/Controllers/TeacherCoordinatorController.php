<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherCoordinatorRequest;
use App\Repository\UserRepository;
use App\Services\TeacherCoordinatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherCoordinatorController extends Controller
{
    protected $teacherCoordinatorRequest;
    protected $teacherCoordinatorService;
    protected $userRepository;

    public function __construct(
        TeacherCoordinatorRequest $teacherCoordinatorRequest,
        TeacherCoordinatorService $teacherCoordinatorService,
        UserRepository $userRepository
    )
    {
        $this->teacherCoordinatorRequest = $teacherCoordinatorRequest;
        $this->teacherCoordinatorService = $teacherCoordinatorService;
        $this->userRepository = $userRepository;
    }

    /**
     * Get teacher's students with unread messages count
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getTeachersWithUnreadMessagesCount()
    {
        $teacherCoordinatorId = Auth::id();
        $teachers  = $this->teacherCoordinatorService->getTeachersWithUnreadMessagesCount($teacherCoordinatorId);

        return $this->success($teachers, 'Teachers with unread messages count retrieved successfully');
    }

    /**
     * Get all teacher's notifications
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getTeachersNotifications(Request $request) {
        $teacherCoordinatorId = Auth::id();
        $this->teacherCoordinatorRequest->validateTeachersNotifications($request);

        $page = $request->page;
        $limit = $request->limit;
        $offset = ($page - 1) * $limit;
        $data = $this->teacherCoordinatorService->getTeachersNotifications($teacherCoordinatorId, $offset, $limit);
        return $this->success($data, 'Teachers notifications fetched successfully');
    }

    /**
     * Get all teacher's for a teacher coordinator
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getListOfTeachers(Request $request) {
       
        $teacherCoordinatorId = Auth::id();
        $this->teacherCoordinatorRequest->validateAllTeachersRequest($request);
        $page = $request->page;
        $limit = $request->limit;
        $offset = ($page - 1) * $limit;

        $data = $this->teacherCoordinatorService->getListOfTeachers($teacherCoordinatorId, $offset, $limit);

        return $this->success($data, 'List of teachers fetched successfully');
    }


    /**
     * to get students of a particular teacher
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getTeacherStudents(Request $request) {
        //$teacherCoordinatorId = Auth::id();
        
        $this->teacherCoordinatorRequest->validateTeacherStudentsRequest($request);
        $teacherId = $request->teacher_id;
        $page = $request->page;
        $limit = $request->limit;
        $offset = ($page - 1) * $limit;

        $data = $this->teacherCoordinatorService->getTeacherStudents($teacherId, $offset, $limit);

        return $this->success($data, 'Teacher students fetched successfully');
    }

    /**
     * courses list of a teacher
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getTeacherCourses(Request $request) {
        $this->teacherCoordinatorRequest->validateGetTeacherCourses($request);
        $teacherId = $request->teacher_id;
        
        $data = $this->teacherCoordinatorService->getTeacherCourses($teacherId);

        return $this->success($data, 'Teacher courses fetched successfully');
    }

    /**
     * get teacher profile
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getTeacherProfile(Request $request) {
        $this->teacherCoordinatorRequest->validateGetTeacherCourses($request);
        $teacherId = $request->teacher_id;
        
        $data = $this->teacherCoordinatorService->getTeacherProfile($teacherId);

        return $this->success($data, 'Teacher profile fetched successfully');
    }

    /**
     * to assign a course to a teacher,
     * first show a gui to teacher coordinator to select a teacher 
     * and then show a gui in which all courses are listed with 
     * on / off button to assign a course to a teacher
     * or to remove a course from a teacher
     * if user makes on the radio button, then call assignCourseToTeacher API
     * of user makes off the radio button, then call removeCourseFromTeacher API
     */
    public function assignCourseToTeacher(Request $request) {
        $this->teacherCoordinatorRequest->validateAssignCourseToTeacher($request);
        $teacherId = $request->teacher_id;
        $courseId = $request->course_id;

        $data = $this->teacherCoordinatorService->assignCourseToTeacher($teacherId, $courseId);

        return $this->success($data, 'Course assigned to teacher successfully');
    }

    public function removeCourseFromTeacher(Request $request) {
        $this->teacherCoordinatorRequest->validateAssignCourseToTeacher($request);
        $teacherId = $request->teacher_id;
        $courseId = $request->course_id;

        $message = $this->teacherCoordinatorService->removeCourseFromTeacher($teacherId, $courseId);

        return $this->success([], $message);
    }

    public function getTeacherAvailability(Request $request) {
        $this->teacherCoordinatorRequest->validateGetTeacherAvailability($request);

        $teacherId = $request->teacher_id;
     
        $teacherAvailability = $this->teacherCoordinatorService->getTeacherAvailability($teacherId);

        return $this->success($teacherAvailability, 'Teacher availability fetched successfully', 200);
    }

    public function deleteTeacherAvailability(Request $request) {
        $this->teacherCoordinatorRequest->validateDeleteTeacherAvailability($request);

        $teacherId = $request->teacher_id;
        $availabilitySlotId = $request->availability_slot_id;
        
        $message = $this->teacherCoordinatorService->deleteTeacherAvailability($teacherId, $availabilitySlotId);

        return $this->success([], $message);
    }
}