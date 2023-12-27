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
        Schema::create('pas_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('role_name',100);
            $table->string('description',255)->default(NULL);
            $table->enum('role_type', ['partner', 'user']);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_roles');
    }
};
