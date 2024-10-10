<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class StudentUpcomingClasses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spCourseActivity;";

            $procedure = "
            CREATE PROCEDURE spCourseActivity(IN studentId INT, IN courseId INT, IN page INT, IN recordsLimit INT) 
        BEGIN
            select `s`.`id` as `student_id`, `s`.`name` as `student_name`, `s`.`timezone` as `student_time_zone`,
            `u`.`id` as `teacher_id`, `u`.`name` as `teacher_name`, `u`.`timezone` as `teacher_time_zone`,
            `act`.`id` as `activity_id`, `act`.`activity_type`, `act`.`description`, 
            `act`.`created_at`, `act`.`file_size`, 
            `c`.`title` as `course_name` 
            from `students` as `s`, 
            `courses` as `c`, `student_courses` as `sc`, 
            `users` as `u`, `student_course_activity` as `act` 
            where `s`.`id` = studentId and 
            `c`.`id` = courseId and 
            `s`.`id` = `sc`.`student_id` and 
            `sc`.`course_id` = `c`.`id` and 
            `sc`.`teacher_id` = `u`.`id` and 
            `act`.`student_course_id` = `sc`.`id` 
            ORDER BY `act`.`id` ASC 
            LIMIT page, recordsLimit;
        END
        ";
            DB::unprepared($deleteProcedure);
            DB::unprepared($procedure);
            
        } catch (QueryException $e) {
            dump($e->getMessage());
            dump($e->getTraceAsString());
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
        $deleteProcedure = "DROP procedure IF EXISTS spCourseActivity;";
        DB::unprepared($deleteProcedure);
    }
}
