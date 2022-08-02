<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Models\PaymentOrder;
use Illuminate\Http\Request;

/**
 * Class NetworkBitcoinProvider
 * @package App\Services\PaymentServiceProviders
 */
class NetworkBitcoinProvider implements PaymentServiceContract
{
    /**
     * @return string
     */
    public static function key(): string
    {
        return 'bitcoin-network';
    }

    /**
     * @return string
     */
    public static function title(): string
    {
        return 'Bitcoin Network';
    }

    /**
     * @return string
     */
    public static function description(): string
    {
        return 'Bitcoin (₿) is a first decentralized digital currency that can be transferred on the peer-to-peer bitcoin network';
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
