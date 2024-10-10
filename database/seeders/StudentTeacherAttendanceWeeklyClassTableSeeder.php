<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\WeeklyClass;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StudentTeacherAttendanceWeeklyClassTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $weeklyClasses = WeeklyClass::orderBy('id')->get();
        foreach ($weeklyClasses as $weeklyClass) {
            $teacherId = $weeklyClass->teacher_id;
            $studentId = $weeklyClass->student_id;
            $classId = $weeklyClass->id;
            $classType = 'App\Models\WeeklyClass';
            $joinedAt = $weeklyClass->class_time;
            $leftAt = Carbon::parse($weeklyClass->class_time)->addMinutes(25)->format('Y-m-d H:i:s');
            $createdAt = $weeklyClass->created_at;

            Attendance::factory()->create([
                'person_id' => $teacherId,
                'person_type' => 'App\Models\User',
                'class_id' => $classId,
                'class_type' => $classType,
                'joined_at' => $joinedAt,
                'left_at' => $leftAt,
                'created_at' => $createdAt,
            ]);

            Attendance::factory()->create([
                'person_id' => $studentId,
                'person_type' => 'App\Models\Student',
                'class_id' => $classId,
                'class_type' => $classType,
                'joined_at' => $joinedAt,
                'left_at' => $leftAt,
                'created_at' => $createdAt,
            ]);
        }
    }
}
