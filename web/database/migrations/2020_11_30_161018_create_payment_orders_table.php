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

            // Based on document
            $table->uuid('based_id')->default(config('settings.empty_uuid'));
            $table->string('based_type', 30)->nullable();
            $table->string('based_service', 36)->nullable();
            $table->mediumText('based_meta')->nullable();

            $table->smallInteger('status')->default(0);
            $table->uuid('user_id')->index();

            $table->string('document_id')->nullable();
            $table->text('payload')->nullable();
            $table->string('check_code');

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
