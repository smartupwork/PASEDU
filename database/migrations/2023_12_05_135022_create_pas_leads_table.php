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
        Schema::create('pas_leads', function (Blueprint $table) {
            $table->id();
            $table->string('zoho_id',100)->nullable()->default(NULL);
            $table->integer('partner_id');
            $table->string('owner_zoho_id',50)->nullable()->default(NULL);
            $table->string('partner_institution',255)->nullable()->default(NULL);
            $table->string('name_of_requester',222)->nullable()->default(NULL);
            $table->string('email_of_requester',122)->nullable()->default(NULL);
            $table->string('firstname',100)->nullable()->default(NULL);
            $table->string('lastname',100)->nullable()->default(NULL);
            $table->string('email',120)->nullable()->default(NULL);
            $table->string('address',255)->nullable()->default(NULL);
            $table->string('phone',255)->nullable()->default(NULL);
            $table->string('city',200)->nullable()->default(NULL);
            $table->string('state',200)->nullable()->default(NULL);
            $table->string('zip',10)->nullable()->default(NULL);
            $table->string('country',222)->nullable()->default(NULL);
            $table->string('interested_program',255)->nullable()->default(NULL);
            $table->string('financing_needs',222)->nullable()->default(NULL);
            $table->string('category_of_interest',200)->nullable()->default(NULL);
            $table->integer('time_zone')->nullable()->default(0);
            $table->string('inquiry_message',255)->nullable()->default(NULL);
            $table->integer('added_by')->default(0);
            $table->timestamp('added_date')->useCurrent()->default(NULL);



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_leads');
    }
};
