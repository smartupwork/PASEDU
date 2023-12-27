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
        Schema::create('pas_marketing', function (Blueprint $table) {
            $table->id();
            $table->enum('marketing_type',['news', 'announcements', 'updates']);
            $table->string('slug',20)->nullable()->default(NULL);
            $table->string('title',255);
            $table->text('description');
            $table->tinyInteger('status')->default(1);
            $table->integer('created_by')->nullable()->default(NULL);
            $table->date('created_at')->nullable()->default(NULL);
            $table->integer('updated_by')->nullable()->default(NULL);
            $table->date('updated_at')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_marketing');
    }
};
