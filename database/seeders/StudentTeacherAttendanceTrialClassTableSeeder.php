<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\TrialClass;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StudentTeacherAttendanceTrialClassTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $trialClasses = TrialClass::orderBy('id')->get();
        foreach ($trialClasses as $trialClass) {
            $teacherId = $trialClass->teacher_id;
            $studentId = $trialClass->student_id;
            $classId = $trialClass->id;
            $classType = 'App\Models\TrialClass';
            $joinedAt = $trialClass->class_time;
            $leftAt = Carbon::parse($trialClass->class_time)->addMinutes(25)->format('Y-m-d H:i:s');
            $createdAt = $trialClass->created_at;

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
