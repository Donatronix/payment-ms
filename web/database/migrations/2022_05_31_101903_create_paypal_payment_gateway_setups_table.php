<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaypalPaymentGatewaySetupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paypal_payment_gateway_setups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('mode')->nullable();
            $table->string('notify_url')->nullable();
            $table->string('currency')->default('GBP');
            $table->string('sandbox_client_id')->nullable();
            $table->string('sandbox_client_secret')->nullable();
            $table->string('live_client_id')->nullable();
            $table->string('live_client_secret')->nullable();
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
        Schema::dropIfExists('paypal_payment_gateway_setups');
    }
}
