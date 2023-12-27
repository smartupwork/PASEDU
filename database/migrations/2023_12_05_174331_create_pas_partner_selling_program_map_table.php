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
        Schema::create('pas_partner_selling_program_map', function (Blueprint $table) {
            $table->id();
            $table->string('partner_zoho_id',100)->nullable()->default(NULL);
            $table->integer('program_id');
            $table->string('program_zoho_id',100)->nullable()->default(NULL);
            $table->integer('selling_count')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_partner_selling_program_map');
    }
};
