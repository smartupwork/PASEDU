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
        Schema::create('pas_partner_type', function (Blueprint $table) {
            $table->id();
            $table->string('partner_type',250);
            $table->string('description',250)->nullable()->default(NULL);
            $table->tinyInteger('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_partner_type');
    }
};
