<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class UpcomingTrialClassesProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spUpcomingTrialClasses;";

            $procedure = "
            CREATE PROCEDURE spUpcomingTrialClasses(IN studentId INT, IN classTime DATETIME, IN page INT, IN recordsLimit INT) 
        BEGIN
            SELECT `c`.`title` AS `course_title`, `s`.`id` as `student_id`, `s`.`name` AS `student_name`, 
            `s`.`timezone` AS `student_timezone`, 
            `u`.`id` as `teacher_id`, `u`.`name` AS `teacher_name`, `tc`.`id` AS `tw_class_id`, 
            `tc`.`status`, `tc`.`class_time` 
            FROM `trial_classes` AS `tc`, `users` AS `u`, 
            `student_courses` AS `sc`, `students` AS `s`, 
            `courses` AS `c` 
            WHERE `s`.`id` = studentId AND 
            `s`.`id` = `sc`.`student_id` AND 
            `sc`.`course_id` = `c`.`id` AND 
            `sc`.`teacher_id` = `u`.`id` AND 
            `tc`.`student_course_id` = `sc`.`id` AND 
            `tc`.`class_time` >= classTime and 
            `tc`.`deleted_at` is null 
            ORDER BY `tc`.`class_time` ASC 
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
        $deleteProcedure = "DROP procedure IF EXISTS spUpcomingTrialClasses;";
        DB::unprepared($deleteProcedure);
    }
}
