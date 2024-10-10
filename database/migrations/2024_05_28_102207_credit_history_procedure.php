<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CreditHistoryProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spStudentAvailableCredits;";

            $procedure = "
            CREATE PROCEDURE spStudentAvailableCredits(IN studentId INT, IN currentTimeUTC datetime) 
        BEGIN
            select `h`.`class_id`, `h`.`class_type` 
            from `student_courses` as `sc`, `credit_history` as `h` 
            where `sc`.`student_id` = studentId and 
            `sc`.`id` = `h`.`student_course_id` and 
            `h`.`deleted_at` is null and 
            `h`.`expired_at` > currentTimeUTC 
            order by `h`.`class_id` asc;
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
        $deleteProcedure = "DROP procedure IF EXISTS spStudentAvailableCredits;";
        DB::unprepared($deleteProcedure);
    }
}
