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
        Schema::create('pas_login_activity', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->dateTime('logged_in_at');
            $table->dateTime('logged_out_at')->nullable()->default(NULL);
            $table->dateTime('last_activity_time');
            $table->string('ip_address', 50);
            $table->string('session_id', 50);
            $table->string('user_agent', 50)->nullable()->default(Null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_login_activity');
    }
};
