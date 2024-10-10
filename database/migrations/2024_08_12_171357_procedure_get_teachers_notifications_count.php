<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ProcedureGetTeachersNotificationsCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spAllTeachersNotificationsCount;";

            $procedure = "
            CREATE PROCEDURE spAllTeachersNotificationsCount(IN teacherCoordinatorId INT) 

            BEGIN
                select count(*) as `count` from 
                `users` as `u`, `notifications` as `n` 
                where `u`.`id` = `n`.`user_id` and 
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
        $deleteProcedure = "DROP procedure IF EXISTS spAllTeachersNotificationsCount;";
        DB::unprepared($deleteProcedure);
    }
}
