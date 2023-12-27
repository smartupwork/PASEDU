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
        Schema::create('pas_roles_access', function (Blueprint $table) {
            $table->id();
            $table->integer('role_id')->nullable()->default(0);
            $table->enum('access_level', ['full-access', 'account-manager', 'account-support','registration-account-partner'])->nullable()->default(NULL);
            $table->string('feature',100)->nullable()->default(NULL);
            $table->string('parent_menu',100)->nullable()->default(NULL);
            $table->tinyInteger('can_view')->nullable()->default(0);
            $table->tinyInteger('can_download')->nullable()->default(0);
            $table->tinyInteger('can_add')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_roles_access');
    }
};
