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
        Schema::create('pas_student', function (Blueprint $table) {
            $table->id();
            $table->integer('partner_id');
            $table->string('zoho_id',100)->nullable()->default(NULL);
            $table->binary('first_name',100);
            $table->binary('last_name',100);
            $table->binary('email',100)->nullable()->default(NULL);
            $table->binary('phone',100)->nullable()->default(NULL);
            $table->string('first_name_old',100);
            $table->string('last_name_old',100);
            $table->string('email_old',100);
            $table->integer('program_id');
            $table->date('start_date');
            $table->date('complete_date')->nullable()->default(NULL);
            $table->tinyInteger('status')->default(0)->comment('1=>Active, 2=>Complete, 3=>Refund, 4=>Expired');
            $table->string('payment_type',200)->nullable()->default(NULL);
            $table->double('payment_amount')->nullable()->default(NULL);
            $table->double('price_paid')->nullable()->default(NULL);
            $table->date('end_date')->nullable()->default(NULL);
            $table->string('phone_old',20)->nullable()->default('000-000-0000');
            $table->string('street',255)->nullable()->default(NULL);
            $table->string('city',150)->nullable()->default(NULL);
            $table->integer('state')->nullable()->default(NULL);
            $table->integer('country')->nullable()->default(NULL);
            $table->string('zip',10)->nullable()->default(NULL);
            $table->string('attachment',200)->nullable()->default(NULL);
            $table->integer('created_by');
            $table->date('created_at')->nullable()->default(NULL);
            $table->integer('updated_by')->nullable()->default(NULL);
            $table->date('updated_at')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_student');
    }
};
