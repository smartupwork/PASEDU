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
        Schema::create('pas_contact', function (Blueprint $table) {
            $table->id();
            $table->string('zoho_id',50);
            $table->binary('first_name',100);
            $table->binary('last_name',100);
            $table->binary('email',100);
            $table->binary('phone',100)->nullable()->default(NULL);
            $table->binary('mobile',100)->nullable()->default(NULL);
            $table->string('first_name_old',100)->nullable()->default();
            $table->string('last_name_old',100)->nullable()->default();
            $table->string('contact_title',150)->nullable()->default();
            $table->binary('date_of_birth',100)->nullable()->default();
            $table->string('email_old',100)->nullable()->default();
            $table->string('mobile_old',50)->nullable()->default();
            $table->string('phone_old',50)->nullable()->default();
            $table->string('contact_active',100)->nullable()->default();
            $table->string('contact_role',100)->nullable()->default();
            $table->string('lead_created',100)->nullable()->default();
            $table->string('lead_source',100)->nullable()->default();
            $table->string('mailing_city',100)->nullable()->default();
            $table->string('mailing_country',100)->nullable()->default();
            $table->string('mailing_state',50)->nullable()->default();
            $table->string('mailing_street',100)->nullable()->default();
            $table->string('mailing_zip',20)->nullable()->default();
            $table->integer('partner_id')->nullable()->default(NULL);
            $table->string('partner_zoho_id',100)->nullable()->default();
            $table->string('secondary_email',150)->nullable()->default();
            $table->binary('social_security_number',150)->nullable()->default();
            $table->timestamp('created_at')->nullable()->default(NULL);
            $table->timestamp('updated_at')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_contact');
    }
};
