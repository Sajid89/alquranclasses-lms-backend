<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ProcedureGetStudentsNotificationsCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spAllStudentsNotificationsCount;";

            $procedure = "
            CREATE PROCEDURE spAllStudentsNotificationsCount(IN teacherCoordinatorId INT) 

            BEGIN
                select count(*) as `count` from 
                `users` as `u`, `notifications` as `n`, 
                `students` as `s`, `student_courses` as `sc` 
                where `s`.`id` = `sc`.`student_id` and 
                `sc`.`teacher_id` = `u`.`id` and 
                `s`.`id` = `n`.`student_id` and 
                `n`.`type` <> 'teacher' and 
                `u`.`coordinated_by` = teacherCoordinatorId;
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
        $deleteProcedure = "DROP procedure IF EXISTS spAllStudentsNotificationsCount;";
        DB::unprepared($deleteProcedure);
    }
}
