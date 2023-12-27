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
        Schema::create('pas_timezone', function (Blueprint $table) {
            $table->id();
            $table->char('country_code');
            $table->string('coordinates',15);
            $table->string('timezone',32);
            $table->string('comments',85);
            $table->string('utc_offset',8);
            $table->string('utc_dst_offset',8);
            $table->string('notes',79)->nullable()->default(NULL);
            $table->integer('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_timezone');
    }
};
