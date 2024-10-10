<?php

namespace Database\Seeders;

use App\Models\StudentCourse;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StudentCourseTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subscripitons = Subscription::orderBy('student_id')->get();
        $teacherIds = User::where('user_type', 'teacher')->pluck('id')->toArray();
        $createdAt = Carbon::now('UTC')->format('Y-m-d H:i:s');

        $i = 0;
        $courseId = 0;
        foreach ($subscripitons as $sub) {
            $teacherId = $teacherIds[array_rand($teacherIds)];
            $shift = 0;
            if($i > 5) {
                $i = 1;
            }
            $i++;
            $shift = $i;

            if($courseId > 3) {
                $courseId = 1;
            } else {
                $courseId++;
            }
            StudentCourse::factory()->create([
                'student_id' => $sub->student_id,
                'course_id' => $courseId,
                'course_level' => 'beginner',
                'teacher_id' => $teacherId,
                'teacher_preference' => 'male',
                'shift_id' => $shift,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'subscription_id' => $sub->id,
            ]);
        }
    }
}
