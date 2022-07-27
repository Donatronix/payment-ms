<?php

namespace App\Services\PaymentServices;

use App\Contracts\PaymentSystemContract;
use App\Helpers\PaymentServiceSettings as PaymentSetting;
use App\Models\PaymentOrder;
use BitPaySDK\Client;
use BitPaySDK\Exceptions\BitPayException;
use BitPaySDK\Model\Invoice\Invoice;
use BitPaySDK\Tokens;
use Exception;
use Illuminate\Http\Request;

class BitpayManager implements PaymentSystemContract
{
    /**
     * Invoice statuses
     */
    // New
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
     * @var \BitPaySDK\Client
     */
    private $gateway;

    /**
     * BitPayManager constructor.
     */
    public function __construct()
    {
        try {
            // Initialize with separate variables and Private Key stored in file.
            $this->gateway = Client::create()->withData(
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

    public static function name(): string
    {
        return 'BitPay';
    }

    public static function description(): string
    {
        return 'BitPay is..';
    }

    public static function gateway(): string
    {
        return 'bitpay';
    }

    public static function getNewStatusId(): int
    {
        return self::STATUS_INVOICE_NEW;
    }

    /**
     * Wrapper for create bitpay invoice for charge money
     *
     * @param PaymentOrder $payment
     * @param object $inputData
     * @return array
     */
    public function createInvoice(PaymentOrder $payment, object $inputData): array
    {
        try {
            // Set invoice detail
            $invoice = new Invoice($inputData->amount, $inputData->currency);

            $invoice->setOrderId($payment->id);

            $invoice->setFullNotifications(true);
            $invoice->setExtendedNotifications(true);
            $invoice->setNotificationURL(PaymentSetting::settings('bitpay_payment_webhook_url') . '/bitpay/invoices');

            $invoice->setRedirectURL($inputData->redirect_url); // PaymentSetting::settings('bitpay_redirect_url')

            $invoice->setPosData(json_encode(['code' => $payment->check_code]));
            $invoice->setItemDesc("Charge user wallet balance");

            // Send data to bitpay and get created invoice
            $chargeObj = $this->gateway->createInvoice($invoice);

            // Update payment transaction data
            $payment->status = self::STATUS_INVOICE_NEW;
            $payment->document_id = $chargeObj->getId();
            $payment->save();

            return [
                'type' => 'success',
                'gateway' => self::gateway(),
                'payment_order_id' => $payment->id,
                'invoice_url' => $chargeObj->getURL()
            ];
        } catch (Exception $e) {
            return [
                'type' => 'danger',
                'message' => sprintf("Unable to create an invoice. Error: %s \n", $e->getMessage())
            ];
        }
    }

    public function charge(PaymentOrder $payment, object $inputData): mixed
    {
        try {
            // Set invoice detail
            $invoice = new Invoice($inputData->amount, $inputData->currency);
            $invoice->setOrderId($payment->id);
            $invoice->setFullNotifications(true);
            $invoice->setExtendedNotifications(true);
            $invoice->setNotificationURL(PaymentSetting::settings('bitpay_payment_webhook_url') . '/bitpay/invoices');

            $invoice->setRedirectURL($inputData->redirect_url); // PaymentSetting::settings('bitpay_redirect_url')

            $invoice->setPosData(json_encode(['code' => $payment->check_code]));
            $invoice->setItemDesc("Charge user wallet balance");

            // Send data to bitpay and get created invoice
            $chargeObj = $this->gateway->createInvoice($invoice);

            // Update payment transaction data
            $payment->status = self::STATUS_INVOICE_NEW;
            $payment->document_id = $chargeObj->getId();
            $payment->save();

            return [
                'type' => 'success',
                'gateway' => self::gateway(),
                'payment_order_id' => $payment->id,
                'invoice_url' => $chargeObj->getURL()
            ];
        } catch (Exception $e) {
            return [
                'type' => 'danger',
                'message' => sprintf("Unable to create an invoice. Error: %s \n", $e->getMessage())
            ];
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array|string[]
     */
    public function handlerWebhook(Request $request): array
    {
        \Log::info($request);
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

        // Find payment transaction
        $payment = PaymentOrder::where('type', PaymentOrder::TYPE_PAYIN)
            ->where('id', $paymentData['orderId'])
            ->where('document_id', $paymentData['id'])
            ->where('check_code', $paymentData['posData']->code)
            ->where('gateway', self::gateway())
            ->first();

        if (!$payment) {
            return [
                'type' => 'danger',
                'message' => 'Payment transaction not found in Payment Microservice database'
            ];
        }

        $payment->status = $request->event['code'];

        // $payment->payload = $paymentData;
        $payment->save();

        // Return result
        return [
            'status' => 'success',
            'payment_order_id' => $payment->id,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'service' => $payment->service,
            'user_id' => $payment->user_id,
            'payment_completed' => (self::STATUS_INVOICE_COMPLETED === $payment->status),
        ];
    }
}
