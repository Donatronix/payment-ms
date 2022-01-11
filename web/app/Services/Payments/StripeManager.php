<?php

namespace App\Services\Payments;

use App\Contracts\PaymentSystemContract;
use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Stripe;

class StripeManager implements PaymentSystemContract
{
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
     * @var array
     */
    private array $gateway;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->gateway = [];

        // Set your secret API key
        Stripe::setApiKey(config('payments.stripe.secret_key'));
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
     * @param Payment $payment
     * @param object $inputData
     * @return array
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function charge(Payment $payment, object $inputData): array
    {
        try {
            // Create a PaymentIntent with amount and currency
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $inputData->amount,
                'currency' => 'eur',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            // Update order data
            $payment->status = self::STATUS_ORDER_REQUIRES_PAYMENT_METHOD;
            $payment->document_id = $paymentIntent->id;
            $payment->save();

            // Return result
            return [
                'type' => 'success',
                'title' => 'Stripe checkout payment session creating',
                'message' => "Session successfully created",
                'data' => [
                    'gateway' => self::gateway(),
                    'payment_id' => $payment->id,
                    'session_id' => $paymentIntent->id,
                    'public_key' => config('payments.stripe.public_key'),
                    'clientSecret' => $paymentIntent->client_secret,
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
     * Wrapper for create Stripe invoice for charge money
     *
     * @param Payment $payment
     * @param object $inputData
     * @return array
     */
    public function createInvoice(Payment $payment, object $inputData): array
    {
        try {
            // Create checkout session
            $checkout_session = \Stripe\Checkout\Session::create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'amount' => $inputData->amount * 100,
                    'currency' => $inputData->currency,
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
            $payment->status = self::STATUS_ORDER_REQUIRES_PAYMENT_METHOD;
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
                    'public_key' => config('payments.stripe.public_key')
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
        $payload = @file_get_contents('php://input');

        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null,
                config('payments.stripe.webhook_secret')
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
