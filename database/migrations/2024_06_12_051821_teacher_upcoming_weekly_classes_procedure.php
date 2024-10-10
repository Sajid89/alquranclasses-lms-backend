<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TeacherUpcomingWeeklyClassesProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spTeacherUpcomingWeeklyClasses;";

            $procedure = "
            CREATE PROCEDURE spTeacherUpcomingWeeklyClasses(IN teacherId INT, IN classTime DATETIME, IN page INT, IN recordsLimit INT) 

            BEGIN

                 select `c`.`title` as `course_title`, `s`.`id` as `student_id`, 
                `s`.`name` as `student_name`, 
                `s`.`timezone` as `student_timezone`, 
                `u`.`id` as `teacher_id`, `u`.`name` as `teacher_name`, 
                `u`.`timezone` as `teacher_timezone`, 
                `wc`.`id` as `tw_class_id`, 
                `wc`.`status`, `wc`.`class_time` 
                from `weekly_classes` as `wc`, `users` as `u`, 
                `student_courses` as `sc`, `students` as `s`, 
                `courses` as `c`, `routine_classes` as `rt` 
                where `u`.`id` = teacherId and 
                `sc`.`teacher_id` = teacherId and 
                `s`.`id` = `sc`.`student_id` and 
                `sc`.`course_id` = `c`.`id` and 
                `sc`.`teacher_id` = `u`.`id` and 
                `rt`.`student_course_id` = `sc`.`id` and 
                `wc`.`routine_class_id` = `rt`.`id` and 
                `wc`.`class_time` >= classTime and 
                `wc`.`deleted_at` is null 
                order by `wc`.`class_time` asc 
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
        $deleteProcedure = "DROP procedure IF EXISTS spTeacherUpcomingWeeklyClasses;";
        DB::unprepared($deleteProcedure);
    }
}
