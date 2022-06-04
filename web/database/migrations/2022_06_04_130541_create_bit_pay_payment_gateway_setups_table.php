<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBitPayPaymentGatewaySetupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bit_pay_payment_gateway_setups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('bitpay_environment')->nullable();
            $table->string('api_token_merchant')->nullable();
            $table->string('api_token_payroll')->nullable();
            $table->string('bitpay_key_path')->nullable();
            $table->string('private_key_password')->nullable();
            $table->string('payment_webhook_url')->nullable();
            $table->integer('redirect_url')->nullable();
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
        Schema::dropIfExists('bit_pay_payment_gateway_setups');
    }
}
