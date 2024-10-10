<?php

namespace App\Repository;

use App\Jobs\SendMailToCustomerOnMakeupRequest;
use App\Models\MakeupRequest;
use App\Models\Notification;
use App\Models\Student;
use App\Models\StudentCourse;
use App\Models\User;
use App\Repository\Interfaces\TeacherRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TeacherRepository implements TeacherRepositoryInterface
{

    protected $model;
    private $studentRepository;

    public function __construct(User $user, StudentRepository $studentRepository)
    {
        $this->model = $user;
        $this->studentRepository = $studentRepository;
    }

    /**
     * Get teachers for a student to choose from
     * for trial/subscription
     * 
     * @param $teacherPreference
     * @param $courseId
     * @param $shiftSlotIds
     * @return mixed
     */
    public function getTeachers(
        $teacherPreference, $courseId, $shiftSlotIds, 
        $studentId, $isTeacherChanged, $teacherId = 0
    )
    {
        $query = $this->model::where('user_type', 'teacher')
            ->where('id', '!=', $teacherId)
            ->where('gender', $teacherPreference)
            ->whereHas('courses', function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->whereHas('availability.availabilitySlots', function ($query) use ($shiftSlotIds) {
                $query->whereIn('slot_id', $shiftSlotIds);
            })
            ->whereDoesntHave('availability.availabilitySlots.routineClass', function ($query) use ($shiftSlotIds) {
                $query->whereIn('slot_id', $shiftSlotIds);
            })
            ->whereDoesntHave('availability.availabilitySlots.makeup_request', function ($query) use ($shiftSlotIds) {
                $query->whereIn('availability_slot_id', $shiftSlotIds);
            });

        if ($studentId !== null && !$isTeacherChanged) {
            $studentCourse = StudentCourse::where('student_id', $studentId)->first();
            if ($studentCourse) {
                $query->orWhere('id', $studentCourse->teacher_id);
            }
            $query->take(3);
        } else {
            $query->take(3);
        }

        return $query->get();
    }

    /**
     * Get the current teacher
     * 
     * @param $teacherId
     * @return mixed
     */
    public function getCurrentTeacher($teacherId)
    {
        $studentsArray = DB::table('student_courses')
            ->join('students', 'student_courses.student_id', '=', 'students.id')
            ->join('courses', 'student_courses.course_id', '=', 'courses.id')
            ->where('student_courses.teacher_id', $teacherId)
            ->select(
                'students.id as id',
                'students.name as name',
                'courses.name as course',
                'students.profile_photo_url as profilePic'
            )
            ->get()
            ->toArray();

        return $studentsArray;
    }

    /**
     * Get teacher students
     * 
     * @param $teacherId
     * @return mixed
     */
    public function getStudents($teacherId, $offset = 0, $limit = 10)
    {
        $studentsArray = DB::table('student_courses')
            ->join('students', 'student_courses.student_id', '=', 'students.id')
            ->join('courses', 'student_courses.course_id', '=', 'courses.id')
            ->join('subscriptions', 'students.id', '=', 'subscriptions.student_id')
            ->where('student_courses.teacher_id', $teacherId)
            ->where('subscriptions.payment_status', 'succeeded')
            ->select(
                'students.id as id',
                'students.name as name',
                'courses.title as course',
                'students.profile_photo_url as profilePic'
            )
            ->distinct() // To avoid duplicate students if they have multiple succeeded subscriptions
            ->skip($offset)
            ->take($limit)
            ->get()
            ->toArray();

        return $studentsArray;
    }

    /**
     * Get teacher's for all of his students 
     * for a customer to chat
     * 
     * @param $teacherId
     * @return mixed
     */
    public function getTeachersForCustomerForChat($customerId)
    {
        $students = $this->studentRepository->getStudentIdsForCustomer($customerId);
        
        $teachersArray = DB::table('student_courses')
            ->join('users', 'student_courses.teacher_id', '=', 'users.id')
            ->join('courses', 'student_courses.course_id', '=', 'courses.id')
            ->where('users.user_type', 'teacher')
            ->whereIn('student_courses.student_id', $students)
            ->groupBy('users.id', 'users.name', 'courses.title', 'users.profile_photo_path')
            ->select(
                'users.id as id',
                'users.name as name',
                'courses.title as course',
                'users.profile_photo_path as profilePic'
            )
            ->distinct() // To avoid duplicate teachers if they are associated with multiple courses
            ->get()
            ->toArray();
        
        return $teachersArray;
    }

    public function getTeacherById($teacherId)
    {
        return $this->model::where('id', $teacherId)->first();
    }

    public function updatePassword($teacherId, $data) {

        return User::where('id', $teacherId)->update($data);
    }
}