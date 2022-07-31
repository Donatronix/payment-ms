<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Helpers\PaymentServiceSettings as PaymentSetting;
use App\Models\PaymentOrder;
use BitPaySDK\Client;
use BitPaySDK\Exceptions\BitPayException;
use BitPaySDK\Model\Invoice\Invoice;
use BitPaySDK\Tokens;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
     * BitPayProvider constructor.
     */
    public function __construct()
    {
        try {
            // Initialize with separate variables and Private Key stored in file.
            $this->service = Client::create()->withData(
                PaymentSetting::settings('bitpay_environment'),
                storage_path(PaymentSetting::settings('bitpay_key_path')),
                new Tokens(
                    PaymentSetting::settings('bitpay_api_token_merchant'), //merchant
                    PaymentSetting::settings('bitpay_api_token_payroll') //payroll
                ),
                PaymentSetting::settings('bitpay_private_key_password')
            );
        } catch (BitPayException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @return string
     */
    public static function service(): string
    {
        return 'bitpay';
    }

    /**
     * @return string
     */
    public static function name(): string
    {
        return 'BitPay Payment Provider';
    }

    /**
     * @return string
     */
    public static function description(): string
    {
        return 'BitPay: Buy Crypto Without Fees | Store, Swap & Spend Bitcoin';
    }

    /**
     * @return int
     */
    public static function newStatus(): int
    {
        return self::STATUS_INVOICE_NEW;
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
            // Set invoice detail
            $invoice = new Invoice($inputData->amount, $inputData->currency);

            $invoice->setOrderId($payment->id);

            $invoice->setFullNotifications(true);
            $invoice->setExtendedNotifications(true);
            $invoice->setNotificationURL( config('settings.api.payments') . '/' . self::service());

            $invoice->setRedirectURL($inputData->redirect_url ?? null);

            $invoice->setPosData(json_encode(['code' => $payment->check_code]));
            $invoice->setItemDesc("Charge user wallet balance");

            // Send data to bitpay and get created invoice
            $chargeObj = $this->service->createInvoice($invoice);

            // Update payment order
            $payment->status = self::STATUS_INVOICE_NEW;
            $payment->document_id = $chargeObj->getId();
            $payment->save();

            // Return result
            return [
                'gateway' => self::service(),
                'payment_order_id' => $payment->id,
                'payment_order_url' => $chargeObj->getURL()
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
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
        $payment = PaymentOrder::where('type', PaymentOrder::TYPE_PAYIN)
            ->where('id', $paymentData['orderId'])
            ->where('document_id', $paymentData['id'])
            ->where('check_code', $paymentData['posData']->code)
            ->where('gateway', self::service())
            ->first();

        if (!$payment) {
            return [
                'type' => 'danger',
                'message' => 'Payment Order not found in Payment Microservice database'
            ];
        }

        $payment->status = $request->event['code'];

        // $payment->payload = $paymentData;
        $payment->save();

        // Return result
        return [
            'status' => 'success',
            'gateway' => self::service(),
            'payment_order_id' => $payment->id,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'service' => $payment->service,
            'user_id' => $payment->user_id,
            'payment_completed' => (self::STATUS_INVOICE_COMPLETED === $payment->status),
        ];
    }
}
