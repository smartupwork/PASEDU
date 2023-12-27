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
        Schema::create('pas_price_book', function (Blueprint $table) {
            $table->id();
            $table->string('pas_price_book',255);
            $table->string('zoho_id',100);
            $table->text('description')->nullable()->default(NULL);
            $table->string('owner',255)->nullable()->default(NULL);
            $table->bigInteger('owner_id');
            $table->string('status',100)->nullable()->default(NULL);
            $table->integer('program_id')->nullable()->default(NULL)->comment('Last Program ID of Total Sele Program Count from ZOHO');
            $table->integer('created_by')->nullable()->default(NULL);
            $table->date('created_at')->nullable()->default(NULL);
            $table->integer('updated_by')->nullable()->default(NULL);
            $table->date('updated_at')->nullable()->default(NULL);
            $table->date('sync_at')->nullable()->default(NULL);
            $table->tinyInteger('sync_status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_price_book');
    }
};
