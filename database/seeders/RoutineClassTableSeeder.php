<?php

namespace Database\Seeders;

use App\Models\AvailabilitySlot;
use App\Models\RoutineClass;
use App\Models\TrialClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoutineClassTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $trialClasses = TrialClass::orderBy('id')->get();
        $createdAt = Carbon::now()->format('Y-m-d H:i:s');
        foreach ($trialClasses as $trial) {
            $teacherId = $trial->teacher_id;
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
            if(count($slots) < 2) {
                continue;
            }

            for($i = 0; $i < 2; $i++) {
                RoutineClass::factory()->create([
                    'student_id' => $trial->student_id,
                    'teacher_id' => $trial->teacher_id,
                    'slot_id' => $slots[$i],
                    'student_course_id' => $trial->student_course_id,
                    'status' => 'active',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

        }

    }
}
