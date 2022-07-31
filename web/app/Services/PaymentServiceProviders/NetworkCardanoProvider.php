<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Models\PaymentOrder;
use Illuminate\Http\Request;

/**
 * Class NetworkCardanoProvider
 * @package App\Services\PaymentServiceProviders\
 */
class NetworkCardanoProvider implements PaymentServiceContract
{
    /**
     * @return string
     */
    public static function service(): string
    {
        return 'cardano-network';
    }

    /**
     * @return string
     */
    public static function name(): string
    {
        return 'Cardano Network';
    }

    /**
     * @return string
     */
    public static function description(): string
    {
        return 'Cardano is a blockchain platform for changemakers, innovators, and visionaries, with the tools and technologies required to create possibility for the many, as well as the few, and bring about positive global change';
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
