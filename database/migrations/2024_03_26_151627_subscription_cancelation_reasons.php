<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SubscriptionCancelationReasons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('subscription_cancelation_reasons')) {
            Schema::create('subscription_cancelation_reasons', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('created_by');
                $table->string('reason', 255);
                $table->softDeletes();
                $table->timestamps();
                $table->foreign('created_by')->references('id')->on('users');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('subscription_cancelation_reasons')) {
            Schema::dropIfExists('subscription_cancelation_reasons');
        }
    }
}
