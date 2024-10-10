<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class PrviousTrialClasses extends Migration
{
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spPreviousTrialClasses;";

            $procedure = "
            CREATE PROCEDURE spPreviousTrialClasses(IN studentId INT, IN classTime DATETIME, IN page INT, IN recordsLimit INT) 

            BEGIN 
            select `c`.`title` as `course_title`, `s`.`name` as `student_name`, 
            `s`.`timezone` as `student_timezone`, 
            `u`.`name` as `teacher_name`, `tc`.`id` as `tw_class_id`, 
            `tc`.`status`, `tc`.`class_time` 
            from `trial_classes` as `tc`, `users` as `u`, 
            `student_courses` as `sc`, `students` as `s`, 
            `courses` as `c` 
            where `s`.`id` = studentId and 
            `s`.`id` = `sc`.`student_id` and 
            `sc`.`course_id` = `c`.`id` and 
            `sc`.`teacher_id` = `u`.`id` and 
            `tc`.`student_course_id` = `sc`.`id` and 
            `tc`.`class_time` < classTime 
            order by `tc`.`class_time` asc 
                LIMIT page, recordsLimit;
            end
            ";
            DB::unprepared($deleteProcedure);
            DB::unprepared($procedure);
            
        } catch (QueryException $e) {
            dd($e);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $deleteProcedure = "DROP procedure IF EXISTS spPreviousTrialClasses;";
        DB::unprepared($deleteProcedure);
    }
}
