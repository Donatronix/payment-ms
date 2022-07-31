<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->tinyInteger('type');
            $table->string('gateway', 30);

            $table->decimal('amount');
            $table->char('currency', 3);

            $table->string('document_id')->nullable();
            $table->string('service', 36)->nullable();
            $table->smallInteger('status')->nullable();
            $table->boolean('transaction_created')->default(false);

            $table->uuid('user_id')->index();

            $table->string('check_code');

            $table->text('payload')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

//            $table->tinyInteger('payment_method');
//            $table->tinyInteger('payment_service');
//            $table->unsignedTinyInteger('payment_currency_id')->nullable();

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_orders');
    }
}
