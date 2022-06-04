<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinBasePaymentGatewaySetupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_base_payment_gateway_setups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('api_key')->nullable();
            $table->string('webhook_key')->nullable();
            $table->string('redirect_url')->nullable();
            $table->string('cancel_url')->nullable();
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
        Schema::dropIfExists('coin_base_payment_gateway_setups');
    }
}
