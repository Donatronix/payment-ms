<?php


namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Models\PaymentOrder;
use Illuminate\Http\Request;

class NetworkBitcoinProvider implements PaymentServiceContract
{
    /**
     * @inheritDoc
     */
    public static function service()
    {
        // TODO: Implement service() method.
    }

    /**
     * @inheritDoc
     */
    public static function name()
    {
        // TODO: Implement name() method.
    }

    /**
     * @inheritDoc
     */
    public static function description()
    {
        // TODO: Implement description() method.
    }

    /**
     * @inheritDoc
     */
    public static function getNewStatusId()
    {
        // TODO: Implement getNewStatusId() method.
    }

    /**
     * @inheritDoc
     */
    public function charge(PaymentOrder $payment, object $inputData): mixed
    {
        // TODO: Implement charge() method.
    }

    /**
     * @inheritDoc
     */
    public function createInvoice(PaymentOrder $payment, object $inputData): mixed
    {
        // TODO: Implement createInvoice() method.
    }

    /**
     * @inheritDoc
     */
    public function handlerWebhook(Request $request): mixed
    {
        // TODO: Implement handlerWebhook() method.
    }
}
