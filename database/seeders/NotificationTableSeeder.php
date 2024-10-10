<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NotificationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $students = Student::orderBy('id')->get();
        $createdAt = Carbon::now('UTC')->format('Y-m-d H:i:s');

        foreach($students as $student) {
            Notification::factory()->create([
                'user_id' => $student->user_id,
                'student_id' => $student->id,
                'type' => 'student',
                'message' => Str::random(10),
                'read' => 0,
            ]);
        }
    }
}

