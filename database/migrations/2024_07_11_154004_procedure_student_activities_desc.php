<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ProcedureStudentActivitiesDesc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spStudentActivities;";

            $procedure = "
            CREATE PROCEDURE spStudentActivities(IN studentId INT, IN page INT, IN recordsLimit INT) 

            BEGIN
                select `s`.`name`  as `student_name`, `s`.`timezone` as `student_timezone`,
                `u`.`timezone` as `teacher_timezone`, 
                `c`.`title` as `course_title`, `sc`.`student_id`, 
                `sc`.`course_id`, `sc`.`course_level`, `sc`.`teacher_id`, 
                `ca`.`activity_type`, `ca`.`created_at`,
                `ca`.`description`, `ca`.`file_name`, 
                `ca`.`file_size`, `ca`.`id` 
                from `students` as `s`, `courses` as `c`, 
                `student_courses` as `sc`, `student_course_activity` as `ca`,
                `users` as `u` 
                where `s`.`id` = studentId and 
                `s`.`id` = `sc`.`student_id` and
                `sc`.`teacher_id` = `u`.`id` and 
                `c`.`id` = `sc`.`course_id` and 
                `sc`.`id` = `ca`.`student_course_id` 
                order by `s`.`id` asc, `c`.`id` asc, 
                `ca`.`id` desc  
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
        $deleteProcedure = "DROP procedure IF EXISTS spStudentActivities;";
        DB::unprepared($deleteProcedure);
    }
}
