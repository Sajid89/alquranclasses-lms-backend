<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ProcedureUpcomingPrevWeeklyClasses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spPreviousAndUpcomingWeeklyClasses;";

            $procedure = "
            CREATE PROCEDURE spPreviousAndUpcomingWeeklyClasses(IN studentId int, IN classTime datetime) 

            BEGIN

            select `q`.* from (
                select `c`.`title` as `course_title`, `s`.`name` as `student_name`, 
                `s`.`timezone` as `student_timezone`, 
                `u`.`name` as `teacher_name`, `wc`.`id` as `tw_class_id`, 
                `wc`.`status`, `wc`.`class_time`, 'previous' AS `previous_or_upcoming` 
                from `weekly_classes` as `wc`, `users` as `u`, 
                `student_courses` as `sc`, `students` as `s`, 
                `courses` as `c`, `routine_classes` as `rt` 
                where `s`.`id` = studentId and 
                `s`.`id` = `sc`.`student_id` and 
                `sc`.`course_id` = `c`.`id` and 
                `sc`.`teacher_id` = `u`.`id` and 
                `rt`.`student_course_id` = `sc`.`id` and 
                `wc`.`routine_class_id` = `rt`.`id` and 
                `wc`.`class_time` < classTime 
                UNION ALL 
                select `c`.`title` as `course_title`, `s`.`name` as `student_name`, 
                `s`.`timezone` as `student_timezone`, 
                `u`.`name` as `teacher_name`, `wc`.`id` as `tw_class_id`, 
                `wc`.`status`, `wc`.`class_time`, 'upcoming' AS `previous_or_upcoming` 
                from `weekly_classes` as `wc`, `users` as `u`, 
                `student_courses` as `sc`, `students` as `s`, 
                `courses` as `c`, `routine_classes` as `rt` 
                where `s`.`id` = studentId and 
                `s`.`id` = `sc`.`student_id` and 
                `sc`.`course_id` = `c`.`id` and 
                `sc`.`teacher_id` = `u`.`id` and 
                `rt`.`student_course_id` = `sc`.`id` and 
                `wc`.`routine_class_id` = `rt`.`id` and 
                `wc`.`class_time` >= classTime
                ) as `q` 
                order by `q`.`class_time` asc;

            END";

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
        $deleteProcedure = "DROP procedure IF EXISTS spPreviousAndUpcomingWeeklyClasses;";
        DB::unprepared($deleteProcedure);        
    }
}
