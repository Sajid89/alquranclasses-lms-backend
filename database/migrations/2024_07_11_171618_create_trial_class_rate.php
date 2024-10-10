<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrialClassRate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
id, rate, created_by, created_at, deleted_at
        */
        Schema::create('trial_class_rates', function (Blueprint $table) {
            $table->id();
            $table->decimal('rate', 5, 2);
            $table->unsignedBigInteger('created_by');
            $table->dateTime('created_at');
            $table->dateTime('deleted_at')->nullable();
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trial_class_rates');
    }
}
