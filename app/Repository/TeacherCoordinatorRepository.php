<?php

namespace App\Repository;

use App\Models\AvailabilitySlot;
use App\Models\Courseable;
use App\Models\StudentCourse;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TeacherCoordinatorRepository
{

    protected $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    /**
     * Get teacher students
     * 
     * @param $teacherId
     * @return mixed
     */
    public function getTeachers($offset = 0, $limit = 50)
    {
        $teachersArray = DB::table('student_courses')
            ->join('users', 'student_courses.teacher_id', '=', 'users.id')
            ->join('courses', 'student_courses.course_id', '=', 'courses.id')
            ->where('users.user_type', 'teacher')
            ->select(
                'users.id as id',
                'users.name as name',
                'courses.title as course',
                'users.profile_photo_path as profilePic'
            )
            ->distinct() // To avoid duplicate teachers if they are associated with multiple courses
            ->skip($offset)
            ->take($limit)
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

    /**
     * total count of students of a teacher
     */
    public function getTotalStudentsOfATeacher($teacherId)
    {
        return DB::table('student_courses')
            ->where('teacher_id', $teacherId)
            ->count();
    }

    /**
     * Get students of a teacher
     * 
     * @param $teacherId
     * @param $offset
     * @param $limit
     * @return mixed
     */
    public function getTeacherStudents($teacherId, $offset, $limit) {
    
        $students = StudentCourse::orderBy('id')
            ->where('teacher_id', $teacherId)
            ->select('id', 'student_id', 'course_id', 'teacher_id', 'subscription_id')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return $students;

    }

    /**
     * Get teacher's courses
     * 
     * @param $teacherId
     * @return mixed
     */
    public function getTeacherCourses($teacherId) {
        return Courseable::orderBy('id')
            ->where('courseable_id', $teacherId)
            ->where('courseable_type', 'App\Models\User')
            ->get();
    }

    public function getLatestTrialClassRate() {
        $trialClassRate = DB::table('trial_class_rates')
            ->orderBy('id', 'desc')
            ->first();
        $rate = null;
        if ($trialClassRate) {
            $rate = $trialClassRate->rate;
        }
        return $rate;
    }

    public function getLatestRegularClassRate($teacherId) {
        $regularClassRate = DB::table('regular_class_rates')
            ->orderBy('id', 'desc')
            ->where('teacher_id', $teacherId)
            ->first();
        $rate = null;
        if ($regularClassRate) {
            $rate = $regularClassRate->rate;
        }
        return $rate;
    }

    public function assignCourseToTeacher($teacherId, $courseId) {
        $data = [
            'course_id' => $courseId,
            'courseable_id' => $teacherId,
            'courseable_type' => 'App\Models\User'
        ];
        return Courseable::create($data);
    }

    public function getTotalStudentsOfATeacherInCourse($teacherId, $courseId) {
        return StudentCourse::where('teacher_id', $teacherId)
            ->where('course_id', $courseId)
            ->count();
    }

    public function removeCourseFromTeacher($teacherId, $courseId) {
        return Courseable::where('courseable_id', $teacherId)
            ->where('course_id', $courseId)
            ->where('courseable_type', 'App\Models\User')
            ->delete();
    }

    public function getTeacherAvailability($teacherId) {
        return DB::table('availabilities as av')
            ->join('availability_slots as avs', 'av.id', '=', 'avs.availability_id')
            ->where('av.available_id', $teacherId)
            ->where('av.available_type', 'App\Models\User')
            ->select('av.id', 'avs.day_id', 'avs.slot_id', 'avs.id as availability_slot_id')
            ->get();
    }

    public function getTotalClassesScheduledInAvailabilitySlot($teacherId, $availabilitySlotId) {
        $trialCount = DB::table('trial_classes')
            ->where('teacher_id', $teacherId)
            ->where('availability_slot_id', $availabilitySlotId)
            ->count();
        $routineCount = DB::table('routine_classes')
            ->where('teacher_id', $teacherId)
            ->where('slot_id', $availabilitySlotId)
            ->count();

            $makeupCount = DB::table('makeup_requests')
            ->where('availability_slot_id', $availabilitySlotId)
            ->count();

        return ($trialCount + $routineCount + $makeupCount);
    }


    public function deleteTeacherAvailability($availabilitySlotId) {
        return AvailabilitySlot::where('id', $availabilitySlotId)->delete();
    }
}