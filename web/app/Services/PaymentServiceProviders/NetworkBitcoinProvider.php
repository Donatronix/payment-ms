<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Models\PaymentOrder;
use Exception;
use Illuminate\Http\Request;

/**
 * Class NetworkBitcoinProvider
 * @package App\Services\PaymentServiceProviders
 */
class NetworkBitcoinProvider implements PaymentServiceContract
{
    // New charge is created
    const STATUS_CHARGE_CREATED = 1;

    // Charge has been detected but has not been confirmed yet
    const STATUS_CHARGE_PENDING = 2;

    // Charge has been confirmed and the associated payment is completed
    const STATUS_CHARGE_CONFIRMED = 3;

    // Charge failed to complete
    const STATUS_CHARGE_FAILED = 4;

    // Charge received a payment after it had been expired
    const STATUS_CHARGE_DELAYED = 5;

    // Charge has been payment successfully
    const STATUS_CHARGE_SUCCEEDED = 6;

    // Charge has been canceled
    const STATUS_CHARGE_CANCELED = 7;

    /**
     * @var int[]
     */
    public static $statuses = [
        self::STATUS_CHARGE_CREATED,
        self::STATUS_CHARGE_PENDING,
        self::STATUS_CHARGE_CONFIRMED,
        self::STATUS_CHARGE_FAILED,
        self::STATUS_CHARGE_DELAYED,
        self::STATUS_CHARGE_SUCCEEDED,
        self::STATUS_CHARGE_CANCELED
    ];

    /**
     * @var
     */
    private $service;

    /**
     * @var string
     */
    private object $settings;

    /**
     * StripeProvider constructor.
     * @param Object $settings
     * @throws Exception
     */
    public function __construct(object $settings)
    {
        $this->settings = $settings;
        $this->service = null;
    }

    /**
     * @return string
     */
    public static function key(): string
    {
        return 'network-bitcoin';
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
        return self::STATUS_CHARGE_CREATED;
    }

    /**
     * Wrapper for create payment order for charge money
     *
     * @param PaymentOrder $order
     * @param object $inputData
     * @return array
     * @throws Exception
     */
    public function charge(PaymentOrder $order, object $inputData): array
    {
        try {
            // Update payment order
            $order->status = self::STATUS_CHARGE_CREATED;
            $order->document_id = null;
            $order->save();

            // Return result
            return [
                'public_key' => $this->settings->public_key
            ];
        } catch (Exception $e) {
            throw $e;
        }
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
