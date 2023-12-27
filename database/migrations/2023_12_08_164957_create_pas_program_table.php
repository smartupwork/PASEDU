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
        Schema::create('pas_program', function (Blueprint $table) {
            $table->id();
            $table->string('name',255);
            $table->text('sub_title')->nullable()->default(NULL);
            $table->string('zoho_id',100);
            $table->string('program_type',255)->nullable()->default(NULL);
            $table->text('category')->nullable()->default(NULL);
            $table->string('code',200);
            $table->integer('hours')->nullable()->default(NULL);
            $table->text('duration_type')->nullable()->default(NULL);
            $table->float('duration_value')->nullable()->default(NULL);
            $table->text('level')->nullable()->default(NULL);
            $table->text('occupation')->nullable()->default(NULL);
            $table->longText('feature_tag_line')->nullable()->default(NULL);
            $table->longText('career_description')->nullable()->default(NULL);
            $table->double('median_salary')->nullable()->default(NULL);
            $table->text('job_growth')->nullable()->default(NULL);
            $table->longText('right_career')->nullable()->default(NULL);
            $table->longText('website_short_description')->nullable()->default(NULL);
            $table->longText('learning_objectives')->nullable()->default(NULL);
            $table->longText('support_description')->nullable()->default(NULL);
            $table->text('retail_wholesale')->nullable()->default(NULL);
            $table->longText('description')->nullable()->default(NULL);
            $table->string('owner',255)->nullable()->default(NULL);
            $table->float('unite_price')->nullable()->default(NULL);
            $table->integer('quantity_in_stock')->nullable()->default(NULL);
            $table->text('vendor_name')->nullable()->default(NULL);
            $table->text('average_completion')->nullable()->default(NULL);
            $table->text('avg_completion_time')->nullable()->default(NULL);
            $table->longText('required_materials')->nullable()->default(NULL);
            $table->longText('technical_requirements')->nullable()->default(NULL);
            $table->integer('accreditation')->nullable()->default(NULL);
            $table->longText('certification_benefits')->nullable()->default(NULL);
            $table->longText('general_features_and_benefits')->nullable()->default(NULL);
            $table->text('demo_url')->nullable()->default(NULL);
            $table->text('status')->nullable()->default(NULL);
            $table->text('certification_included')->nullable()->default(NULL);
            $table->longText('certification_inclusion')->nullable()->default(NULL);
            $table->text('displayed_on');
            $table->tinyInteger('service_item_not_program');
            $table->text('prepares_for_certification')->nullable()->default(NULL);
            $table->longText('mycaa_description')->nullable()->default(NULL);
            $table->text('layout')->nullable()->default(NULL);
            $table->tinyInteger('is_featured');
            $table->tinyInteger('is_best_seller')->nullable()->default(0);
            $table->longText('tag_line')->nullable()->default(NULL);
            $table->longText('prerequisites')->nullable()->default(NULL);
            $table->longText('outline')->nullable()->default(NULL);
            $table->text('externship_included')->nullable()->default(NULL);
            $table->longText('approved_offering')->nullable()->default(NULL);
            $table->text('language')->nullable()->default(NULL);
            $table->text('ce_units')->nullable()->default(NULL);
            $table->longText('audience')->nullable()->default(NULL);
            $table->longText('delivery_methods_available')->nullable()->default(NULL);
            $table->longText('certification')->nullable()->default(NULL);
            $table->string('vendor_id',20)->nullable()->default(NULL);
            $table->tinyInteger('is_copy')->default(1);
            $table->tinyInteger('price_book_counter')->default(0); 
            $table->integer('created_by')->nullable()->default(NULL);
            $table->dateTime('created_at')->nullable()->default(NULL);
            $table->integer('updated_by')->nullable()->default(NULL);
            $table->dateTime('updated_at')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_program');
    }
};
