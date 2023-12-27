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
        Schema::create('pas_marketing_template', function (Blueprint $table) {
            $table->id();
            $table->integer('category_id');
            $table->string('mime_type',100)->nullable()->default(NULL);
            $table->string('media_file',200);
            $table->string('group_type',100)->nullable()->default(NULL);
            $table->date('created_at')->nullable()->default(NULL);
            $table->integer('created_by')->nullable()->default(NULL);
            $table->date('updated_at')->nullable()->default(NULL);
            $table->integer('updated_by')->nullable()->default(NULL);
            $table->string('video_file',200)->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_marketing_template');
    }
};
