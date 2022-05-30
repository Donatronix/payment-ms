<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStripePaymentGatewaySetupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stripe_payment_gateway_setups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('gateway_name')->default('stripe');
            $table->string('webhook_secret')->nullable();
            $table->string('public_key')->nullable();
            $table->string('secret_key')->nullable();
            $table->string('status')->default(1);
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
        Schema::dropIfExists('stripe_payment_gateway_setups');
    }
}
