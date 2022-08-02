<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Models\PaymentOrder;
use Illuminate\Http\Request;

/**
 * Class NetworkBNBSmartChainProvider
 * @package App\Services\PaymentServiceProviders
 */
class NetworkBNBSmartChainProvider implements PaymentServiceContract
{
    /**
     * @return string
     */
    public static function key(): string
    {
        return 'bnb-smart-chain-network';
    }

    /**
     * @return string
     */
    public static function title(): string
    {
        return 'BNB Smart Chain Network';
    }

    /**
     * @return string
     */
    public static function description(): string
    {
        return 'BNB Smart Chain (formerly known as Binance Smart Chain) is also used for sending and receiving BNB and digital assets. However, these assets are based on the BEP20 token standard. The BSC network is compatible with the Ethereum Virtual Machine (EVM) so it shares the same address format as Ethereum (ETH) addresses, which start with "0x..."';
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
