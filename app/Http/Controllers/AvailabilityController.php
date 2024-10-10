<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use Illuminate\Http\Request;
use App\Http\Requests\AvailabilityRequest;
use App\Models\Student;
use App\Repository\StudentCoursesRepository;
use App\Services\AvailabilityService;

class AvailabilityController extends Controller
{
    protected $availabilityRequest;
    protected $availabilityService;
    protected $studentCourse;

    public function __construct(
        AvailabilityRequest $availabilityRequest,
        AvailabilityService $availabilityService,
        StudentCoursesRepository $studentCourse
    )
    {
        $this->availabilityRequest = $availabilityRequest;
        $this->availabilityService = $availabilityService;
        $this->studentCourse = $studentCourse;
    }

    public function getTeachersForStudent(Request $request)
    {
        $teacherId = 0;
        if ($request->has('change_plan'))
        {
            $this->availabilityRequest->validateGetCurrentTeacherForStudent($request);
            
            $studentId = $request->has('student_id') ? $request->student_id : null;
            $courseId = $request->course_id;
            $studentCourse = $this->studentCourse->getStudentCourseByCourseId($studentId, $courseId);
            $teacherId = $studentCourse->teacher_id;
            $teacherPreference = $studentCourse->teacher_preference;
            $shiftId = $studentCourse->shift_id;
            $studentTimezone = Student::find($studentId)->timezone;
        }
        else
        {
            $this->availabilityRequest->validateGetTeachersForStudent($request);

            $studentId = $request->has('student_id') ? $request->student_id : null;
            $teacherId = null;
            $courseId = $request->course_id;
            $teacherPreference = $request->teacher_preference;
            $shiftId = $request->shift_id;
            $studentTimezone = $request->student_timezone;
        }

        $isTeacherChanged = false;
        if($request->has('change_teacher'))
        {
            $isTeacherChanged = $request->change_teacher;
            if($request->has('teacher_id')) {
                $teacherId = $request->teacher_id;
            }
        }
        
        $teachers = $this->availabilityService->getTeacherList(
            $courseId, $teacherPreference, $shiftId, 
            $studentTimezone, $studentId, $teacherId, $isTeacherChanged
        );

        return $this->success($teachers, 'Teachers fetched successfully', 200);
    }

    /**
     * Get teacher availability for a specific day and shift
     * while creating a new makeup request
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTeacherAvailability(Request $request)
    {
        $this->availabilityRequest->validateGetTeacherAvailability($request);

        $teacherId = $request->teacher_id;
        $studentId = $request->student_id;
        $courseId = $request->course_id;
        $dayId = $request->day_id;
        $shiftId = $request->shift_id;

        $teacher = $this->availabilityService
            ->getTeacherSchedule($teacherId, $studentId, $courseId, $dayId, $shiftId);

        return $this->success($teacher, 'Teacher availability fetched successfully', 200);
    }
}
