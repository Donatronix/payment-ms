<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Helpers\PaymentServiceSettings as PaymentSetting;
use App\Models\PaymentOrder;
use CoinbaseCommerce\ApiClient;
use CoinbaseCommerce\Resources\Charge;
use CoinbaseCommerce\Webhook;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CoinbaseProvider implements PaymentServiceContract
{
    // New charge is created
    const STATUS_CHARGE_CREATED = 1;

    //	Charge has been detected but has not been confirmed yet
    const STATUS_CHARGE_PENDING = 2;

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
        self::STATUS_CHARGE_PENDING,
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
     * CoinbaseProvider constructor.
     */
    public function __construct()
    {
        try {
            $this->service = ApiClient::init(PaymentSetting::settings('coinbase_api_key'));
            $this->service->setTimeout(3);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @return string
     */
    public static function service(): string
    {
        return 'coinbase';
    }

    /**
     * @return string
     */
    public static function name(): string
    {
        return 'Coinbase Payment Provider';
    }

    /**
     * @return string
     */
    public static function description(): string
    {
        return 'Coinbase – Buy &amp; Sell Bitcoin, Ethereum, and more with trust';
    }

    /**
     * @return int
     */
    public static function newStatus(): int
    {
        return self::STATUS_CHARGE_CREATED;
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
                    'code' => $payment->check_code,
                    'payment_order_id' => $payment->id
                ],
                'redirect_url' => $inputData->redirect_url ?? null,
                'cancel_url' => $inputData->cancel_url ?? null
            ]);

            // Update payment order
            $payment->status = self::STATUS_CHARGE_CREATED;
            $payment->document_id = $chargeObj->id;
            $payment->save();

            // Return result
            return [
                'gateway' => self::service(),
                'payment_order_id' => $payment->id,
                'invoice_url' => $chargeObj->hosted_url
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
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
                PaymentSetting::settings('coinbase_webhook_key')
            );
            $paymentData = [
                "id" => $event->data->id,
                "metadata" => [
                    "code" => $event->data->metadata->code,
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
        $payment = PaymentOrder::where('type', PaymentOrder::TYPE_PAYIN)
            ->where('id', $paymentData["metadata"]['payment_order_id'])
            ->where('document_id', $paymentData["id"])
            ->where('check_code', $paymentData["metadata"]['code'])
            ->where('gateway', self::service())
            ->first();

        if (!$payment) {
            return [
                'type' => 'danger',
                'message' => sprintf("Payment Order not found in database: payment_order_id=%s, document_id=%s, code=%s", $paymentData->metadata['payment_order_id'], $paymentData->id, $paymentData->metadata['code'])
            ];
        }

        // Update Payment Order status
        $status = 'STATUS_' . mb_strtoupper(Str::snake(str_replace(':', ' ', $event["type"])));
        $payment->status = intval(constant("self::{$status}"));
        //$payment->payload = $paymentData;
        $payment->save();

        // Return result
        return [
            'status' => 'success',
            'gateway' => self::service(),
            'payment_order_id' => $payment->id,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'service' => $payment->service,
            'user_id' => $payment->user_id,
            'payment_completed' => (self::STATUS_CHARGE_CONFIRMED === $payment->status),
        ];
    }
}
