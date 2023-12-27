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
        Schema::create('pas_owner', function (Blueprint $table) {
            $table->id();
            $table->string('zoho_id',50)->nullable()->default(NULL);
            $table->string('full_name',200);
            $table->string('email',200);
            $table->string('status',50)->nullable()->default(NULL);
            $table->string('role',50)->nullable()->default(NULL);
            $table->string('role_zoho_id',50)->nullable()->default(NULL);
            $table->date('created_at');
            $table->date('updated_at')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_owner');
    }
};
