<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Models\PaymentOrder;
use BitPaySDK\Client;
use BitPaySDK\Exceptions\BitPayException;
use BitPaySDK\Model\Invoice\Invoice;
use BitPaySDK\Tokens;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class BitpayProvider
 * @package App\Services\PaymentServiceProviders
 */
class BitpayProvider implements PaymentServiceContract
{
    // New Invoice statuses
    const STATUS_INVOICE_NEW = 1000;

    // To notify merchant that an invoice has reached the status paid
    const STATUS_INVOICE_PAID_IN_FULL = 1003;

    // To notify a merchant that an invoice has expired without being paid
    const STATUS_INVOICE_EXPIRED = 1004;

    // To notify merchant that an invoice has reached the status confirmed
    const STATUS_INVOICE_CONFIRMED = 1005;

    // To notify merchant that an invoice has reached the status completed
    const STATUS_INVOICE_COMPLETED = 1006;

    // To notify merchant that an invoice has reached the status invalid
    const STATUS_INVOICE_FAILED_TO_CONFIRM = 1013;

    // To notify a merchant that a refund request has been successfully processed
    const STATUS_INVOICE_REFUND_COMPLETE = 1016;

    /**
     * @var Client
     */
    private Client $service;

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
            // Initialize with separate variables and Private Key stored in file.
            $this->service = Client::create()->withData(
                ucfirst($this->settings->is_develop),
                storage_path($this->settings->key_path),
                new Tokens(
                    $this->settings->api_token_merchant, //merchant
                    $this->settings->api_token_payroll //payroll
                ),
                $this->settings->private_key_password
            );
        } catch (BitPayException $e) {
            throw $e;
        }
    }

    /**
     * @return string
     */
    public static function key(): string
    {
        return 'bitpay';
    }

    /**
     * @return string
     */
    public static function title(): string
    {
        return 'BitPay payment service provider';
    }

    /**
     * @return string
     */
    public static function description(): string
    {
        return 'BitPay: Buy Crypto Without Fees | Store, Swap & Spend Bitcoin';
    }

    /**
     * Wrapper for create payment order for charge money
     *
     * @param PaymentOrder $order
     * @param object $inputData
     * @return array
     * @throws BitPayException
     */
    public function charge(PaymentOrder $order, object $inputData): array
    {
        try {
            // Set invoice detail
            $invoice = new Invoice($inputData->amount, $inputData->currency);

            $invoice->setOrderId($order->id);

            $invoice->setFullNotifications(true);
            $invoice->setExtendedNotifications(true);
            $invoice->setNotificationURL(config('settings.api.payments') . '/' . self::key());

            $invoice->setRedirectURL($inputData->redirect_url ?? '');

            $invoice->setPosData(json_encode(['code' => $order->check_code]));
            $invoice->setItemDesc("Charge user wallet balance");

            // Send data to bitpay and get created invoice
            $chargeObj = $this->service->createInvoice($invoice);

            // Update payment order
            $order->status = self::STATUS_INVOICE_NEW;
            $order->service_document_id = $chargeObj->getId();
            $order->save();

            // Return result
            return [
                'payment_order_url' => $chargeObj->getURL()
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Request $request
     *
     * @return array|string[]
     */
    public function handlerWebhook(Request $request): array
    {
        Log::info($request);
        // Check event property
        if (!$request->has('event')) {
            return [
                'type' => 'danger',
                'message' => 'Empty / Incorrect event data'
            ];
        }

        // Get event data
        $paymentData = $request->get('data', null);
        if ($paymentData === null) {
            return [
                'type' => 'danger',
                'message' => 'Empty / Incorrect event data'
            ];
        }

        $paymentData['posData'] = json_decode($paymentData['posData']);

        // Find Payment Order
        $order = PaymentOrder::where('type', PaymentOrder::TYPE_CHARGE)
            ->where('id', $paymentData['orderId'])
            ->where('service_document_id', $paymentData['id'])
            ->where('check_code', $paymentData['posData']->code)
            ->where('service_key', self::key())
            ->first();

        if (!$order) {
            return [
                'type' => 'danger',
                'message' => 'Payment Order not found in Payment Microservice database'
            ];
        }

        $order->status = $request->event['code'];

        // $order->payload = $paymentData;
        $order->save();

        // Return result
        return [
            'type' => 'success',
            'service_key' => self::key(),
            'payment_order_id' => $order->id,
            'amount' => $order->amount,
            'currency' => $order->currency,
            'service' => $order->service,
            'user_id' => $order->user_id,
            'payment_completed' => (self::STATUS_INVOICE_COMPLETED === $order->status),
        ];
    }

    /**
     * @param object $payload
     * @return mixed
     */
    public function checkTransaction(object $payload): mixed
    {
        // TODO: Implement checkTransaction() method.
    }
}
