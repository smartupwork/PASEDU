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
        Schema::create('pas_schedule', function (Blueprint $table) {
            $table->id();
            $table->string('zoho_id',30)->nullable()->default(NULL);
            $table->integer('partner_id')->nullable()->default(NULL);
            $table->string('partner_zoho_id',30)->nullable()->default(NULL);
            $table->string('contact_zoho_id',50)->nullable()->default(NULL);
            $table->integer('contact_id')->nullable()->default(NULL);
            $table->binary('deal_name',100)->nullable()->default(NULL);
            $table->binary('email',100)->nullable()->default(NULL);
            $table->binary('phone',100)->nullable()->default(NULL);
            $table->string('deal_name_old',200)->nullable()->default(NULL);
            $table->string('stage',200)->nullable()->default(NULL);
            $table->integer('program_id')->nullable()->default(NULL);
            $table->string('program_zoho_id',100)->nullable()->default(NULL);
            $table->date('start_date')->nullable()->default(NULL);
            $table->date('end_date')->nullable()->default(NULL);
            $table->double('payment_amount')->nullable()->default(NULL);
            $table->double('amount')->nullable()->default(NULL);
            $table->string('payment_type',100)->nullable()->default(NULL);
            $table->string('email_old',200)->nullable()->default(NULL);
            $table->string('phone_old',30)->nullable()->default(NULL);
            $table->string('street',200)->nullable()->default(NULL);
            $table->string('city',100)->nullable()->default(NULL);
            $table->string('state',100)->nullable()->default(NULL);
            $table->string('zip',20)->nullable()->default(NULL);
            $table->string('country',100)->nullable()->default(NULL);
            $table->date('created_at')->nullable()->default(NULL);
            $table->date('updated_at')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_schedule');
    }
};
