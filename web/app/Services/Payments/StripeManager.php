<?php

namespace App\Services\Payments;

use App\Contracts\IPaymentSystemContract;
use App\Models\Currency;
use App\Models\PaymentOrder;
use App\Models\PaymentOrderPaypal;
use App\Models\PaymentOrderStripe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

class StripeManager implements IPaymentSystemContract
{
    private $gateway;

    /**
     * BitPayManager constructor.
     */
    public function __construct()
    {
        $this->gateway = [];
    }

    public static function type(): string
    {
        return 'stripe';
    }

    public static function name(): string
    {
        return 'Stripe';
    }

    public static function description(): string
    {
        return 'Stripe is..';
    }

    /**
     * Wrapper for create bitpay invoice for charge money
     *
     * @param array $data
     *
     * @return array
     */
    public function createInvoice(array $data)
    {
        try {
            // Create check code
            $currency_id = $data['currency']['id'] ?? Currency::$currencies[mb_strtoupper($data['currency']['code'])];

            // Create internal order
            $paymentOrder = PaymentOrder::create([
                'user_id' => $data['user_id'] ?? Auth::user()->getAuthIdentifier(),
                'amount' => $data['amount'],
                'currency_id' => $currency_id,
                'check_code' => '',
                'type' => PaymentOrder::TYPE_ORDER_INVOICE,
                'gateway' => self::type()
            ]);

            $checkout_session = \Stripe\Checkout\Session::create([
                'success_url' => env('PAYMENTS_WEBHOOK_URL').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => env('PAYMENTS_WEBHOOK_URL').'?session_id={CHECKOUT_SESSION_ID}',
                'payment_method_types' => ['card'],
                'mode' => 'payment',
                'line_items' => [[
                    'amount' => $data['amount'],
                    'currency' => $data['currency']['code'],
                ]],
                'metadata' => [
                    'payment_order' => $paymentOrder->id
                ]
            ]);

            // Update order data
            $paymentOrder->document_id = $checkout_session['id'];
            $paymentOrder->status = self::STATUS_ORDER_CREATED;
            $paymentOrder->save();

            return [
                'status' => 'success',
                'title' => 'Create Invoice',
                'message' => 'Invoice successfully created',
                'invoice_url' => '',
                'session_id' => $checkout_session['id'],
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
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }

        // Handle the event
        // TODO: find ALL needed event type
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object; // contains a StripePaymentIntent
                handlePaymentIntentSucceeded($paymentIntent);
                break;
            // ... handle other event types
            default:
                http_response_code(400);
//                echo 'Received unknown event type ' . $event->type;
        }

        // Get invoice data
        $orderData = $paymentIntent->metadata;
        if (!$orderData) {
            http_response_code(400);
        }

        http_response_code(200);

        // Find order
        $order = PaymentOrder::where('type', PaymentOrder::TYPE_ORDER_INVOICE)
            ->where('document_id', $orderData->id)
            ->where('gateway', self::type())
            ->first();

        if (!$order) {
            http_response_code(400);
        }

        $status = 'STATUS_ORDER_' . mb_strtoupper($paymentIntent->status);
        $order->status = self::$$status;
//        $order->payload = $orderData;
        $order->save();
    }

}
