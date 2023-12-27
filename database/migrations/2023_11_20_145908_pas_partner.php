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
        Schema::create('pas_partner', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('zoho_id',30);
            $table->integer('ps_shop_id')->nullable()->default(NULL);
            $table->bigInteger('canvas_sub_account_id')->nullable()->default(NULL);
            $table->string('partner_name',200)->nullable();
            $table->string('contact_name',255)->nullable()->default(NULL);
            $table->string('tp_contact_name',255)->nullable()->default(NULL);
            $table->bigInteger('parent_partner_name')->nullable()->default(NULL);
            $table->string('parent_partner_zoho_id',20)->nullable()->default(NULL);
            $table->integer('parent_partner_id')->nullable()->default(NULL);
            $table->string('title',255)->nullable()->default(NULL);
            $table->string('phone',15)->nullable()->default(NULL);
            $table->string('email',120)->nullable()->default(NULL);
            $table->string('pi_phone',15)->nullable()->default(NULL);
            $table->string('pi_email',120)->nullable()->default(NULL);
            $table->string('department',255)->nullable()->default(NULL);
            $table->string('street',255)->nullable()->default(NULL);
            $table->string('city',200)->nullable()->default(NULL);
            $table->integer('state')->nullable()->default(NULL);
            $table->string('zip_code',10)->nullable()->default(NULL);
            $table->tinyInteger('wia')->default(0);
            $table->tinyInteger('mycaa')->default(0);
            $table->string('hosted_site',255)->nullable()->default(NULL);
            $table->integer('price_book_id')->nullable()->default(NULL);
            $table->string('price_book_zoho_id',255)->nullable()->default(NULL);
            $table->integer('total_selling_course')->default(0);
            $table->string('logo',150)->nullable()->default(NULL);
            $table->string('record_image',1000)->nullable()->default(NULL);
            $table->string('partner_type',255)->nullable()->default(NULL);
            $table->longText('contacts')->nullable()->default(NULL);
            $table->tinyInteger('status')->default(0)->comment('0 => Inactive, 1 => Active');
            $table->string('contact_title',500)->nullable()->default(NULL);
            $table->string('campus_name_if_applicable',500)->nullable()->default(NULL);
            $table->string('billing_street',255)->nullable()->default(NULL);
            $table->string('billing_address_2',255)->nullable()->default(NULL);
            $table->string('billing_city',100)->nullable()->default(NULL);
            $table->string('billing_code',100)->nullable()->default(NULL);
            $table->string('billing_country',100)->nullable()->default(NULL);
            $table->string('billing_state',500)->nullable()->default(NULL);
            $table->string('tp_website',500)->nullable()->default(NULL);
            $table->string('mkt_colors1',15)->nullable()->default(NULL);
            $table->string('mkt_colors2',15)->nullable()->default(NULL);
            $table->string('mkt_colors3',15)->nullable()->default(NULL);
            $table->string('mkt_colors4',15)->nullable()->default(NULL);
            $table->string('mkt_colors5',15)->nullable()->default(NULL);
            $table->string('mkt_colors6',15)->nullable()->default(NULL);
            $table->string('mkt_colors7',15)->nullable()->default(NULL);
            $table->string('mkt_colors8',15)->nullable()->default(NULL);
            $table->string('mkt_colors9',15)->nullable()->default(NULL);
            $table->string('mkt_colors10',15)->nullable()->default(NULL);
            $table->text('prestashop_menu')->nullable()->default(NULL);
            $table->tinyInteger('sync_ps_product')->default(0);
            $table->integer('created_by')->nullable()->default(NULL);
            $table->integer('updated_by')->nullable()->default(NULL);
            $table->string('signature',200)->nullable()->default(NULL);
            $table->dateTime('sync_at')->nullable()->default(NULL);
            $table->tinyInteger('sync_status')->nullable()->default(NULL);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_partner');
    }
};
