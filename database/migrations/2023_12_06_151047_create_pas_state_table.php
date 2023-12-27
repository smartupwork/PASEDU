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
        Schema::create('pas_state', function (Blueprint $table) {
            $table->id();
            $table->integer('country_id');
            $table->string('state_name');
            $table->char('iso2_code',2)->nullable()->default(NULL);
            $table->tinyInteger('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_state');
    }
};
