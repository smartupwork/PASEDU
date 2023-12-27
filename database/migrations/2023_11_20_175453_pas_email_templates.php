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
        Schema::create('pas_email_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('from_email',200);
            $table->string('from_name',255);
            $table->string('type',100)->nullable()->default(NULL);
            $table->string('subject',200)->nullable()->default(NULL);
            $table->text('message')->nullable()->default(NULL);
            $table->timestamp('added_date')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_email_templates');
    }
};
