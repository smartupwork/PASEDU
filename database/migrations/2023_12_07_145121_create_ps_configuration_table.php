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
        Schema::create('ps_configuration', function (Blueprint $table) {
            $table->id();
            $table->integer('partner_id');
            $table->string('type',150)->collation('latin1_swedish_ci');
            $table->text('content')->collation('latin1_swedish_ci');
            $table->tinyInteger('is_active')->default(0);
            $table->dateTime('created_at');
            $table->integer('created_by');
            $table->dateTime('updated_at')->nullable()->default(NULL);
            $table->integer('updated_by')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ps_configuration');
    }
};
