<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ProcedureGetTeachersNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spAllTeachersNotifications;";

            $procedure = "
            CREATE PROCEDURE spAllTeachersNotifications(IN teacherCoordinatorId INT, IN page INT, IN recordsLimit INT) 

            BEGIN
                select `n`.`id`, `u`.`name` as `teacher_name`, 
                `u`.`timezone`, `u`.`email`, `u`.`phone`, 
                `n`.`created_at`, `n`.`message`, `n`.`type` 
                from `users` as `u`, `notifications` as `n` 
                where `u`.`id` = `n`.`user_id` and 
                `u`.`coordinated_by` = teacherCoordinatorId and 
                `type` <> 'student' 
                order by `n`.`id` asc,
                `u`.`id` asc 
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
        $deleteProcedure = "DROP procedure IF EXISTS spAllTeachersNotifications;";
        DB::unprepared($deleteProcedure);
    }
}
