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
        Schema::create('listing_setting', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('partner_id')->nullable()->default(NULL);
            $table->string('module',100);
            $table->text('menu')->nullable()->default(NULL);
            $table->timestamp('created_at')->useCurrent()->nullable()->default(NULL);
            $table->timestamp('updated_at')->useCurrent()->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_setting');
    }
};
