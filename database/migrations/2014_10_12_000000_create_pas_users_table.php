<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('pas_users', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('user_type')
            ->default(0)
            ->comment('1=>Admin, 2=>Partner, 3=>We Users, 4 => My Users');
            $table->string('email')->nullable()->default(NULL)->unique();
            $table->string('password')->nullable()->default(NULL);
            $table->integer('roleid')->default(0);
            $table->string('firstname')->nullable()->default(NULL);
            $table->string('lastname')->nullable()->default(NULL);
            $table->dateTime('last_active')->nullable()->default(NULL);
            $table->tinyInteger('status')->default(0)->comment('1=>Active, 2=>Inactive');
            $table->dateTime('request_time')->nullable()->default(NULL);
            $table->tinyInteger('reset_status')->default(0);
            $table->tinyInteger('first_login')->default(0);
            $table->tinyInteger('login_status')->default(0);
            $table->integer('otp')->default(0);
            $table->string('photo')->nullable()->default(NULL);
            $table->string('phone')->nullable()->default(NULL);
            $table->string('partner')->nullable()->default(NULL);
            $table->integer('partner_type')->nullable()->default(NULL);
            $table->integer('augusoft_campus')->default(0)->nullable()->default(NULL)->comment('1=>Augusoft, 2=>Campus CE');
            $table->enum('access_level', ['account-manager', 'account-support', 'registration-account-partner'])->nullable()->default(NULL);
            $table->string('access_feature')->nullable()->default(NULL);
            $table->integer('partner_id')->nullable()->default(NULL);
            $table->integer('added_by')->nullable()->default(NULL);
            $table->integer('login_code')->nullable()->default(NULL);
            $table->dateTime('last_wrong_attempted_at')->nullable()->default(NULL);
            $table->json('highlight_reports')->nullable()->default('["cy-enrollments","cy-revenue","retention-rate","lifetime-revenue"]');
            $table->dateTime('password_expired_at')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas_users');
    }
};
