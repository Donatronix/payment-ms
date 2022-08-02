<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Helpers\PaymentServiceSettings;
use App\Models\PaymentOrder;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class OpenpaydProvider implements PaymentServiceContract
{
    // https://apidocs.openpayd.com/docs/transaction-status-updated-webhook#transaction-types
    // Transaction statuses

    const TRANSACTION_TYPE_PAYIN = "PAYIN";
    const TRANSACTION_TYPE_PAYOUT = "PAYOUT";
    const TRANSACTION_TYPE_FEE = "FEE";
    const TRANSACTION_TYPE_TRANSFER = "TRANSFER";
    const TRANSACTION_TYPE_EXCHANGE = "EXCHANGE";
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
    private $settings;

    /**
     * OpenpaydProvider constructor.
     * @throws Exception
     */
    public function __construct()
    {
        try {
            $this->settings = PaymentServiceSettings::get(self::key());

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
            throw new Exception($e->getMessage());
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
     * @param PaymentOrder $payment
     * @param object $inputData
     * @return mixed
     */
    public function charge(PaymentOrder $payment, object $inputData): mixed
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

        if ($transactionType == self::TRANSACTION_TYPE_PAYIN) {
            //  retrieve payment and update status
            // TODO find a way to access webhook metadata.
            $payment = PaymentOrder::where('type', PaymentOrder::TYPE_PAYIN)
                ->where('id', $webhookPayload["metadata"]['orderId'])
                ->where('document_id', $webhookPayload["metadata"]['documentId'])
                ->where('check_code', $webhookPayload["metadata"]['check_code'])
                ->where('gateway', self::key())
                ->first();

            if (!$payment) {
                return [
                    'type' => 'danger',
                    'message' => 'Payment Order not found in Payment Microservice database'
                ];
            }

            $payment->status = $transactionStatus;

            // $payment->payload = $paymentData;
            $payment->save();

            // Return result
            return [
                'status' => 'success',
                'payment_order_id' => $payment->id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'service' => $payment->service,
                'user_id' => $payment->user_id,
                'payment_completed' => (self::TRANSACTION_STATUS_COMPLETED === $payment->status),
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
}
