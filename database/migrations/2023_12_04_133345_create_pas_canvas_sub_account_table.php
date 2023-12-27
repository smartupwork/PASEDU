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
        Schema::create('pas_canvas_sub_account', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_account_id')->unsigned();
            $table->integer('sub_account_id')->unsigned();
            $table->integer('root_account_id')->unsigned();
            $table->string('name',100);
            $table->string('work_status',50);
            $table->string('uuid',100);
            $table->string('default_time_zone',150);
            $table->timestamp('created_at')->nullable()->default(NULL);
            $table->timestamp('updated_at')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_canvas_sub_account');
    }
};
