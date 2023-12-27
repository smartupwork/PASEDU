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
        Schema::create('pas_imported_files', function (Blueprint $table) {
            $table->id();
            $table->string('file',255)->nullable()->default(NULL);
            $table->date('date')->nullable()->default(NULL);
            $table->integer('partner_id');
            $table->integer('added_by');
            $table->integer('records_imported')->default(0);
            $table->integer('records_imported_warning')->default(0);
            $table->integer('records_skiped')->default(0);
            $table->string('file_size',111)->nullable()->default(NULL);
            $table->string('processing_time',111)->nullable()->default(NULL);
            $table->text('warning_rows')->nullable()->default(NULL);
            $table->text('skipped_rows')->nullable()->default(NULL);
            $table->timestamp('added_date')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_imported_files');
    }
};
