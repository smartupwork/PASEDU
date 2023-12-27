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
        Schema::create('pas_canvas_course', function (Blueprint $table) {
            $table->id();
            $table->integer('pas_sub_account_id')->nullable()->default(NULL);
            $table->integer('canvas_course_id');
            $table->integer('account_id');
            $table->integer('root_account_id');
            $table->string('name',100);
            $table->string('work_status',50);
            $table->string('uuid',100);
            $table->timestamp('start_at')->useCurrent()->nullable()->default(NULL);
            $table->timestamp('end_at')->useCurrent()->nullable()->default(NULL);
            $table->string('course_code',50);
            $table->string('license',20)->nullable()->default(NULL);
            $table->tinyInteger('is_public')->nullable()->default(NULL);
            $table->string('time_zone',150)->nullable()->default(NULL);
            $table->text('migration_detail')->nullable()->default(NULL);
            $table->timestamp('created_at')->useCurrent()->nullable()->default(NULL);
            $table->timestamp('updated_at')->useCurrent()->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_canvas_course');
    }
};
