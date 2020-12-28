<?php

namespace App\Services\Payments;

use App\Contracts\PaymentSystemContract;
use App\Models\Currency;
use App\Models\PaymentOrderPaypal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

class PaypalManager implements PaymentSystemContract
{
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
            $checkCode = PaymentOrderPaypal::getCheckCode();

            // Create internal order
            $paymentOrder = PaymentOrderPaypal::create([
                'user_id' => $data['user_id'] ?? Auth::user()->getAuthIdentifier(),
                'amount' => $data['amount'],
                'currency_id' => Currency::$currencies[mb_strtoupper($data['currency'])],
                'check_code' => $checkCode,
                'type' => PaymentOrderPaypal::TYPE_ORDER_INVOICE,
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
                            'currency_code' => $data['currency'],
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
            $paymentOrder->status = PaymentOrderPaypal::STATUS_ORDER_CREATED;
            $paymentOrder->save();

            $invoiceUrl = '';
            foreach($chargeObj->result->links as $link){
                if($link->rel === 'approve'){
                    $invoiceUrl = $link->href;
                }
            }

            return [
                'status' => 'success',
                'title' => 'Create Invoice',
                'message' => 'Invoice successfully created',
                'invoice_url' => $invoiceUrl
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'title' => 'Create Invoice',
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
        $order = PaymentOrderPaypal::where('type', PaymentOrderPaypal::TYPE_ORDER_INVOICE)
            ->where('document_id', $orderData->id)
            ->where('check_code', $orderData->purchase_units[0]->custom_id)
            ->where('gateway', self::type())
            ->first();

        if (!$order) {
            http_response_code(400);
        }

        $status = 'STATUS_ORDER_' . mb_strtoupper($orderData->status);
        $order->status = PaymentOrderPaypal::$$status;
        $order->response = $orderData;
        $order->save();

        // Send response code
        http_response_code(200);
    }
}
