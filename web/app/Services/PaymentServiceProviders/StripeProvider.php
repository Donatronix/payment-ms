<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Helpers\PaymentServiceSettings as PaymentSetting;
use App\Models\PaymentOrder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

/**
 * Class StripeProvider
 * @package App\Services\PaymentServiceProviders
 */
class StripeProvider implements PaymentServiceContract
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
     * @var
     */
    private $service;

    /**
     * StripeProvider constructor.
     */
    public function __construct()
    {
        $this->service = null;

        // Set your secret API key
        Stripe::setApiKey(PaymentSetting::settings('stripe_secret_key'));
    }

    /**
     * @return string
     */
    public static function service(): string
    {
        return 'stripe';
    }

    /**
     * @return string
     */
    public static function name(): string
    {
        return 'Stripe Payment Provider';
    }

    /**
     * @return string
     */
    public static function description(): string
    {
        return 'Stripe Payment Processing Platform for the Internet';
    }

    /**
     * @return int
     */
    public static function newStatus(): int
    {
        return self::STATUS_ORDER_REQUIRES_PAYMENT_METHOD;
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
            // Create a PaymentIntent with amount and currency
            $stripeDocument = PaymentIntent::create([
                'amount' => $inputData->amount * 100,
                'currency' => mb_strtolower($inputData->currency),
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'payment_method_types' => [
                    'acss_debit',
                    'affirm',

                    'afterpay_clearpay',
                    'alipay',

                    'au_becs_debit',
                    'bacs_debit',
                    'bancontact',
                    'blik',
                    'boleto',

                    'card',

                    'card_present',
                    'eps',
                    'fpx',
                    'giropay',
                    'grabpay',
                    'ideal',

                    'klarna',

                    'konbini',
                    'link',
                    'oxxo',
                    'p24',
                    'paynow',
                    'promptpay',
                    'sepa_debit',
                    'sofort',
                    'us_bank_account',

                    'wechat_pay'
                ],
                'metadata' => [
                    'code' => $payment->check_code,
                    'payment_order_id' => $payment->id
                ],
                'return_url' => $payment->redirect_url ?? null
            ]);

            // Update payment order
            $payment->status = self::STATUS_ORDER_REQUIRES_PAYMENT_METHOD;
            $payment->document_id = $stripeDocument->id;
            $payment->save();

            // Return result
            return [
                'gateway' => self::service(),
                'payment_order_id' => $payment->id,
                'session_id' => $stripeDocument->id,
                'public_key' => PaymentSetting::settings('stripe_public_key'),
                'clientSecret' => $stripeDocument->client_secret,
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
        $event = null;
        $payload = $request->getContent();

        if (env("APP_DEBUG", 0)) {
            Log::info($request->headers);
            Log::info($payload);
        }

        try {
            $event = Webhook::constructEvent(
                $payload,
                ($_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null),
                PaymentSetting::settings('stripe_webhook_secret')
            );
        } catch (UnexpectedValueException $e) {
            // Invalid payload
            $result = [
                'type' => 'danger',
                'message' => "Stripe Webhook: Invalid payload: " . $e->getMessage(),
                'payload' => $payload
            ];

            if (env("APP_DEBUG", 0)) {
                Log::error($result);
            }

            return $result;
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            $result = [
                'type' => 'danger',
                'message' => "Stripe Webhook: Invalid signature: " . $e->getMessage(),
                'payload' => $payload
            ];

            if (env("APP_DEBUG", 0)) {
                Log::error($result);
            }

            return $result;
        }

        if (env("APP_DEBUG", 0)) {
            Log::info('### START EVENT OBJECT ###');
            Log::info($event);
            Log::info('### FINISH EVENT OBJECT ###');
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
            case 'checkout.session.async_payment_succeeded':
            case 'checkout.session.async_payment_failed':

            case 'payment_intent.amount_capturable_updated':
            case 'payment_intent.canceled':
            case 'payment_intent.created':
            case 'payment_intent.payment_failed':
            case 'payment_intent.processing':
            case 'payment_intent.requires_action':
            case 'payment_intent.succeeded':
                // Read contains a StripePaymentIntent
                $stripeDocument = $event->data->object;

                if (env("APP_DEBUG", 0)) {
                    Log::info($stripeDocument);
                }

                // Return result
                return [
                    'type' => 'success',
                    'stripeDocument' => $stripeDocument
//                    'payment_order_id' => $payment->id,
//                    'service' => $payment->service,
//                    'amount' => $payment->amount,
//                    'currency' => $payment->currency,
//                    'user_id' => $payment->user_id,
//                    'payment_completed' => (self::STATUS_ORDER_SUCCEEDED === $payment->status),
                ];

            default:
                $result = [
                    'type' => 'info',
                    'message' => 'Stripe Webhook: Received unsupported event type ' . $event->type,
                    'payload' => $payload
                ];

                Log::info($result);

                return $result;
        }
    }
}
