<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ProcStudentActivityCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spStudentActivitiesCount;";

            $procedure = "
            CREATE PROCEDURE spStudentActivitiesCount(IN studentId INT) 

            BEGIN
                 select count(*) as `count` 
                    from `student_courses` as `sc`, 
                    `student_course_activity` as `sca` 
                    where `sc`.`student_id` = studentId and 
                    `sc`.`id` = `sca`.`student_course_id`;
            
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
        $deleteProcedure = "DROP procedure IF EXISTS spStudentActivitiesCount;";
        DB::unprepared($deleteProcedure);
    }
}
