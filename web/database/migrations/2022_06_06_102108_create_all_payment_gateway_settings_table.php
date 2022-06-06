<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllPaymentGatewaySettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('all_payment_gateway_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('payment_gateway_name')->nullable();

            $table->string('bitpay_bitpay_environment')->nullable();
            $table->string('bitpay_api_token_merchant')->nullable();
            $table->string('bitpay_api_token_payroll')->nullable();
            $table->string('bitpay_bitpay_key_path')->nullable();
            $table->string('bitpay_private_key_password')->nullable();
            $table->string('bitpay_payment_webhook_url')->nullable();
            $table->string('bitpay_redirect_url')->nullable();

            $table->string('coinbase_api_key')->nullable();
            $table->string('coinbase_webhook_key')->nullable();
            $table->string('coinbase_redirect_url')->nullable();
            $table->string('coinbase_cancel_url')->nullable();

            $table->string('openpayd_username')->nullable();
            $table->string('openpayd_password')->nullable();
            $table->string('openpayd_url')->nullable();
            $table->string('openpayd_public_key_path')->nullable();

            $table->string('paypal_mode')->nullable();
            $table->string('paypal_notify_url')->nullable();
            $table->string('paypal_currency')->nullable();
            $table->string('paypal_sandbox_client_id')->nullable();
            $table->string('paypal_sandbox_client_secret')->nullable();
            $table->string('paypal_live_client_id')->nullable();
            $table->string('paypal_live_client_secret')->nullable();
            $table->string('paypal_sandbox_api_url')->nullable();
            $table->string('paypal_live_api_url')->nullable();
            $table->string('paypal_payment_action')->nullable();
            $table->string('paypal_locale')->nullable();
            $table->string('paypal_validate_ssl')->nullable();
            $table->string('paypal_app_id')->nullable();

            $table->string('stripe_webhook_secret')->nullable();
            $table->string('stripe_public_key')->nullable();
            $table->string('stripe_secret_key')->nullable();

            $table->integer('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('all_payment_gateway_settings');
    }
}
