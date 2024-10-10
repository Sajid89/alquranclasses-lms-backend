<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class change_teacher_reasons_list extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $reasons = [
                ['reason' => 'Teaching Style: I am not satisfied with teaching style.'],
                ['reason' => 'Pace of Instruction: The pace at which the teacher conducts lesson is too fast or too slow.'],
                ['reason' => 'Communication Issues: A lack of clear communication or misunderstandings between the student and teacher.']
            ];
        DB::table('change_teacher_reasons')->insert($reasons);
    }
}
