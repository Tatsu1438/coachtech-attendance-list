<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBreakNumberToAttendanceRequestBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_request_breaks', function (Blueprint $table) {
            $table->integer('break_number')->after('attendance_request_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_request_breaks', function (Blueprint $table) {
            $table->dropColumn('break_number');
        });
    }
}
