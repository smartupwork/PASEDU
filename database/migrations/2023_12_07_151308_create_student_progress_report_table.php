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
        Schema::create('student_progress_report', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('request_type')->default(1)->comment('1 - Institution Request, 2- Marketing Collateral Request');
            $table->integer('partner_id');
            $table->integer('student_id')->nullable()->default(NULL);
            $table->string('is_typical',11)->nullable()->default(NULL);
            $table->string('occurrence',66)->nullable()->default(NULL);
            $table->integer('requested_by')->default(0);
            $table->timestamp('requested_date')->timestamps();
            $table->enum('read_status',['read', 'unread', 'title-read'])->default('unread');
            $table->tinyInteger('status')->default(1);
            $table->integer('updated_by')->nullable()->default(NULL);
            $table->dateTime('updated_at')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_progress_report');
    }
};
