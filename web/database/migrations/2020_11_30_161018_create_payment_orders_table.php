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

            // PO detail
            $table->tinyInteger('type');
            $table->float('amount', 20, 10);
            $table->char('currency', 3);
            $table->uuid('user_id')->index();
            $table->smallInteger('status')->default(0);
            $table->text('metadata')->nullable();

            // Based on document
            $table->uuid('based_id')->default(config('settings.empty_uuid'));
            $table->string('based_type', 30)->nullable();
            $table->string('based_service', 36)->nullable();
            $table->mediumText('based_metadata')->nullable();

            // Service provider data
            $table->string('service_key', 30);
            $table->string('service_document_id')->nullable();
            $table->string('service_document_type')->nullable();
            $table->text('service_payload')->nullable();

            // Transaction check code
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
