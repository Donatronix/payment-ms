<?php

namespace App\Services\Payments;

use App\Contracts\PaymentSystemContract;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;

class StripeManager implements PaymentSystemContract
{
    private $gateway;

    const STATUS_ORDER_REQUIRES_PAYMENT_METHOD = 1;
    const STATUS_ORDER_REQUIRES_CONFIRMATION = 2;
    const STATUS_ORDER_REQUIRES_ACTION = 3;
    const STATUS_ORDER_PROCESSING = 4;
    const STATUS_ORDER_REQUIRES_CAPTURE = 5;
    const STATUS_ORDER_CANCELED = 6;
    const STATUS_ORDER_SUCCEEDED = 7;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->gateway = [];
        $this->publisher_key = env('STRIPE_PUBLISHER_KEY', 'pk_test_ZZGWT0pQRB47mX9Qfg9vY3q000tnr0ycgG');
        $this->secret_key = env('STRIPE_SECRET_KEY', 'sk_test_mwiW1DmmnkYFzhOUfspqLxH000wGfzYpGY');
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
     * Wrapper for create bitpay invoice for charge money
     *
     * @param array $data
     *
     * @return array
     */
    public function createInvoice(array $data)
    {
        try {
            Stripe::setApiKey($this->secret_key);
            $checkCode = Payment::getCheckCode();

            // Create internal order
            $payment = Payment::create([
                'type' => Payment::TYPE_INVOICE,
                'gateway' => self::type(),
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'check_code' => $checkCode,
                'service' => $data['service'],
                'user_id' => Auth::user()->getAuthIdentifier(),
                'status' => self::STATUS_ORDER_REQUIRES_PAYMENT_METHOD
            ]);

            $checkout_session = \Stripe\Checkout\Session::create([
                'success_url' => env('PAYMENTS_WEBHOOK_URL').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => env('PAYMENTS_WEBHOOK_URL').'?session_id={CHECKOUT_SESSION_ID}',
                'payment_method_types' => ['card'],
                'mode' => 'payment',
                'line_items' => [[
                    'amount' => $data['amount']*100,
                    'currency' => $data['currency'],
                    'quantity' => 1,
                    'name' => 'Wallet Charge',
                ]],
                'metadata' => [
                    'payment_order' => $payment->id,
                    'check_code' => $checkCode,
                ]
            ]);

            // Update order data
            $payment->document_id = $checkout_session['id'];
            $payment->save();

            return [
                'status' => 'success',
                'gateway' => self::type(),
                'session_id' => $checkout_session['id'],
                'stripe_pubkey' => $this->publisher_key,
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
     * @return mixed
     */
    public function handlerWebhookInvoice(Request $request)
    {
        \Log::info($request);
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $request, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            return [
                'status' => 'error',
                'message' => 'Unexpected value error'
            ];
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return [
                'status' => 'error',
                'message' => 'Signature check error'
            ];
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
            case 'checkout.session.async_payment_succeeded':
            case 'checkout.session.async_payment_failed':
                $paymentIntent = $event->data->object; // contains a StripePaymentIntent
                break;
            default:
                return [
                    'status' => 'error',
                    'message' => 'Unexpected event type'
                ];
        }

        // Get invoice data
        $orderData = $paymentIntent->metadata;
        if (!$orderData) {
            return [
                'status' => 'error',
                'message' => 'No order data'
            ];
        }

        http_response_code(200);

        // Find order
        $payment = Payment::where('id', $orderData->payment_order)
            ->where('check_code', $orderData->check_code)
            ->first();

        if (!$payment) {
            return [
                'status' => 'error',
                'message' => 'Order not found'
            ];
        }

        $status = 'STATUS_ORDER_' . mb_strtoupper($paymentIntent->status);
        if(!isset(self::$$status)) {
            return [
                'status' => 'error',
                'message' => 'Status error: '.mb_strtoupper($paymentIntent->status)
            ];
        }
        $payment->status = self::$$status;
        $payment->payload = $request;
        $payment->save();
        return [
            'status' => 'success',
            'payment_id' => $payment->id,
            'service' => $payment->service,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'payment_status' => ''
        ];
    }

}
