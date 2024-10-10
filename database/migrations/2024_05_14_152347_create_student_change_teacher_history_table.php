<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentChangeTeacherHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_change_teacher_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_course_id');
            $table->unsignedBigInteger('change_teacher_reason_id');
            $table->timestamps();
            $table->foreign('student_course_id')->references('id')->on('student_courses');
            $table->foreign('change_teacher_reason_id')->references('id')->on('change_teacher_reasons');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_change_teacher_history');
    }
}