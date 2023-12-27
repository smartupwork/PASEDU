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
        Schema::create('pas_users_access', function (Blueprint $table) {
            $table->integer('user_id')->primary();
            $table->string('feature', 100)->nullable()->default(Null);
            $table->string('parent_menu', 100)->nullable()->default(Null);
            $table->tinyInteger('can_view')->nullable()->default(Null);
            $table->tinyInteger('can_download')->nullable()->default(Null);
            $table->tinyInteger('can_add')->nullable()->default(Null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_users_access');
    }
};
