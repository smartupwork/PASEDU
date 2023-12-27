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
        Schema::create('email_queue', function (Blueprint $table) {
            $table->id();
            $table->integer('partner_id')->nullable()->default(NULL);
            $table->integer('enrollment_id')->nullable()->default(NULL);
            $table->string('from_email',128)->nullable()->default(NULL);
            $table->string('to_email',200)->nullable()->default(NULL);
            $table->text('cc_email')->nullable()->default(NULL);
            $table->string('subject',200)->nullable()->default(NULL);
            $table->text('message')->nullable()->default(NULL);
            $table->text('attachments')->nullable()->default(NULL);
            $table->tinyText('is_sent')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_queue');
    }
};
