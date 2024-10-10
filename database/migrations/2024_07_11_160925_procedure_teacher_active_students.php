<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ProcedureTeacherActiveStudents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spTeacherActiveStudents;";

            $procedure = "
            CREATE PROCEDURE spTeacherActiveStudents(IN teacherId INT, IN page INT, IN recordsLimit INT) 

            BEGIN
                 select `s`.`name`  as `student_name`, 
                    `c`.`title` as `course_title`, `sc`.`student_id`, 
                    `sc`.`course_id`, `sc`.`course_level`, `sc`.`teacher_id`, 
                    `sub`.`status`, `sub`.`id` as `subscription_id` 
                    from `students` as `s`, `courses` as `c`, 
                    `student_courses` as `sc`, `subscriptions` as `sub` 
                    where `sc`.`teacher_id` = teacherId and 
                    `s`.`id` = `sc`.`student_id` and 
                    `c`.`id` = `sc`.`course_id` and 
                    `s`.`id` = `sub`.`student_id` and 
                    `sub`.`payment_status` = 'succeeded' 
                    order by `s`.`id` asc, `c`.`id` asc, `sub`.`id` asc 
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
        $deleteProcedure = "DROP procedure IF EXISTS spTeacherActiveStudents;";
        DB::unprepared($deleteProcedure);
    }
}
