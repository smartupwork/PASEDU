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
        Schema::create('pas_partner_inquiry', function (Blueprint $table) {
            $table->id();
            $table->string('request_type',222)->nullable()->default(NULL);
            $table->string('request_reason',222)->nullable()->default(NULL);
            $table->text('message')->nullable()->default(NULL);
            $table->integer('added_by')->nullable()->default(NULL);
            $table->date('added_date')->nullable()->default(NULL);
            $table->string('status',55)->nullable()->default(NULL);
            $table->integer('partner_id')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_partner_inquiry');
    }
};
