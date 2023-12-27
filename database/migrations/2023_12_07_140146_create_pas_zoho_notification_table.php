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
        Schema::create('pas_zoho_notification', function (Blueprint $table) {
            $table->id();
            $table->string('channel_id',100)->collation('latin1_swedish_ci');
            $table->tinyText('ids')->nullable()->default(NULL)->collation('latin1_swedish_ci');
            $table->string('module',200)->collation('latin1_swedish_ci');
            $table->string('resource_uri',255)->collation('latin1_swedish_ci');
            $table->string('operation',100)->collation('latin1_swedish_ci');
            $table->string('token',255)->nullable()->default(NULL)->collation('latin1_swedish_ci');
            $table->text('response')->nullable()->default(NULL);
            $table->tinyInteger('is_executed')->default(0);
            $table->date('created_at')->nullable()->default(NULL);
            $table->date('updated_at')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_zoho_notification');
    }
};
