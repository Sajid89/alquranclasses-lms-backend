<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ProcedureScTodaysClassesCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spTeachersTodaysClassesCount;";

            $procedure = "
            CREATE PROCEDURE spTeachersTodaysClassesCount(IN teacherCoordinatorId INT, IN todayDate VARCHAR(11)) 

            BEGIN
                select `p`.*, `q`.* from 
                    (
                    select `t`.`id` as `tw_class_id`, 
                    `t`.`class_time`, `t`.`student_id` as `t_student_id`,
                    `t`.`teacher_id` as `t_teacher_id` 
                    from `trial_classes` as `t` 
                    where `t`.`class_time` like(todayDate) 
                    ) as `p` 
                    left join 
                    (
                    select `u`.`timezone` as `teacher_timezone`, `u`.`name` as `teacher_name`, 
                    `s`.`name` as `student_name`, `s`.`timezone` as `student_timezone`,
                    `sc`.`student_id`, `sc`.`teacher_id`, `sc`.`course_level`, 
                    `sc`.`id` as `student_course_id`, 
                    `c`.`title` as `course_title` 
                    from `users` as `u`, `students` as `s`, 
                    `student_courses` as `sc`, 
                    `courses` as `c` 
                    where `s`.`id` = `sc`.`student_id` and 
                    `u`.`id` = `sc`.`teacher_id` and 
                    `c`.`id` = `sc`.`course_id` and 
                    `u`.`coordinated_by` = teacherCoordinatorId 
                    ) as `q` 
                    on `p`.`t_student_id` = `q`.`student_id` and 
                    `p`.`t_teacher_id` = `q`.`teacher_id`;
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
        $deleteProcedure = "DROP procedure IF EXISTS spTeachersTodaysClassesCount;";
        DB::unprepared($deleteProcedure);
    }
}
