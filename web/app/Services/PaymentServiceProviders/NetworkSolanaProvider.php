<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Models\PaymentOrder;
use Illuminate\Http\Request;

/**
 * Class NetworkSolanaProvider
 * @package App\Services\PaymentServiceProviders
 */
class NetworkSolanaProvider implements PaymentServiceContract
{
    /**
     * @return string
     */
    public static function service(): string
    {
        return 'solana-network';
    }

    /**
     * @return string
     */
    public static function name(): string
    {
        return 'Solana Network';
    }

    /**
     * @return string
     */
    public static function description(): string
    {
        return 'Scalable Blockchain Infrastructure: Billions of transactions &amp; counting | Solana: Build crypto apps that scale';
    }

    /**
     * @return int
     */
    public static function newStatus(): int
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
