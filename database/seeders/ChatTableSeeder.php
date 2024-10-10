<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\StudentCourse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ChatTableSeeder extends Seeder
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

        foreach ($studentCourses as $studentCourse) {
            for($i = 0; $i < 10; $i++) {
                Chat::factory()->create([
                    'from' => $studentCourse->student_id,
                    'to' => $studentCourse->teacher_id,
                    'message' => Str::random(20),
                    'seen' => 1,
                    'type' => 'App\Models\Student',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                Chat::factory()->create([
                    'from' => $studentCourse->teacher_id,
                    'to' => $studentCourse->student_id,
                    'message' => Str::random(20),
                    'seen' => 1,
                    'type' => 'App\Models\User',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

            }

        }

    }
}
