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
        Schema::create('pas_marketing_category', function (Blueprint $table) {
            $table->id();
            $table->enum('category_type',['course-marketing','funding-sources','social-media'])->nullable()->default(NULL);
            $table->string('category_name',200);
            $table->string('slug',255)->nullable()->default(NULL);
            $table->integer('parent_id')->nullable()->default(NULL);
            $table->string('group_type',100)->nullable()->default(NULL);
            $table->string('media_name',200);
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
        Schema::dropIfExists('pas_marketing_category');
    }
};
