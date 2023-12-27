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
        Schema::create('pas_zoho_token', function (Blueprint $table) {
            $table->id();
            $table->string('org_id',255)->nullable()->default(NULL);
            $table->string('access_token',255)->nullable()->default(NULL);
            $table->string('refresh_token',255)->nullable()->default(NULL);
            $table->string('api_domain',255)->default('https://www.zohoapis.com');
            $table->date('expires_at');
            $table->date('created_at');
            $table->date('updated_at')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_zoho_token');
    }
};
