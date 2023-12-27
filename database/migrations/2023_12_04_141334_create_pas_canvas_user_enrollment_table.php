<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pas_canvas_user_enrollment', function (Blueprint $table) {
            $table->id();
            $table->integer('canvas_enrollment_id')->nullable()->default(NULL);
            $table->integer('user_id');
            $table->integer('course_id');
            $table->integer('course_section_id');
            $table->date('enroll_start_date')->nullable()->default(NULL);
            $table->date('enroll_end_date')->nullable()->default(NULL);
            $table->string('total_activity_sec',50)->nullable()->default(NULL);
            $table->string('today_activity_sec',50)->nullable()->default(NULL);
            $table->timestamp('last_activity_at')->nullable()->default(NULL);
            $table->timestamp('login_time')->nullable()->default(NULL);
            $table->timestamp('logout_time')->nullable()->default(NULL);
            $table->string('ip_address',200)->nullable()->default(NULL);
            $table->date('report_at');
            $table->timestamp('created_at')->nullable()->default(NULL);
            $table->timestamp('updated_at')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_canvas_user_enrollment');
    }
};
