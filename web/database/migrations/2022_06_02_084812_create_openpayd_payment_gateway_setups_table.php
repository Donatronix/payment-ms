<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpenpaydPaymentGatewaySetupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('openpayd_payment_gateway_setups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('url')->nullable();
            $table->string('public_key_path')->nullable();
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
        Schema::dropIfExists('openpayd_payment_gateway_setups');
    }
}
