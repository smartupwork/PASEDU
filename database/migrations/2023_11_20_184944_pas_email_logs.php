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
        Schema::create('pas_email_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('from_email',128)->nullable()->default(NULL);
            $table->string('to_email',200)->nullable()->default(NULL);
            $table->tinyText('cc_email')->nullable()->default(NULL);
            $table->tinyText('bcc_email')->nullable()->default(NULL);
            $table->string('subject',200)->nullable()->default(NULL);
            $table->text('message')->nullable()->default(NULL);
            $table->text('attachments')->nullable()->default(NULL);
            $table->integer('added_by')->nullable()->default(NULL);
            $table->timestamp('added_date')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_email_logs');
    }
};
