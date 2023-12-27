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
        Schema::create('pas_user_notification', function (Blueprint $table) {
            $table->id();
            $table->string('relation_table',100);
            $table->integer('foreign_key_id');
            $table->integer('partner_id')->nullable()->default(NULL);
            $table->integer('user_id');
            $table->enum('read_status',['read', 'unread', 'title-read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_user_notification');
    }
};
