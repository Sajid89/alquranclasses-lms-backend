<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class SpEnrolmentPlan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spEnrollmentPlan;";

            $procedure = "
            CREATE PROCEDURE spEnrollmentPlan(IN `customerId` INT) 

            BEGIN

            select `s`.`id`, `s`.`name`, `s`.`gender`, 
            `s`.`is_subscribed`, `s`.`profile_photo_url`,
            `sc`.`course_level`, `sc`.`subscription_id`, 
            `c`.`title` as `course_title`, 
            `sp`.`title` as `subscription_plan_title`,
            `sub`.`price`, `sub`.`status` as `subscription_status`, 
            `sub`.`start_at`, 
            (case when (`sub`.`status` = 'end' or `sub`.`status` = 'cancelled') then `sub`.`ends_at` else null end) as `ends_at`
            from `students` as `s`, `student_courses` as `sc`,
            `courses` as `c`, `subscription_plans` as `sp`, 
            `subscriptions` as `sub`
            where `s`.`user_id` = customerId and 
            `s`.`id` = `sc`.`student_id` and 
            `sc`.`course_id` = `c`.`id` and 
            `sub`.`planID` = `sp`.`stripe_plan_id` and 
            `s`.`id` = `sub`.`student_id` and 
            `sc`.`subscription_id` = `sub`.`id`
            order by `s`.`id` asc;
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
        $deleteProcedure = "DROP procedure IF EXISTS spEnrollmentPlan;";
        DB::unprepared($deleteProcedure);
    }
}
