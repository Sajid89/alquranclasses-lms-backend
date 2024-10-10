<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProcedureAllAvailableCouponsExceptCancelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $deleteProcedure = "DROP procedure IF EXISTS spCouponsExceptCancellation;";

            $procedure = "
            CREATE PROCEDURE spCouponsExceptCancellation(IN currentDate date) 

            BEGIN

            select `id`, `code`, `type`, `value`, `usage_limit`, `used` 
            from `coupons` 
            where `deleted_at` is null and 
            `expires_at` >= currentDate and 
            (`used` < `usage_limit` or `usage_limit` is null) and 
            `for_subscription_cancellation` = 0;

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
        $deleteProcedure = "DROP procedure IF EXISTS spCouponsExceptCancellation;";
        
        DB::unprepared($deleteProcedure);
    }
}
