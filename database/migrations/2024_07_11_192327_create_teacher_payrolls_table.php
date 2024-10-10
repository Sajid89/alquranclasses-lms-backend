<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeacherPayrollsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        Schema::create('teacher_payrolls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->decimal('total_trial_duration', 6, 2);
            $table->decimal('total_regular_duration', 6, 2);
            $table->date('from_date');
            $table->date('to_date');
            $table->integer('trial_classes_count');
            $table->integer('regular_classes_count');
            $table->decimal('team_bonus', 6, 2)->default(0);
            $table->decimal('customer_bonus', 6, 2)->default(0);
            $table->decimal('allowance', 6, 2)->default(0);
            $table->decimal('late_joining_deduction', 6, 2)->default(0);
            $table->decimal('loan_deduction', 6, 2)->default(0);
            $table->string('salary_status', 15)->default('pending');
            $table->decimal('total_regular_amount', 6, 2);
            $table->decimal('total_trial_amount', 6, 2);
            $table->decimal('net_to_pay', 6, 2);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->foreign('teacher_id')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teacher_payrolls');
    }
}
