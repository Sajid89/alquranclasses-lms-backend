<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ProcedureGetAllStudentsNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spAllStudentsNotifications;";

            $procedure = "
            CREATE PROCEDURE spAllStudentsNotifications(IN teacherCoordinatorId INT, IN page INT, IN recordsLimit INT) 

            BEGIN
                select `n`.`id`, `n`.`created_at`, `n`.`message`, 
                `n`.`type`, `u`.`id` as `user_id`, `u`.`name` as `teacher_name`,
                `s`.`name` as `student_name`, `sc`.`course_level`,
                `sc`.`student_id` 
                from `users` as `u`, `notifications` as `n`, 
                `students` as `s`, `student_courses` as `sc` 
                where `s`.`id` = `sc`.`student_id` and 
                `sc`.`teacher_id` = `u`.`id` and 
                `s`.`id` = `n`.`student_id` and 
                `n`.`type` <> 'teacher' and 
                `u`.`coordinated_by` = 4 
                order by `n`.`id` asc, `s`.`id` asc 
                limit page, recordsLimit;
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
        $deleteProcedure = "DROP procedure IF EXISTS spAllStudentsNotifications;";
        DB::unprepared($deleteProcedure);
    }
}
