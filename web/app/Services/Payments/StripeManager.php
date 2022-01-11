<?php

namespace App\Services\Payments;

use App\Contracts\PaymentSystemContract;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;

class StripeManager implements PaymentSystemContract
{
    private array $gateway;
    private $public_key;
    private $secret_key;

    const STATUS_ORDER_REQUIRES_PAYMENT_METHOD = 1;
    const STATUS_ORDER_REQUIRES_CONFIRMATION = 2;
    const STATUS_ORDER_REQUIRES_ACTION = 3;
    const STATUS_ORDER_PROCESSING = 4;
    const STATUS_ORDER_REQUIRES_CAPTURE = 5;
    const STATUS_ORDER_CANCELED = 6;
    const STATUS_ORDER_SUCCEEDED = 7;
    const STATUS_ORDER_PAID = 8;
    const STATUS_ORDER_UNPAID = 9;
    const STATUS_ORDER_NO_PAYMENT_REQUIRED = 10;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->gateway = [];
        $this->public_key = env('STRIPE_PUBLIC_KEY', 'pk_test_**');
        $this->secret_key = env('STRIPE_SECRET_KEY', 'sk_test_**');
    }

    public static function gateway(): string
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

    public static function getNewStatusId(): int
    {
        return self::STATUS_ORDER_REQUIRES_PAYMENT_METHOD;
    }

    /**
     * Wrapper for create Stripe invoice for charge money
     *
     * @param array $data
     *
     * @return array
     */
    public function createInvoice(array $data): array
    {
        try {
            // Set your secret API key
            Stripe::setApiKey($this->secret_key);

            // Create internal order
            $payment = Payment::create([
                'type' => Payment::TYPE_INVOICE,
                'gateway' => self::gateway(),
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'service' => $data['service'],
                'user_id' => Auth::user()->getAuthIdentifier(),
                'status' => self::STATUS_ORDER_REQUIRES_PAYMENT_METHOD
            ]);

            $checkout_session = \Stripe\Checkout\Session::create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'amount' => $data['amount'] * 100,
                    'currency' => $data['currency'],
                    'quantity' => 1,
                    'name' => 'Wallet Charge',
                ]],
                'metadata' => [
                    'payment_order' => $payment->id,
                    'check_code' => $payment->check_code,
                ],
                'success_url' => env('PAYMENTS_WEBHOOK_URL') . '?success=true',
                'cancel_url' => env('PAYMENTS_WEBHOOK_URL') . '?canceled=true'
            ]);

            // Update order data
            $payment->document_id = $checkout_session->id;
            $payment->save();

            // Return result
            return [
                'type' => 'success',
                'title' => 'Stripe checkout payment session creating',
                'message' => "Session successfully created",
                'data' => [
                    'gateway' => self::gateway(),
                    'payment_id' => $payment->id,
                    'session_id' => $checkout_session->id,
                    'session_url' => $checkout_session->url,
                    'public_key' => $this->public_key
                ]
            ];
        } catch (\Exception $e) {
            return [
                'type' => 'danger',
                'title' => 'Stripe checkout payment session creating',
                'message' => sprintf("Unable to create an session. Error: %s \n", $e->getMessage()),
                'data' => []
            ];
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function handlerWebhookInvoice(Request $request): array
    {
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');
        if (isset($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
            $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        } else {
            $sig_header = "";
        }
        $payload = @file_get_contents('php://input');
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            \Log::error("Invalid payload: " . $payload);
            return [
                'type' => 'danger',
                'message' => 'Unexpected value error'
            ];
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            \Log::error("Invalid signature: " . $payload);
            if (env("APP_DEBUG", 0)) {
                $event = (object)[
                    "type" => $request["type"],
                    "data" => (object)[
                        "object" => (object)[
                            "metadata" => (object)[
                                "payment_order" => $request["data"]["object"]["metadata"]["payment_order"],
                                "check_code" => $request["data"]["object"]["metadata"]["check_code"],
                            ],
                            "payment_status" => $request["data"]["object"]["payment_status"],
                        ]
                    ]
                ];
            } else return [
                'type' => 'danger',
                'message' => 'Signature check error'
            ];
        }

        \Log::info($request);
        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
            case 'checkout.session.async_payment_succeeded':
            case 'checkout.session.async_payment_failed':
                $paymentIntent = $event->data->object; // contains a StripePaymentIntent
                break;
            default:
                \Log::error("Unexpected event type: " . $payload);
                return [
                    'type' => 'danger',
                    'message' => 'Unexpected event type'
                ];
        }

        // Get invoice data
        $orderData = $paymentIntent->metadata;
        if (!$orderData || !is_object($orderData)) {
            return [
                'type' => 'danger',
                'message' => 'No order data'
            ];
        }

        http_response_code(200);

        // Find order
        $payment = Payment::where('id', $orderData->payment_order)
            ->where('check_code', $orderData->check_code)
            ->first();

        if (!$payment) {
            \Log::error("Order not found: " . $payload);
            return [
                'type' => 'danger',
                'message' => 'Order not found'
            ];
        }

        $status = 'STATUS_ORDER_' . mb_strtoupper($paymentIntent->payment_status);
        if (!defined("self::{$status}")) {
            \Log::error("Status error: " . $payload);
            return [
                'type' => 'danger',
                'message' => 'Status error: ' . mb_strtoupper($paymentIntent->payment_status)
            ];
        }

        $payment->status = intval(constant("self::{$status}"));
        // $payment->payload = $request;
        $payment->save();

        // Return result
        return [
            'status' => 'success',
            'payment_id' => $payment->id,
            'service' => $payment->service,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'user_id' => $payment->user_id,
            'payment_completed' => (self::STATUS_ORDER_SUCCEEDED === $payment->status),
        ];
    }
}
