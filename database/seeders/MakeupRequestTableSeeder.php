<?php

namespace Database\Seeders;

use App\Models\MakeupRequest;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MakeupRequestTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $query = "select sc.id, r.student_course_id, r.slot_id, 
            w.id as class_id, w.class_time 
            from routine_classes as r, weekly_classes as w, 
            student_courses as sc 
            where sc.id = r.student_course_id and 
            r.id = w.routine_class_id
            order by w.id asc;";
        $resultSet = DB::select($query);
        $createdAt = Carbon::now('UTC')->format('Y-m-d H:i:s');

        foreach($resultSet as $result) {
            MakeupRequest::factory()->create([
                'student_course_id' => $result->student_course_id,
                'class_type' => 'App\Models\WeeklyClass',
                'class_id' => $result->class_id,
                'availability_slot_id' => $result->slot_id,
                'makeup_date_time' => $result->class_time,
                'class_old_date_time' => $result->class_time,
                'status' => 'pending',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'is_teacher' => 1,
            ]);
            MakeupRequest::factory()->create([
                'student_course_id' => $result->student_course_id,
                'class_type' => 'App\Models\WeeklyClass',
                'class_id' => $result->class_id,
                'availability_slot_id' => $result->slot_id,
                'makeup_date_time' => $result->class_time,
                'class_old_date_time' => $result->class_time,
                'status' => 'pending',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'is_teacher' => 0,
            ]);
        }
    }
}

