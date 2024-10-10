<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class AlterTransactionHistoryProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spCustomerTransactionHistory;";

            $procedure = "
            CREATE PROCEDURE spCustomerTransactionHistory(IN customerId int) 

            BEGIN

            select `inv`.`id`, `inv`.`amount`, `inv`.`created_at`, `inv`.`invoice_date`, 
            `std`.`gender`, `std`.`name`, `std`.`profile_photo_url`, 
            `std`.`subscription_status`, `std`.`timezone`, 
            `std`.`vacation_mode`, `sub`.`deleted_at`, 
            `sub`.`planID`, `stc`.`course_level`, 
            `c`.`title`, `c`.`is_custom`, `c`.`description`, 
            `u`.`name` as `customer_name`, `u`.`email`,
            `u`.`profile_photo_path` 
             from `invoices` as `inv`, `subscriptions` as `sub`, 
            `students` as `std`, `student_courses` as `stc`, 
            `courses` as `c`, `users` as `u` 
            where `u`.`id` = customerId and 
            `std`.`user_id` = `u`.`id` and 
            `std`.`id` = `stc`.`student_id` and 
            `c`.`id` = `stc`.`course_id` and 
            `sub`.`user_id` = `u`.`id` and 
            `sub`.`student_id` = `std`.`id` and 
            `sub`.`id` = `inv`.`subscription_id` 
            order by `inv`.`created_at` asc, `inv`.`invoice_date` asc, 
            `std`.`id` asc;

            END";

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
        $deleteProcedure = "DROP procedure IF EXISTS spCustomerTransactionHistory;";
        
        DB::unprepared($deleteProcedure);
    }
}
