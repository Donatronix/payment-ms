<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Helpers\PaymentServiceSettings as PaymentSetting;
use App\Models\PaymentOrder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

class PaypalProvider implements PaymentServiceContract
{
    /**
     * Order / Invoice statuses
     */
    // The order was created with the specified context
    const STATUS_ORDER_CREATED = 1;

    // The order was saved and persisted. The order status continues to be in progress until
    // a capture is made with final_capture = true for all purchase units within the order.
    const STATUS_ORDER_SAVED = 2;

    // The customer approved the payment through the PayPal wallet or another form of guest
    // or unbranded payment. For example, a card, bank account, or so on.
    const STATUS_ORDER_APPROVED = 3;

    // All purchase units in the order are voided.
    const STATUS_ORDER_VOIDED = 4;

    // The payment was authorized or the authorized payment was captured for the order
    const STATUS_ORDER_COMPLETED = 5;

    // The order requires an action from the payer (e.g. 3DS authentication).
    // Redirect the payer to the "rel":"payer-action" HATEOAS link returned as part
    // of the response prior to authorizing or capturing the order.
    const STATUS_ORDER_PAYER_ACTION_REQUIRED = 6;

    /**
     * @var PayPalHttpClient
     */
    private PayPalHttpClient $service;

    /**
     * PaypalProvider constructor.
     */
    public function __construct()
    {
        if (PaymentSetting::settings('paypal_mode') === 'sandbox') {
            $environment = new SandboxEnvironment(
                PaymentSetting::settings('paypal_sandbox_client_id'),
                PaymentSetting::settings('paypal_sandbox_client_secret')
            );
        } else {
            $environment = new ProductionEnvironment(
                PaymentSetting::settings('paypal_live_client_id'),
                PaymentSetting::settings('paypal_live_client_secret')
            );
        }

        $this->service = new PayPalHttpClient($environment);
    }

    /**
     * @return string
     */
    public static function service(): string
    {
        return 'paypal';
    }

    /**
     * @return string
     */
    public static function name(): string
    {
        return 'PayPal Payment Provider';
    }

    /**
     * @return string
     */
    public static function description(): string
    {
        return 'PayPal is a Simple and Safer Way to Pay and Get Paid';
    }

    /**
     * @return int
     */
    public static function newStatus(): int
    {
        return self::STATUS_ORDER_CREATED;
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
            $request = new OrdersCreateRequest();
            $request->prefer('return=representation');
            $request->body = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'description' => 'Charge Balance for Sumra User',
                        'custom_id' => $payment->check_code,
                        'invoice_id' => $payment->id,
                        'amount' => [
                            'value' => $inputData->amount,
                            'currency_code' => $inputData->currency,
                        ],
                        'payment_instruction' => [
                            'disbursement_mode' => 'INSTANT'
                        ],
                        'soft_descriptor' => 'SUMRA.NET',
                    ]
                ],
                'application_context' => [
                    'brand_name' => 'INFINITY SUMRA NET',
                    'locale' => 'en-US',
                    'landing_page' => 'NO_PREFERENCE',
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                    'return_url' => $inputData->redirect_url ?? null,
                    'cancel_url' => $inputData->cancel_url ?? null,
                ]
            ];
            $chargeObj = $this->service->execute($request);

            // Update Payment Order data
            $payment->status = self::STATUS_ORDER_CREATED;
            $payment->document_id = $chargeObj->result->id;
            $payment->save();

            $invoiceUrl = '';
            foreach ($chargeObj->result->links as $link) {
                if ($link->rel === 'approve') {
                    $invoiceUrl = $link->href;
                }
            }

            return [
                'gateway' => self::service(),
                'payment_order_id' => $payment->id,
                'invoice_url' => $invoiceUrl
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
        Log::info($request);

        // Check sender
        if (!Str::contains($request->header('User-Agent'), 'PayPal')) {
            return [
                'type' => 'danger',
                'message' => 'Payload was sent not from PayPal'
            ];
        }

        // Get event data
        $paymentData = $request->get('resource', null);
        if ($paymentData === null) {
            return [
                'type' => 'danger',
                'message' => 'Empty / Incorrect event data'
            ];
        }

        // Find Payment Order
        $payment = PaymentOrder::where('type', PaymentOrder::TYPE_PAYIN)
            ->where('id', $paymentData["purchase_units"][0]["invoice_id"])
            ->where('document_id', $paymentData["id"])
            ->where('check_code', $paymentData["purchase_units"][0]["custom_id"])
            ->where('gateway', self::service())
            ->first();

        if (!$payment) {
            return [
                'type' => 'danger',
                'message' => 'Payment Order not found in Payment Microservice database'
            ];
        }

        // Update Payment Order status
        $status = 'STATUS_ORDER_' . mb_strtoupper($paymentData["status"]);
        $payment->status = constant("self::{$status}");
        // $payment->payload = $paymentData;
        $payment->save();

        // Return result
        return [
            'status' => 'success',
            'payment_order_id' => $payment->id,
            'service' => $payment->service,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'user_id' => $payment->user_id,
            'payment_completed' => (self::STATUS_ORDER_COMPLETED === $payment->status),
        ];
    }
}
