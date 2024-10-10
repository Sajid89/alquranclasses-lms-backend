<?php

namespace Database\Seeders;

use App\Models\StudentCourse;
use App\Models\TrialClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrialClassTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $studentCourses = StudentCourse::orderBy('id')->get();
        
        foreach ($studentCourses as $studentCourse) {
            $teacherId = $studentCourse->teacher_id;
            $query = "select u.id as user_id, u.timezone, a.created_at, asl.id
                from availabilities as a, availability_slots as asl, 
                users as u 
                where u.id = $teacherId and 
                u.id = a.available_id and 
                a.id = asl.availability_id and 
                a.available_type = 'App\\\Models\\\User'
                order by asl.id asc;";

                    $resultSet = DB::select($query);

            $slots = array();
            foreach ($resultSet as $result) {
                if ($result && isset($result->id)) {
                    $slots[] = $result->id;
                }
            }
            if(count($slots) < 1) {
                continue;
            }

            $availabilitySlotId = $slots[array_rand($slots)];
            TrialClass::factory()->create([
                'customer_id' => $studentCourse->student->user_id,
                'student_id' => $studentCourse->student_id,
                'teacher_id' => $teacherId,
                'availability_slot_id' => $availabilitySlotId,
                'student_course_id' => $studentCourse->id,
            ]);
        }

    }
}
