<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStudentCourseIdToRoutineClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('routine_classes', function (Blueprint $table) {
            $table->unsignedBigInteger('student_course_id')->after('slot_id');
            $table->foreign('student_course_id')->references('id')->on('student_courses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('routine_classes', function (Blueprint $table) {
            //
        });
    }
}
