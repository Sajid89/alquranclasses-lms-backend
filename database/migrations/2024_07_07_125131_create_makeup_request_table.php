<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMakeupRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('makeup_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_course_id');
            $table->string('class_type', 50);
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('availability_slot_id');
            $table->dateTime('makeup_date_time');
            $table->dateTime('class_old_date_time');
            $table->string('status', 50);
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->tinyInteger('is_teacher')->default(0);
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
        Schema::dropIfExists('makeup_requests');
    }
}
