<?php

namespace App\Services\Payments;

use App\Contracts\PaymentSystemContract;
use App\Models\Currency;
use App\Models\PaymentOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

class PaypalManager implements PaymentSystemContract
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
     * @var \PayPalCheckoutSdk\Core\PayPalHttpClient
     */
    private $gateway;

    /**
     * BitPayManager constructor.
     */
    public function __construct()
    {
        if(config('payments.paypal.mode') === 'sandbox'){
            $environment = new SandboxEnvironment(
                config('payments.paypal.sandbox.client_id'),
                config('payments.paypal.sandbox.client_secret')
            );
        }else{
            $environment = new ProductionEnvironment(
                config('payments.paypal.live.client_id'),
                config('payments.paypal.live.client_secret')
            );
        }

        $this->gateway = new PayPalHttpClient($environment);
    }

    public static function type(): string
    {
        return 'paypal';
    }

    public static function name(): string
    {
        return 'PayPal';
    }

    public static function description(): string
    {
        return 'PayPal payment is..';
    }

    /**
     * Wrapper for create paypal invoice for charge money
     *
     * @param array $data
     *
     * @return mixed|void
     */
    public function createInvoice(array $data): array
    {
        try {
            // Create check code
            $checkCode = PaymentOrder::getCheckCode();

            $currency_id = $data['currency']['id'] ?? Currency::$currencies[mb_strtoupper($data['currency']['code'])];

            // Create internal order
            $paymentOrder = PaymentOrder::create([
                'user_id' => $data['user_id'] ?? Auth::user()->getAuthIdentifier(),
                'amount' => $data['amount'],
                'currency_id' => $currency_id,
                'check_code' => $checkCode,
                'type' => PaymentOrder::TYPE_ORDER_INVOICE,
                'gateway' => self::type()
            ]);

            // Create new charge
            $request = new OrdersCreateRequest();
            $request->prefer('return=representation');
            $request->body = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'description' => 'Charge Balance for Sumra User',
                        'custom_id' => $checkCode,
                        'check_code' => $checkCode,
                        'invoice_id' => $paymentOrder->id,
                        'amount' => [
                            'value' => $data['amount'],
                            'currency_code' => $data['currency']['code'],
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
                    'return_url' => env('PAYMENTS_REDIRECT_URL'),
                    'cancel_url' => env('PAYMENTS_REDIRECT_URL'),
                ]
            ];
            $chargeObj = $this->gateway->execute($request);

            // Update order data
            $paymentOrder->document_id = $chargeObj->result->id;
            $paymentOrder->status = self::STATUS_ORDER_CREATED;
            $paymentOrder->save();

            $invoiceUrl = '';
            foreach($chargeObj->result->links as $link){
                if($link->rel === 'approve'){
                    $invoiceUrl = $link->href;
                }
            }

            return [
                'status' => 'success',
                'invoice_url' => $invoiceUrl
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => sprintf("Unable to create an order. Error: %s \n", $e->getMessage())
            ];
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed|void
     */
    public function handlerWebhookInvoice(Request $request)
    {
        // Check content type
        if (!$request->isJson()) {
            http_response_code(400);
        }

        // Check sender
        if (!Str::contains($request->header('User-Agent'), 'PayPal')) {
            http_response_code(400);
        }

        // Get invoice data
        $orderData = $request->get('resource', null);
        if ($orderData === null) {
            http_response_code(400);
        }

        // Find order
        $order = PaymentOrder::where('type', PaymentOrder::TYPE_ORDER_INVOICE)
            ->where('document_id', $orderData->id)
            ->where('check_code', $orderData->purchase_units[0]->custom_id)
            ->where('gateway', self::type())
            ->first();

        if (!$order) {
            http_response_code(400);
        }

        $status = 'STATUS_ORDER_' . mb_strtoupper($orderData->status);
        $order->status = self::$$status;
        $order->payload = $orderData;
        $order->save();

        // Send response code
        http_response_code(200);
    }
}
