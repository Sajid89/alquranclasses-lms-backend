<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ProcTeacherCurrentStudentsCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spTeacherActiveStudentsCount;";

            $procedure = "
            CREATE PROCEDURE spTeacherActiveStudentsCount(IN teacherId INT) 

            BEGIN

                 select count(*) as `count`
                    from `students` as `s`, `courses` as `c`, 
                    `student_courses` as `sc`, `subscriptions` as `sub` 
                    where `sc`.`teacher_id` = teacherId and 
                    `s`.`id` = `sc`.`student_id` and 
                    `c`.`id` = `sc`.`course_id` and 
                    `s`.`id` = `sub`.`student_id` and 
                    `sub`.`status` = 'active';
            
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
        $deleteProcedure = "DROP procedure IF EXISTS spTeacherActiveStudentsCount;";
        DB::unprepared($deleteProcedure);
    }
}
