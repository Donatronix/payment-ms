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
    const STATUS_PAYMENT_INTENT_CREATED = 1;

    // Occurs when a PaymentIntent has started processing.
    const STATUS_PAYMENT_INTENT_PROCESSING = 2;

    // Occurs when a PaymentIntent has successfully completed payment.
    const STATUS_PAYMENT_INTENT_SUCCEEDED = 3;

    // Occurs when a PaymentIntent is canceled.
    const STATUS_PAYMENT_INTENT_CANCELED = 4;

    // Occurs when a PaymentIntent has failed the attempt to create a payment method or a payment.
    const STATUS_PAYMENT_INTENT_PAYMENT_FAILED = 3;

    // Occurs when funds are applied to a customer_balance PaymentIntent and the ‘amount_remaining’ changes.
    const STATUS_PAYMENT_INTENT_PARTIALLY_FUNDED = 4;

    // Occurs when a PaymentIntent transitions to requires_action state
    const STATUS_PAYMENT_INTENT_REQUIRES_ACTION = 6;

    // Occurs when a PaymentIntent has funds to be captured.
    // Check the amount_capturable property on the PaymentIntent to determine the amount that can be captured.
    // You may capture the PaymentIntent with an amount_to_capture value up to the specified amount. Learn more about capturing PaymentIntents.
    const STATUS_PAYMENT_INTENT_AMOUNT_CAPTURABLE_UPDATED = 7;



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
     * @return int
     */
    public static function newOrderStatus(): int
    {
        return self::STATUS_PAYMENT_INTENT_CREATED;
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
            $order->status = self::STATUS_ORDER_REQUIRES_PAYMENT_METHOD;
            $order->service_document_id = $stripeDocument->id;
            $order->service_document_type = $stripeDocument->object;
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

            // Get charge detail
            $charge = [];
            $charges = collect($intent->charges['data']);
            if ($charges->count() == 1) {
                $charge = (object)$charges->first();
            }else{
                // loop $charges->map(....);
            }

            $result = [
                'status' => self::STATUS_PAYMENT_INTENT_PROCESSING
            ];

            if ($charge->status == 'succeeded' && $charge->paid) {
                $type = $charge->payment_method_details['type'];
                $detail = $charge->payment_method_details[$type];

                if ($type == 'card') {
                    $result['card_last4'] = $detail['last4'];
                    $result['card_brand'] = $detail['brand'];
                }

                $result['payment_method'] = $type;
                $result['status'] = self::STATUS_PAYMENT_INTENT_SUCCEEDED;
                $result['transaction_id'] = $charge->balance_transaction;
            }

            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
