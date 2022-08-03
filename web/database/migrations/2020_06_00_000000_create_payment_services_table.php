<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_services', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('title');
            $table->string('key', 30);
            $table->mediumText('description')->nullable();
            $table->text('icon')->nullable();
            $table->string('new_order_status')->default(1);
            $table->double('amount_min', 15, 9, true)->default(0);
            $table->double('amount_max', 15, 9, true)->default(0);
            $table->boolean('is_develop')->default(true);
            $table->boolean('status')->default(false);

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
        Schema::dropIfExists('payment_services');
    }
}
