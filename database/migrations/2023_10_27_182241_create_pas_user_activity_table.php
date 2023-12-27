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
        Schema::create('pas_user_activity', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable()->default(Null);
            $table->enum('action', ['create', 'update', 'delete', 'fetch']);
            $table->enum('action_via', ['web','cron'])->default('web');
            $table->string('url',1000)->nullable()->default(NULL);
            $table->string('method',20)->nullable()->default(NULL);
            $table->longText('old_data')->nullable()->default(NULL);
            $table->longText('new_data')->nullable()->default(NULL);
            $table->string('ref_ids',500)->nullable()->default(NULL);
            $table->string('ip_address',50)->nullable()->default(NULL);
            $table->string('session_id',150);
            $table->string('user_agent',500)->nullable()->default(NULL);
            $table->date('created_at');
            $table->integer('created_by')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_user_activity');
    }
};
