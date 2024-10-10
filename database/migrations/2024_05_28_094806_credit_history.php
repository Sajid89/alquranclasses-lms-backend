<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreditHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_course_id');
            $table->string('class_type');
            $table->unsignedBigInteger('class_id');
            $table->dateTime('expired_at');
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();
            $table->foreign('student_course_id')->references('id')->on('student_courses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_history');
    }
}
