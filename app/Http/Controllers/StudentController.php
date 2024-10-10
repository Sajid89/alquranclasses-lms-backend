<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;
use App\Http\Resources\StudentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StudentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    protected $studentRequest;
    protected $studentService;

    public function __construct(
        StudentRequest $studentRequest,
        StudentService $studentService
    )
    {
        $this->studentRequest = $studentRequest;
        $this->studentService = $studentService;
    }

    /**
     * Add a new student with: 
     * course, availability, class schedule
     * 
     * @param Request $request
     * @return mixed
     */
    public function addStudent(Request $request)
    {
        if ($request->has('student_id')) 
        {
            $this->studentRequest->validateEnrollNewCourse($request);
        }
        else
        {
            $this->studentRequest->validateAddStudent($request);
        }

        DB::beginTransaction();

        try {
            $student = $this->studentService->createStudent($request);
            
            if (isset($student['error'])) {
                DB::rollBack();
                return $this->error($student['error'], 400);
            }

            DB::commit();
            return $this->success(new StudentResource($student), 'Student added successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exception at ' . $request->url() . ': ' . $e);
            return $this->error('An error occurred while adding the student', 400);
        }
    }

    /**
     * Get all students for a customer/teacher
     * 
     * @param Request $request
     * @return mixed
     */
    public function getStudentProfiles()
    {
        $userId = Auth::id();
        $students = $this->studentService->getAllCustomerStudents($userId);

        if ($students->isEmpty())
        {
            return $this->success([], 'No students found');
        }

        return $this->success($students, 'Students retrieved successfully');
    }

     /**
     * Cancel student subscription
     * remove upcoming weekly classes
     * charge $5 fee for the teacher change process
     * remove subscription from db of that course
     * email to customer, previous teacher and customer support
     * 
     * @param $request
     */
    public function changeTeacher(Request $request) 
    {        
        $this->studentRequest->validatechangeTeacher($request);
        $this->studentService->changeTeacher($request);

        return $this->success([], 'Teacher changed successfully');
    }

    /**
     * Get all courses for a student
     * 
     * @param Request $request
     * @return mixed
     */
    public function getStudentCourses(Request $request)
    {
        $this->studentRequest->validateGetStudentCourses($request);
        $studentId = $request->student_id;
        $courses = $this->studentService->getStudentCourses($studentId);

        if (count($courses) === 0)
        {
            return $this->success([], 'No courses found for this student', 404);
        }

        return $this->success($courses, 'Courses retrieved successfully');
    }

    /**
     * Get all students for a teacher
     * 
     * @param Request $request
     * @return mixed
     */
    public function getStudentsForTeacher(Request $request)
    {
        $teacherId = Auth::id();
        $students = $this->studentService->getStudentsForTeacher($teacherId);

        if (count($students) === 0)
        {
            return $this->success([], 'No students found for this teacher', 404);
        }

        return $this->success($students, 'Students retrieved successfully');
    }

    public function createMakeupRequest(Request $request) {
        $user = Auth::user();
        if($user) {
            $this->studentRequest->validateCreateMakeupRequest($request);
            $studentId = $request->student_id;
            $classId = $request->weekly_class_id;
            $availabilitySlotId = $request->availability_slot_id;
            $makeupDateTime = $request->makeup_date_time;
            $classType = $request->class_type;

            $message = $this->studentService->createMakeupRequest($studentId, $classId, $availabilitySlotId, $makeupDateTime, $classType);
            
            return $this->success([], $message, 200);
        } else {
            return $this->error('Unauthenticated', 404);
        }
    }

    public function makeupRequests(Request $request) { 
        $this->studentRequest->validateMakeupRequests($request);
        $studentId = $request->student_id;
        $makeupRequests = $this->studentService->makeupRequests($studentId);
    }

    /**
     * Accept or reject a makeup request
     * 
     * @param Request $request
     * @return mixed
     */
    public function acceptRejectMakeupRequest(Request $request) {
        $this->studentRequest->validateAcceptRejectMakeupRequest($request);
        $makeupRequestId = $request->makeup_request_id;
        $status = $request->status;

        $message = $this->studentService
            ->acceptRejectMakeupRequest($makeupRequestId, $status);
        return $this->success([], $message, 200);
}

    /**
     * Get all activities for a student in a course
     * 
     * @param Request $request
     * @return mixed
     */
    public function getStudentCourseActivity(Request $request) 
    {
        $this->studentRequest->validateGetStudentCourseActivity($request);
        $studentId = $request->student_id;
        $courseId = $request->course_id;
        $page = $request->page;
        $limit = $request->limit;
        $offset = ($page - 1) * $limit;
        $activities = $this->studentService
            ->getStudentCourseActivity($studentId, $courseId, $limit, $offset);

        return $this->success($activities, 'Activities retrieved successfully');
    }
}
