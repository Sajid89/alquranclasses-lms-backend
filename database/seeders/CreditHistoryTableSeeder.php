<?php

namespace Database\Seeders;

use App\Models\CreditHistory;
use App\Models\TrialClass;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CreditHistoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $query = "select sc.id, r.student_course_id, w.id as class_id 
            from routine_classes as r, weekly_classes as w, 
            student_courses as sc
            where sc.id = r.student_course_id and 
            r.id = w.routine_class_id 
            order by w.id asc;";
        $weeklyClasses = DB::select($query);
        $createdAt = Carbon::now('UTC')->format('Y-m-d H:i:s');

        foreach ($weeklyClasses as $trial) {
            CreditHistory::factory()->create([
                'student_course_id' => $trial->id,
                'class_type' => 'App\Models\WeeklyClass',
                'class_id' => $trial->class_id,
                'created_at' => $createdAt,
                'expired_at' => $createdAt,
            ]);
        }
    }
}
