<?php

use App\Models\PaymentOrder;
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
            $table->string('number', 20)->index();
            $table->tinyInteger('type');
            $table->float('amount', 20, 10);
            $table->char('currency', 3);
            $table->uuid('user_id')->index();
            $table->unsignedSmallInteger('status')->default(PaymentOrder::STATUS_ORDER_CREATED);
            $table->text('metadata')->nullable();

            // Based on document
            $table->uuid('based_id')->default(config('settings.empty_uuid'));
            $table->string('based_object', 30)->nullable();
            $table->string('based_service', 36)->nullable();
            $table->mediumText('based_metadata')->nullable();
            $table->index(['based_id', 'based_object', 'based_service']);

            // Service provider data
            $table->string('service_key', 30)->index();
            $table->string('service_document_id')->nullable()->index();
            $table->string('service_document_type')->nullable()->index();
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
