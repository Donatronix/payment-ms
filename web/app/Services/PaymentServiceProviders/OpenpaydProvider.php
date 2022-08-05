<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Models\PaymentOrder;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

/**
 * Class OpenpaydProvider
 * @package App\Services\PaymentServiceProviders
 */
class OpenpaydProvider implements PaymentServiceContract
{
    // Transaction statuses
    // https://apidocs.openpayd.com/docs/transaction-status-updated-webhook#transaction-types

    const TRANSACTION_TYPE_CHARGE = "PAYIN";
    const TRANSACTION_TYPE_PAYOUT = "PAYOUT";
    const TRANSACTION_TYPE_FEE = "FEE";
    const TRANSACTION_TYPE_RETURN_IN = "RETURN_IN";
    const TRANSACTION_TYPE_RETURN_OUT = "RETURN_OUT";

    const TRANSACTION_STATUS_PROCESSING = "PROCESSING";
    const TRANSACTION_STATUS_RELEASED = "RELEASED";
    const TRANSACTION_STATUS_COMPLETED = "COMPLETED";
    const TRANSACTION_STATUS_FAILED = "FAILED";
    const TRANSACTION_STATUS_CANCELLED = "CANCELLED";

    /**
     * @var  Client
     */
    private Client $service;

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
            $this->service = new Client(['base_uri' => $this->settings->url]);

            $username = $this->settings->username;
            $password = $this->settings->password;
            $salt = $username . ":" . $password;

            $code = base64_encode($salt);

            $payload = [
                "form_params" => [
                    "username" => $username,
                    "password" => $password
                ],
                "headers" => [
                    "Authorization" => "Basic " . $code,
                ]
            ];

            return $this->service->post("oauth/token?grant_type=client_credentials", $payload);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @return string
     */
    public static function key(): string
    {
        return 'openpayd';
    }

    /**
     * @return string
     */
    public static function title(): string
    {
        return 'OpenPayd - Embedded Finance for the digital economy';
    }

    /**
     * @return string
     */
    public static function description(): string
    {
        return 'Embedded Finance for the digital economy. Accounts, FX, domestic and international payments, acquiring, open banking, and more - all delivered through a single, developer-first API.';
    }

    /**
     * @return integer
     */
    public static function newOrderStatus(): int
    {
        return 0;
    }

    /**
     * Make one-time charge money to system
     *
     * @param PaymentOrder $order
     * @param object $inputData
     * @return mixed
     */
    public function charge(PaymentOrder $order, object $inputData): mixed
    {
        // TODO not yet provided by openpayd
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function handlerWebhook(Request $request): array
    {
        $signature = $request->header("signature");
        $payload = $request->get("payload", null);

        if (!$this->isValidSignature($signature, $payload)) {
            return [
                "type" => "danger",
                "message" => "Openpayd: Invalid signature"
            ];
        }

        $webhookPayload = json_decode($payload);

        $transactionStatus = strtoupper($webhookPayload["status"]);
        $transactionType = strtoupper($webhookPayload["type"]);

        if ($transactionType == self::TRANSACTION_TYPE_CHARGE) {
            //  retrieve payment and update status
            // TODO find a way to access webhook metadata.
            $order = PaymentOrder::where('type', PaymentOrder::TYPE_CHARGE)
                ->where('id', $webhookPayload["metadata"]['orderId'])
                ->where('service_document_id', $webhookPayload["id"])
                ->where('check_code', $webhookPayload["metadata"]['check_code'])
                ->where('service_key', self::key())
                ->first();

            if (!$order) {
                return [
                    'type' => 'danger',
                    'message' => 'Payment Order not found in Payment Microservice database'
                ];
            }

            $order->status = $transactionStatus;

            // $order->payload = $paymentData;
            $order->save();

            // Return result
            return [
                'type' => 'success',
                'payment_order_id' => $order->id,
                'amount' => $order->amount,
                'currency' => $order->currency,
                'service' => $order->service,
                'user_id' => $order->user_id,
                'payment_completed' => (self::TRANSACTION_STATUS_COMPLETED === $order->status),
            ];
        } else {
            //  we are not yet interested in other account webhooks not PAYIN
            return [
                'type' => 'danger',
                'message' => 'OpenPayd: Not a PAYIN webhook'
            ];
        }
    }

    private function isValidSignature($signature, $data): bool
    {
        $pubKeyPath = $this->settings->public_key_path;

        if ($signature == hash_hmac_file('sha256', $data, $pubKeyPath)) {

            return true;
        }

        return false;
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
