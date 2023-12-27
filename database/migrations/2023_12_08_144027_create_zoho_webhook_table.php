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
        Schema::create('zoho_webhook', function (Blueprint $table) {
            $table->id();
            $table->enum('action',['create/update','delete'])->collation('latin1_swedish_ci')->default('create/update');
            $table->string('module',100)->collation('latin1_swedish_ci')->nullable()->default('SalesOrders');
            $table->enum('status',['success','exception'])->collation('latin1_swedish_ci')->nullable()->default('success');
            $table->text('response')->collation('latin1_swedish_ci')->nullable()->default(NULL);
            $table->dateTime('created_at')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoho_webhook');
    }
};
