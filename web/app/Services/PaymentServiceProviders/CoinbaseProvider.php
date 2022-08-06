<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Models\PaymentOrder;
use CoinbaseCommerce\ApiClient;
use CoinbaseCommerce\Resources\Charge;
use CoinbaseCommerce\Webhook;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class CoinbaseProvider
 * @package App\Services\PaymentServiceProviders
 */
class CoinbaseProvider implements PaymentServiceContract
{
    // New charge is created
    const STATUS_CHARGE_CREATED = 1;

    //	Charge has been detected but has not been confirmed yet
    const STATUS_CHARGE_PROCESSING = 2;

    //	Charge has been confirmed and the associated payment is completed
    const STATUS_CHARGE_CONFIRMED = 3;

    //	Charge failed to complete
    const STATUS_CHARGE_FAILED = 4;

    //	Charge received a payment after it had been expired
    const STATUS_CHARGE_DELAYED = 5;

    //	Charge has been resolved
    const STATUS_CHARGE_RESOLVED = 6;

    /**
     * @var int[]
     */
    public static $statuses = [
        self::STATUS_CHARGE_CREATED,
        self::STATUS_CHARGE_PROCESSING,
        self::STATUS_CHARGE_CONFIRMED,
        self::STATUS_CHARGE_FAILED,
        self::STATUS_CHARGE_DELAYED,
        self::STATUS_CHARGE_RESOLVED,
    ];

    /**
     * @var ApiClient
     */
    private ApiClient $service;

    /**
     * @var string
     */
    private object $settings;

    /**
     * CoinbaseProvider constructor.
     * @param object $settings
     * @throws Exception
     */
    public function __construct(object $settings)
    {
        $this->settings = $settings;

        try {
            $this->service = ApiClient::init($this->settings->api_key);
            $this->service->setTimeout(3);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @return string
     */
    public static function key(): string
    {
        return 'coinbase';
    }

    /**
     * @return string
     */
    public static function title(): string
    {
        return 'Coinbase payment service provider';
    }

    /**
     * @return string
     */
    public static function description(): string
    {
        return 'Coinbase – Buy &amp; Sell Bitcoin, Ethereum, and more with trust';
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
            // Create new charge
            $chargeObj = Charge::create([
                'name' => 'Charge account balance',
                'description' => 'Payment Order for charge user account balance',
                'pricing_type' => 'fixed_price',
                'local_price' => [
                    'amount' => $inputData->amount,
                    'currency' => $inputData->currency
                ],
                'metadata' => [
                    'check_code' => $order->check_code,
                    'payment_order_id' => $order->id
                ],
                'redirect_url' => $inputData->redirect_url ?? null,
                'cancel_url' => $inputData->cancel_url ?? null
            ]);

            // Update payment order
            $order->status = self::STATUS_CHARGE_CREATED;
            $order->service_document_id = $chargeObj->id;
            $order->save();

            // Return result
            return [
                'payment_order_url' => $chargeObj->hosted_url
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
        Log::info((string)$_SERVER);
        Log::info(file_get_contents('php://input'));
        Log::info($request);
        $event = $request["event"];
        Log::info("Event:");
        Log::info($event);
        $paymentData = $event["data"];

        $signature = $request->header('X-Cc-Webhook-Signature', null);
        if ($signature === null && !env("APP_DEBUG", 0)) {
            return [
                'type' => 'danger',
                'message' => 'Missing signature'
            ];
        }

        // Get event
        try {
            $event = Webhook::buildEvent(
                trim(file_get_contents('php://input')),
                $signature,
                $this->settings->webhook_key
            );
            $paymentData = [
                "id" => $event->data->id,
                "metadata" => [
                    "check_code" => $event->data->metadata->check_code,
                    "payment_order_id" => $event->data->metadata->payment_order_id
                ]
            ];
        } catch (Exception $e) {
            if (env("APP_DEBUG", 0)) {

            } else return [
                'type' => 'danger',
                'message' => $e->getMessage()
            ];
        }

        // Get event data
        Log::info($event);
        Log::info(json_encode($paymentData));
        if (!isset($paymentData) || !is_array($paymentData) || !isset($paymentData["metadata"])) {
            return [
                'type' => 'danger',
                'message' => 'Empty / Incorrect event data'
            ];
        }

        // Find Payment Order
        $order = PaymentOrder::where('type', PaymentOrder::TYPE_CHARGE)
            ->where('id', $paymentData["metadata"]['payment_order_id'])
            ->where('service_document_id', $paymentData["id"])
            ->where('check_code', $paymentData["metadata"]['check_code'])
            ->where('service_key', self::key())
            ->first();

        if (!$order) {
            return [
                'type' => 'danger',
                'message' => sprintf("Payment Order not found in database: payment_order_id=%s, service_document_id=%s, check_code=%s", $paymentData->metadata['payment_order_id'], $paymentData->id, $paymentData->metadata['check_code'])
            ];
        }

        // Update Payment Order status
        $status = 'STATUS_' . mb_strtoupper(Str::snake(str_replace(':', ' ', $event["type"])));
        $order->status = intval(constant("self::{$status}"));
        //$order->payload = $paymentData;
        $order->save();

        // Return result
        return [
            'type' => 'success',
            'service_key' => self::key(),
            'payment_order_id' => $order->id,
            'amount' => $order->amount,
            'currency' => $order->currency,
            'service' => $order->service,
            'user_id' => $order->user_id,
            'payment_completed' => (self::STATUS_CHARGE_CONFIRMED === $order->status),
        ];
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
