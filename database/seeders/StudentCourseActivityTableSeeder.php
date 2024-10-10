<?php

namespace Database\Seeders;

use App\Models\StudentCourseActivity;
use App\Models\StudentCourse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StudentCourseActivityTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $studentCourses = StudentCourse::orderBy('id')->get();

        $createdAt = Carbon::now('UTC')->format('Y-m-d H:i:s');

        foreach($studentCourses as $studentCourse) {
            for($i = 0; $i < 4; $i++) {
                StudentCourseActivity::factory()->create([
                    'student_course_id' => $studentCourse->id,
                    'activity_type' => 'Assignment',
                    'description' => Str::random(10),
                    'file_size' => '',
                    'file_name' => '',
                ]);
            }
        }
    }
}

