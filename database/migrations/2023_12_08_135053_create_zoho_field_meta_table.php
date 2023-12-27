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
        Schema::create('zoho_field_meta', function (Blueprint $table) {
            $table->id();
            $table->string('module',100)->collation('latin1_swedish_ci');
            $table->string('field_label',100)->collation('latin1_swedish_ci');
            $table->longText('pick_list_values')->collation('latin1_swedish_ci');
            $table->longText('all_data')->collation('latin1_swedish_ci');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoho_field_meta');
    }
};
