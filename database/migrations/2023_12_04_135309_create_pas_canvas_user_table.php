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
        Schema::create('pas_canvas_user', function (Blueprint $table) {
            $table->id();
            $table->integer('canvas_user_id');
            $table->string('name',200);
            $table->string('role',100)->nullable()->default(NULL);
            $table->string('sortable_name',200);
            $table->string('short_name',100);
            $table->string('sis_user_id',50)->nullable()->default(NULL);
            $table->string('integration_id',20)->nullable()->default(NULL);
            $table->string('sis_import_id',100)->nullable()->default(NULL);
            $table->string('login_id',200)->nullable()->default(NULL);
            $table->timestamp('created_at')->nullable()->default(NULL);
            $table->timestamp('updated_at')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_canvas_user');
    }
};
