<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Models\PaymentOrder;
use Illuminate\Http\Request;

/**
 * Class NetworkEthereumProvider
 * @package App\Services\PaymentServiceProviders
 */
class NetworkEthereumProvider implements PaymentServiceContract
{
    /**
     * @return string
     */
    public static function key(): string
    {
        return 'ethereum-network';
    }

    /**
     * @return string
     */
    public static function title(): string
    {
        return 'Ethereum Network';
    }

    /**
     * @return string
     */
    public static function description(): string
    {
        return 'Ethereum is a global, decentralized platform for money and new kinds of applications. On Ethereum, you can write code that controls money, and build applications accessible anywhere in the world.';
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
