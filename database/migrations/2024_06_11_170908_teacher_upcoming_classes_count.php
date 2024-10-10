<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TeacherUpcomingClassesCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spTeacherUpcomingClassesCount;";
    
                $procedure = "
                CREATE PROCEDURE spTeacherUpcomingClassesCount(IN teacherId INT, IN currentDateTime DATETIME) 
    
                BEGIN
    
                SELECT 
                    (
                        SELECT COUNT(*) FROM `trial_classes` WHERE `teacher_id` = teacherId and 
                        `class_time` >= currentDateTime and 
                        `deleted_at` IS NULL 
                        ) 
                        +
                    (
                        SELECT COUNT(*) FROM `weekly_classes` WHERE `teacher_id` = teacherId and 
                        `class_time` >= currentDateTime and 
                        `deleted_at` IS NULL 
                        ) AS `count`; 
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
        $deleteProcedure = "DROP procedure IF EXISTS spTeacherUpcomingClassesCount;";
        DB::unprepared($deleteProcedure);
    }
}
