<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Models\PaymentOrder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
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
     * @var string
     */
    private object $settings;

    /**
     * StripeProvider constructor.
     * @param Object $settings
     * @throws Exception
     */
    public function __construct(object $settings)
    {
        $this->settings = $settings;

        try {
            // Set your secret API key
            $this->service = null;
            Stripe::setApiKey($this->settings->secret_key);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @return string
     */
    public static function key(): string
    {
        return 'stripe';
    }

    /**
     * @return string
     */
    public static function title(): string
    {
        return 'Stripe payment service provider';
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
    public static function newOrderStatus(): int
    {
        return self::STATUS_ORDER_REQUIRES_PAYMENT_METHOD;
    }

    /**
     * Wrapper for create payment order for charge money
     *
     * @param PaymentOrder $order
     * @param object $inputData
     * @return array
     * @throws ApiErrorException
     */
    public function charge(PaymentOrder $order, object $inputData): array
    {
        try {
            // Support payment methods from different currency
            $methods = [
                'usd' => [
                    'alipay',
                    'acss_debit',
                    'affirm',
                    'afterpay_clearpay',
                    // 'card_present', // mode stripe terminal with physical card
                    'klarna',
                    //'link',
                    'us_bank_account',
                    'wechat_pay'
                ],
                'eur' => [
                    'bancontact',
                    'eps',
                    'giropay',
                    'ideal',
                    'p24',
                    'sepa_debit',
                    'sofort'
                ],
                'gbp' => [
                    'bacs_debit'
                ]
            ];

            // do low case for currency
            $currency = mb_strtolower($inputData->currency);

            // Create a PaymentIntent with amount and currency
            $stripeDocument = PaymentIntent::create([
                'amount' => $inputData->amount * 100,
                'currency' => $currency,
//                'automatic_payment_methods' => [
//                    'enabled' => true,
//                ],
                'payment_method_types' => ['card'] + $methods[$currency],
                'metadata' => [
                    'code' => $order->check_code,
                    'payment_order_id' => $order->id
                ],
//                'confirm' => true,
//                'return_url' => $inputData->redirect_url ?? null
            ]);

            // Update payment order
            $order->status = self::STATUS_ORDER_REQUIRES_PAYMENT_METHOD;
            $order->document_id = $stripeDocument->id;
            $order->save();

            // Return result
            return [
                'public_key' => $this->settings->public_key,
                'clientSecret' => $stripeDocument->client_secret,
            ];
        } catch (Exception $e) {
            throw $e;
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
                $this->settings->webhook_secret
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
//                    'payment_order_id' => $order->id,
//                    'service' => $order->service,
//                    'amount' => $order->amount,
//                    'currency' => $order->currency,
//                    'user_id' => $order->user_id,
//                    'payment_completed' => (self::STATUS_ORDER_SUCCEEDED === $order->status),
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
