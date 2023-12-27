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
        Schema::create('pas_dashboard_report', function (Blueprint $table) {
            $table->id();
            $table->integer('partner_id');
            $table->integer('current_year_enrollments')->default(0);
            $table->double('current_year_revenue')->default(0);
            $table->integer('active_enrollments')->default(0);
            $table->integer('life_time_enrollments')->default(0);
            $table->double('remaining_po_amount')->default(0);
            $table->double('completion_rate')->default(0);
            $table->double('conversion_rate')->default(0);
            $table->double('retention_rate')->default(0);
            $table->double('lifetime_revenue')->default(0);
            $table->dateTime('created_at')->nullable()->default(NULL);
            $table->dateTime('updated_at')->nullable()->default(NULL);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_dashboard_report');
    }
};
