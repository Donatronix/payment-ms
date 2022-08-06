<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Models\PaymentOrder;
use Exception;
use Illuminate\Http\Request;
use Ultainfinity\SolanaPhpSdk\Connection;
use Ultainfinity\SolanaPhpSdk\SolanaRpcClient;

/**
 * Class NetworkSolanaProvider
 * @package App\Services\PaymentServiceProviders
 *
 * https://solscan.io/account/8UuRLvWGtLpYK9QCamc2NUyohQFygA8sQouymbinwYHP
 */
class NetworkSolanaProvider implements PaymentServiceContract
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

        try{
            if ($this->settings->is_develop) {
                $endpoint = SolanaRpcClient::DEVNET_ENDPOINT;
            }else{
                $endpoint = SolanaRpcClient::MAINNET_ENDPOINT;
            }

            $this->service = new Connection(new SolanaRpcClient($endpoint));
        }catch (Exception $e){
            throw $e;
        }
    }

    /**
     * @return string
     */
    public static function key(): string
    {
        return 'network-solana';
    }

    /**
     * @return string
     */
    public static function title(): string
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
                $result['recipient_address'] = $this->settings->recipient_address_devnet;
                $result['network_type'] = 'devnet';
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
        try{
            $accountInfo = $this->service->getTransaction($payload->meta->trx_id);

            $status = $accountInfo['meta']['status'];



        }catch (Exception $e){
            throw $e;
        }
    }
}
