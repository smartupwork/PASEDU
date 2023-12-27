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
        Schema::create('pas_country', function (Blueprint $table) {
            $table->id();
            $table->string('country_name',150);
            $table->char('iso2_code',2)->nullable()->default(NULL);
            $table->char('iso3_code',3)->nullable()->default(NULL);
            $table->tinyInteger('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_country');
    }
};
