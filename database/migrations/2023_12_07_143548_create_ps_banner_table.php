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
        Schema::create('ps_banner', function (Blueprint $table) {
            $table->id();
            $table->integer('partner_id')->nullable()->default(NULL);
            $table->string('media_file',100)->collation('latin1_swedish_ci');
            $table->string('link',1000)->collation('latin1_swedish_ci');
            $table->string('title',100)->collation('latin1_swedish_ci')->nullable()->default(NULL);
            $table->string('description',100)->collation('latin1_swedish_ci')->nullable()->default(NULL);
            $table->tinyInteger('open_new_tab')->default(0);
            $table->tinyInteger('position')->nullable()->default(NULL);
            $table->tinyInteger('is_active')->default(1);
            $table->datetime('created_at');
            $table->integer('created_by');
            $table->timestamp('updated_at')->nullable()->default(NULL);
            $table->integer('updated_by')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ps_banner');
    }
};
