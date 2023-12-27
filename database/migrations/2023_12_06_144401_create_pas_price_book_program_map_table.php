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
        Schema::create('pas_price_book_program_map', function (Blueprint $table) {
            $table->id();
            $table->integer('price_book_id')->nullable()->default(NULL);
            $table->string('price_book_zoho_id',100)->nullable()->default(NULL);
            $table->integer('program_id');
            $table->string('program_zoho_id',100)->nullable()->default(NULL);
            $table->float('program_list_price')->nullable()->default(NULL);
            $table->date('created_at')->nullable()->default(NULL);
            $table->date('updated_at')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_price_book_program_map');
    }
};
