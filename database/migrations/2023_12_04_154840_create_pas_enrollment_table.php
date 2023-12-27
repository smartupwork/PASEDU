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
        Schema::create('pas_enrollment', function (Blueprint $table) {
            $table->id();
            $table->string('zoho_id',30);
            $table->integer('student_id')->nullable()->default(NULL);
            $table->string('student_zoho_id',100)->nullable()->default(NULL);
            $table->integer('partner_id');
            $table->string('partner_zoho_id',50);
            $table->integer('contact_id')->nullable()->default(NULL);
            $table->string('contact_zoho_id',100)->nullable()->default(NULL);
            $table->string('subject',1000)->nullable()->default(NULL);
            $table->string('status',200)->nullable()->default(NULL);
            $table->binary('date_of_birth',100)->nullable()->default(NULL);
            $table->binary('social_security_number',100)->nullable()->default(NULL);
            $table->double('grand_total')->nullable()->default(NULL);
            $table->date('start_date')->nullable()->default(NULL);
            $table->string('program_name',200)->nullable()->default(NULL);
            $table->string('program_zoho_id',30)->nullable()->default(NULL);
            $table->date('completion_date')->nullable()->default(NULL);
            $table->date('end_date')->nullable()->default(NULL);
            $table->double('final_grade')->nullable()->default(NULL);
            $table->string('username',200)->nullable()->default(NULL);
            $table->timestamp('enrollment_created_at')->nullable()->default(NULL);
            $table->timestamp('enrollment_updated_at')->nullable()->default(NULL);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_enrollment');
    }
};
