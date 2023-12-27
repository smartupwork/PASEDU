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
        Schema::create('student_activity_progress', function (Blueprint $table) {
            $table->id();
            $table->enum('activity_type',['activity-progress','activity-log']);
            $table->enum('report_type',['generate-report','schedule-report'])->comment('activity-progress => Activity Progress Report, activity-log => Program Progress Report');
            $table->integer('partner_id');
            $table->integer('enrollment_id')->nullable()->default(NULL);
            $table->enum('schedule_interval',['bi-week','one-month','six-month','one-time'])->nullable()->default(NULL);
            $table->date('scheduled_at')->nullable()->default(NULL);
            $table->enum('fetch_report_type',['all', 'date-range'])->nullable()->default(NULL);
            $table->date('fetch_start_date')->nullable()->default(NULL);
            $table->date('fetch_end_date')->nullable()->default(NULL);
            $table->tinyInteger('is_recurring')->default(0);
            $table->bigInteger('canvas_student_id')->nullable()->default(NULL);
            $table->dateTime('created_at');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable()->default(NULL);
            $table->dateTime('updated_at')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_activity_progress');
    }
};
