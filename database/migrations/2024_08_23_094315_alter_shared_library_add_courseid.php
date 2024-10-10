<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSharedLibraryAddCourseid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shared_libraries', function (Blueprint $table) {
            $table->unsignedBigInteger('course_id')->nullable()->after('is_locked');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shared_libraries', function (Blueprint $table) {
            $table->dropColumn('course_id');
        });
    }
}
