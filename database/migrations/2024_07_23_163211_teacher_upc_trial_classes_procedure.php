<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
class TeacherUpcTrialClassesProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spTeacherUpcomingTrialClasses;";

            $procedure = "
            CREATE PROCEDURE spTeacherUpcomingTrialClasses(IN teacherId INT, IN classTime DATETIME, IN page INT, IN recordsLimit INT) 

            BEGIN

                select `c`.`id` as `course_id`, `c`.`title` as `course_title`, `s`.`id` as `student_id`, `s`.`name` as `student_name`, 
                `s`.`timezone` as `student_timezone`, 
                `u`.`id` as `teacher_id`, `u`.`name` as `teacher_name`, 
                `u`.`timezone` as `teacher_timezone`, 
                `tc`.`id` as `tw_class_id`, 
                `tc`.`status`, `tc`.`class_time` 
                from `trial_classes` as `tc`, `users` as `u`, 
                `student_courses` as `sc`, `students` as `s`, 
                `courses` as `c` 
                where `u`.`id` = teacherId and 
                `sc`.`teacher_id` = teacherId and 
                `s`.`id` = `sc`.`student_id` and 
                `sc`.`course_id` = `c`.`id` and 
                `sc`.`teacher_id` = `u`.`id` and 
                `tc`.`student_course_id` = `sc`.`id` and 
                `tc`.`class_time` >= classTime and 
                `tc`.`deleted_at` is null 
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
        $deleteProcedure = "DROP procedure IF EXISTS spTeacherUpcomingTrialClasses;";
        DB::unprepared($deleteProcedure);
    }
}
