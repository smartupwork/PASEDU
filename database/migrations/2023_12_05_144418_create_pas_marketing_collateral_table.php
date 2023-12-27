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
        Schema::create('pas_marketing_collateral', function (Blueprint $table) {
            $table->id();
            $table->integer('progress_report_id');
            $table->string('contact_name',200);
            $table->string('contact_email',180)->nullable()->default(NULL);
            $table->integer('partner_id')->nullable()->default(NULL);
            $table->tinyInteger('is_requested_material')->default(0);
            $table->date('event_date')->nullable()->default(NULL);
            $table->string('target_audience',255)->nullable()->default(NULL);
            $table->text('intended_outcome')->nullable()->default(NULL);
            $table->tinyInteger('branding')->nullable()->default(NULL);
            $table->date('due_date')->nullable()->default(NULL);
            $table->tinyInteger('project_type')->nullable()->default(NULL);
            $table->integer('program_id')->nullable()->default(NULL);
            $table->text('description')->nullable()->default(NULL);
            $table->text('additional_notes')->nullable()->default(NULL);
            $table->text('purpose')->nullable()->default(NULL);
            $table->date('desired_completion_date')->nullable()->default(NULL);
            $table->date('meeting_proposed_date')->nullable()->default(NULL);
            $table->integer('created_by');
            $table->date('created_at');
            $table->integer('updated_by')->nullable()->default(NULL);
            $table->date('updated_at')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_marketing_collateral');
    }
};
