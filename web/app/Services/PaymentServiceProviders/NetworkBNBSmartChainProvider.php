<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Models\PaymentOrder;
use Exception;
use Illuminate\Http\Request;

/**
 * Class NetworkBNBSmartChainProvider
 * @package App\Services\PaymentServiceProviders
 */
class NetworkBNBSmartChainProvider implements PaymentServiceContract
{
    // New charge is created
    const STATUS_CHARGE_CREATED = 'created';

    // Charge has been detected but has not been confirmed yet
    const STATUS_CHARGE_PROCESSING = 'processing';

    // Charge has been confirmed and the associated payment is completed
    const STATUS_CHARGE_CONFIRMED = 'confirmed';

    // Charge failed to complete
    const STATUS_CHARGE_FAILED = 'failed';

    // Charge received a payment after it had been expired
    const STATUS_CHARGE_DELAYED = 'delayed';

    // Charge has been payment successfully
    const STATUS_CHARGE_SUCCEEDED = 'succeeded';

    // Charge has been canceled
    const STATUS_CHARGE_CANCELED = 'canceled';

    /**
     * @var array|string[]
     */
    private static array $statuses = [
        'created' => self::STATUS_CHARGE_CREATED,
        'processing' => self::STATUS_CHARGE_PROCESSING,
        'confirmed' => self::STATUS_CHARGE_CONFIRMED,
        'delayed' => self::STATUS_CHARGE_DELAYED,
        'failed' => self::STATUS_CHARGE_FAILED,
        'succeeded' => self::STATUS_CHARGE_SUCCEEDED,
        'canceled' => self::STATUS_CHARGE_CANCELED
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
        return 'network-bnb-smart-chain';
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
            $order->status = PaymentOrder::$statuses[self::STATUS_CHARGE_PROCESSING];
            $order->service_document_id = null;
            $order->save();

            $result = [];
            if ($this->settings->is_develop) {
                $result['recipient_address'] = $this->settings->recipient_address_testnet;
                $result['network_type'] = 'testnet';
            } else {
                $result['recipient_address'] = $this->settings->recipient_address_mainnet;
                $result['network_type'] = 'mainnet';
            }

            // Return result
            return $result;
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

    /**
     * @param object $payload
     * @return mixed
     */
    public function checkTransaction(object $payload): mixed
    {
        // TODO: Implement checkTransaction() method.
    }
}
