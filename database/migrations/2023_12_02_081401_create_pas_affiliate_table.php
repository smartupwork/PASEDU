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
        Schema::create('pas_affiliate', function (Blueprint $table) {
            $table->id(); 
            $table->integer('zoho_id');
            $table->integer('ps_shop_id')->nullable()->default(NULL);
            $table->integer('canvas_sub_account_id')->nullable()->default(NULL);
            $table->string('affiliate_name',100);
            $table->string('phone',15)->nullable()->default(NULL);
            $table->string('email',120)->nullable()->default(NULL);
            $table->string('city',120)->nullable()->default(NULL);
            $table->string('state',100)->nullable()->default(NULL);
            $table->string('zip_postal_code',10)->nullable()->default(NULL);
            $table->string('address_1',500)->nullable()->default(NULL);
            $table->string('address_2',500)->nullable()->default(NULL);
            $table->string('hosted_site',500)->nullable()->default(NULL);
            $table->string('affiliate_site',500)->nullable()->default(NULL);
            $table->integer('price_book_id')->nullable()->default(NULL);
            $table->string('price_book_zoho_id',255)->nullable()->default(NULL);
            $table->tinyInteger('status')->default(0)->comment('0 => Inactive, 1 => Active');
            $table->text('prestashop_menu')->nullable()->default(NULL);
            $table->tinyInteger('sync_ps_product')->default(0);
            $table->timestamp('sync_at')->useCurrent()->nullable()->default(NULL);
            $table->tinyInteger('sync_status')->nullable()->default(NULL);
            $table->integer('created_by')->nullable()->default(NULL);
            $table->timestamp('created_at')->useCurrent()->nullable()->default(NULL);
            $table->integer('updated_by')->nullable()->default(NULL);
            $table->timestamp('updated_at')->useCurrent()->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_affiliate');
    }
};
