<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegularClassRates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //id, teacher_id, rate, created_by, created_at, deleted_at

        Schema::create('regular_class_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->decimal('rate', 5, 2);
            $table->unsignedBigInteger('created_by');
            $table->dateTime('created_at');
            $table->dateTime('deleted_at')->nullable();
            $table->foreign('teacher_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('regular_class_rates');
    }
}
