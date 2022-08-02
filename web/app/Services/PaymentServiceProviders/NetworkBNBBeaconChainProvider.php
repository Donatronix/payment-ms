<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Models\PaymentOrder;
use Illuminate\Http\Request;

/**
 * Class NetworkBNBBeaconChainProvider
 * @package App\Services\PaymentServiceProviders
 */
class NetworkBNBBeaconChainProvider implements PaymentServiceContract
{
    /**
     * @return string
     */
    public static function key(): string
    {
        return 'bnb-beacon-chain-network';
    }

    /**
     * @return string
     */
    public static function title(): string
    {
        return 'BNB Beacon Chain Network';
    }

    /**
     * @return string
     */
    public static function description(): string
    {
        return 'BNB Beacon Chain (formerly known as Binance Chain) is used for sending and receiving BNB and digital assets based on the BEP2 token standard. BNB network addresses start with "bnb..."';
    }

    /**
     * @return int
     */
    public static function newOrderStatus(): int
    {
        return 0;
    }

    /**
     * Wrapper for create payment order for charge money
     *
     * @param PaymentOrder $payment
     * @param object $inputData
     * @return array
     * @throws Exception
     */
    public function charge(PaymentOrder $payment, object $inputData): array
    {
        return [];
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function handlerWebhook(Request $request): array
    {
        return [];
    }
}
