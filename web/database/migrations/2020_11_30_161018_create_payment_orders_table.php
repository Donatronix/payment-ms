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

            $table->float('amount', 20, 10);
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
