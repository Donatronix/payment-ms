<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Models\PaymentOrder;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3\Web3;

/**
 * Class NetworkEthereumProvider
 * @package App\Services\PaymentServiceProviders
 */
class NetworkEthereumProvider implements PaymentServiceContract
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

        try {
            if ($this->settings->is_develop) {
                $endpoint = null;
            } else {
                $endpoint = 'http://localhost:8545';
            }
            $this->service = new Web3(new HttpProvider(new HttpRequestManager($endpoint)));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @return string
     */
    public static function key(): string
    {
        return 'network-ethereum';
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
     * @throws Exception
     */
    public function checkTransaction(object $payload): mixed
    {
        try {
            $transactionInfo = false;

            // Get transaction info
            $this->service->batch(true);
            $this->service->clientVersion(function ($err, $version) {
                if ($err !== null) {
                    throw new RuntimeException($err);
                }
            });

            //add transaction hash
            $this->service->hash($payload->meta['trx_id']);
            $this->service->execute(function ($err, $data) use (&$transactionInfo) {
                if ($err !== null) {
                    throw new RuntimeException($err);
                }

                // do something
                $transactionInfo = $data;
            });

            // If is not transaction info, then transaction is still processing
            if (!$transactionInfo) {
                return [
                    'status' => self::STATUS_CHARGE_PROCESSING,
                ];
            }

            $status = strtolower(array_key_first($transactionInfo['meta']['status']));
            $result = [
                'status' => self::$statuses[$status],
                'transaction_id' => $payload->meta['trx_id'],
                'block_time' => Carbon::parse($transactionInfo['blockTime'])->format('d M Y h:i A')
            ];

            if (strtolower($status) === 'ok') {
                // Get transaction amount
                $postBalance = $transactionInfo['meta']['postBalances'][1];
                $preBalance = $transactionInfo['meta']['preBalances'][1];
                $result['amount'] = ($postBalance - $preBalance) / 1000000000;

                // Get sender wallet
                $result = array_merge($result, ['sender_wallet' => $transactionInfo['transaction']['message']['accountKeys'][0]]);
                // Add info
                $result = array_merge($result, ['network' => ucfirst(Str::replace('network-', '', self::key()))]);
                $result = array_merge($result, ['mode' => $this->settings->is_develop ? 'devnet' : 'mainnet']);
                $result = array_merge($result, ['payer_name' => '']);
                $result = array_merge($result, ['payer_email' => '']);
            }

            return $result;
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
