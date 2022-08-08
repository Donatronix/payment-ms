<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Models\PaymentOrder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\Webhook;

/**
 * Class StripeProvider
 * @package App\Services\PaymentServiceProviders
 */
class StripeProvider implements PaymentServiceContract
{
    // Occurs when a new PaymentIntent is created.
    const STATUS_PAYMENT_INTENT_CREATED = 'created';

    // Occurs when a PaymentIntent has started processing.
    const STATUS_PAYMENT_INTENT_PROCESSING = 'processing';

    // Occurs when a PaymentIntent has successfully completed payment.
    const STATUS_PAYMENT_INTENT_SUCCEEDED = 'succeeded';

    // Occurs when a PaymentIntent is canceled.
    const STATUS_PAYMENT_INTENT_CANCELED = 'canceled';

    // Occurs when a PaymentIntent has failed the attempt to create a payment method or a payment.
    const STATUS_PAYMENT_INTENT_PAYMENT_FAILED = 'failed';

    // Occurs when funds are applied to a customer_balance PaymentIntent and the ‘amount_remaining’ changes.
    const STATUS_PAYMENT_INTENT_PARTIALLY_FUNDED = 'partially_funded';

    // Occurs when a PaymentIntent transitions to requires_action state
    const STATUS_PAYMENT_INTENT_REQUIRES_ACTION = 'processing';

    // Occurs when a PaymentIntent has funds to be captured.
    // Check the amount_capturable property on the PaymentIntent to determine the amount that can be captured.
    // You may capture the PaymentIntent with an amount_to_capture value up to the specified amount. Learn more about capturing PaymentIntents.
    const STATUS_PAYMENT_INTENT_AMOUNT_CAPTURABLE_UPDATED = 'failed';

    /**
     * @var array
     */
    private static array $statuses = [
        'created' => self::STATUS_PAYMENT_INTENT_CREATED,
        'processing' => self::STATUS_PAYMENT_INTENT_PROCESSING,
        'partially_funded' => self::STATUS_PAYMENT_INTENT_PARTIALLY_FUNDED,
        'requires_action' => self::STATUS_PAYMENT_INTENT_REQUIRES_ACTION,
        'requires_payment_method' => self::STATUS_PAYMENT_INTENT_REQUIRES_ACTION,
        'amount_capturable_updated' => self::STATUS_PAYMENT_INTENT_AMOUNT_CAPTURABLE_UPDATED,
        'failed' => self::STATUS_PAYMENT_INTENT_PAYMENT_FAILED,
        'succeeded' => self::STATUS_PAYMENT_INTENT_SUCCEEDED,
        'canceled' => self::STATUS_PAYMENT_INTENT_CANCELED
    ];

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
            $this->service = new \Stripe\StripeClient($this->settings->secret_key);
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
            // Search and create customer
            $customerSearch = $this->service->customers->search([
                'query' => sprintf("metadata['user_id']: '%s'", Auth::user()->getAuthIdentifier()),
            ]);

            // If not found, then create new one
            if (empty($customerSearch['data'])) {
                $customer = $this->service->customers->create([
                    'description' => '',
                    'metadata' => [
                        'user_id' => Auth::user()->getAuthIdentifier()
                    ]
                ]);
            } else {
                $customer = $customerSearch['data'][0];
            }

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
            $stripeDocument = $this->service->paymentIntents->create([
                'customer' => $customer->id,
                'amount' => $inputData->amount * 100,
                'currency' => $currency,
//                'automatic_payment_methods' => [
//                    'enabled' => true,
//                ],
                'payment_method_types' => ['card'] + $methods[$currency],
                'metadata' => [
                    'check_code' => $order->check_code,
                    'payment_order_id' => $order->id,
                    'user_id' => Auth::user()->getAuthIdentifier()
                ],
//                'confirm' => true,
//                'return_url' => $inputData->redirect_url ?? null
            ]);

            // Update payment order
            $order->status = PaymentOrder::$statuses[self::STATUS_PAYMENT_INTENT_PROCESSING];
            $order->service_document_id = $stripeDocument->id;
            $order->service_document_type = $stripeDocument->object;
            $order->save();

            // Return result
            return [
                'status' => self::STATUS_PAYMENT_INTENT_PROCESSING,
                'payment_intent' => $stripeDocument->id,
                'clientSecret' => $stripeDocument->client_secret,
                'public_key' => $this->settings->public_key,
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
                'message' => "Stripe Webhook invalid payload: " . $e->getMessage(),
            ];

            return $result;
        } catch (SignatureVerificationException $e) {
            throw new Exception("Stripe Webhook invalid signature: " . $e->getMessage());
        }

        if (env("APP_DEBUG", 0)) {
            Log::info('### START EVENT OBJECT ###');
            Log::info($event);
            Log::info('### FINISH EVENT OBJECT ###');
        }

        // Handle the event
        switch ($event->type) {
            /**
             *
             */
            case 'checkout.session.completed':
            case 'checkout.session.async_payment_succeeded':
            case 'checkout.session.async_payment_failed':
                break;

            /**
             * Payment Intent
             */
            case 'payment_intent.amount_capturable_updated':
            case 'payment_intent.canceled':
            case 'payment_intent.created':
            case 'payment_intent.partially_funded':
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
//                    'payment_completed' => (self::STATUS_PAYMENT_INTENT_SUCCEEDED === $order->status),
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

    /**
     * @param object $payload
     * @return mixed
     */
    public function checkTransaction(object $payload): mixed
    {
        try {
            // Get stripe payment intent data
            $intent = (object)$this->service->paymentIntents->retrieve(
                $payload->meta['payment_intent']
            )->toArray();
            // "payment_intent_client_secret": "pi_3LTVJ6KkrmrXUD8m1LiQNqQD_secret_blXgoRlk1I49X6uLsH97uyva5"

            // If missing payment method, then intent is continue processing
            if($intent->status === 'requires_payment_method'){
                return [
                    'status' => self::$statuses[$intent->status],
                ];
            }

            // Get charge detail
            $charge = [];
            $charges = collect($intent->charges['data']);
            if ($charges->count() == 1) {
                $charge = (object)$charges->first();
            } else {
                // loop $charges->map(....);
            }

            $result = [
                'status' => self::$statuses[$charge->status],
                'transaction_id' => $charge->balance_transaction
            ];

            if ($charge->status == 'succeeded' && $charge->paid) {
                $payment_method = $charge->payment_method_details['type'];
                $payment_detail = $charge->payment_method_details[$payment_method];

                $result['payment_method'] = $payment_method;
                $result['payer_name'] = $charge->billing_details['name'] ?? '';
                $result['payer_email'] = $charge->billing_details['email'] ?? '';

                if ($payment_method == 'card') {
                    $result['card_last4'] = $payment_detail['last4'];
                    $result['card_brand'] = $payment_detail['brand'];
                }
            }

            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
