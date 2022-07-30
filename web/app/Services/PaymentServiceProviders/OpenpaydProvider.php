<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Models\PaymentOrder;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Helpers\PaymentServiceSettings as PaymentSetting;

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
     * @var  \GuzzleHttp\Client
     */
    private $openPaydClient;

    public function __construct()
    {
    }

    public function getAccessToken()
    {
        try {

            $this->openPaydClient = new Client(['base_uri' => PaymentSetting::settings('openpayd_url')]);

            $username = PaymentSetting::settings('openpayd_username');
            $password = PaymentSetting::settings('openpayd_password');
            $salt = $username.":".$password;

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

            $response = $this->openPaydClient->post("oauth/token?grant_type=client_credentials", $payload);

            return $response;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public static function name(): string
    {
        return 'OpenPayd';
    }

    public static function description(): string
    {
        return 'OpenPayd is..';
    }

    public static function service(): string
    {
        return 'openpayd';
    }

    /**
     * @return integer
     */
    public static function getNewStatusId()
    {
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
     * @param PaymentOrder $payment
     * @param object $inputData
     *
     * @return mixed
     */
    public function createInvoice(PaymentOrder $payment, object $inputData): mixed
    {
        // TODO not yet provided by openpayd
    }

    /**
     * @param \Illuminate\Http\Request $request
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
            $payment = PaymentOrder::where('type', PaymentOrder::TYPE_INVOICE)
                ->where('id', $webhookPayload["metadata"]['orderId'])
                ->where('document_id', $webhookPayload["metadata"]['documentId'])
                ->where('check_code', $webhookPayload["metadata"]['check_code'])
                ->where('gateway', self::service())
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
         $pubKeyPath = PaymentSetting::settings('openpayd_public_key_path');

         if ($signature == hash_hmac_file('sha256', $data, $pubKeyPath)){

            return true;
        }

        return false;
    }
}
