<?php

namespace App\Services\Payments;

use App\Contracts\PaymentSystemContract;
use App\Models\Currency;
use App\Models\PaymentOrder;
use BitPaySDK\Client;
use BitPaySDK\Exceptions\BitPayException;
use BitPaySDK\Model\Invoice\Invoice;
use BitPaySDK\Tokens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                config('payments.bitpay.environment'),
                config('payments.bitpay.private_key.path'),
                new Tokens(
                    config('payments.bitpay.api_tokens.merchant'), //merchant
                    config('payments.bitpay.api_tokens.payroll') //payroll
                ),
                config('payments.bitpay.private_key.password')
            );
        } catch (BitPayException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function type(): string
    {
        return 'bitpay';
    }

    public static function name(): string
    {
        return 'BitPay';
    }

    public static function description(): string
    {
        return 'BitPay is..';
    }

    /**
     * Wrapper for create bitpay invoice for charge money
     *
     * @param array $data
     *
     * @return \BitPaySDK\Model\Invoice\Invoice|string
     */
    public function createInvoice(array $data)
    {
        try {
            // Create check code
            $checkCode = PaymentOrder::getCheckCode();

            $currency_id = $data['currency']['id'] ?? Currency::$currencies[mb_strtoupper($data['currency']['code'])];

            // Create internal order
            $transaction = PaymentOrder::create([
                'type' => PaymentOrder::TYPE_ORDER_INVOICE,
                'gateway' => self::type(),
                'amount' => $data['amount'],
                'currency_id' => $currency_id,
                'check_code' => $checkCode,
                'user_id' => $data['user_id'] ?? Auth::user()->getAuthIdentifier(),
                'status' => self::STATUS_INVOICE_NEW
            ]);

            // Set invoice detail
            $invoice = new Invoice($data['amount'], $data['currency']['code']);
            $invoice->setOrderId($transaction->id);
            $invoice->setFullNotifications(true);
            $invoice->setExtendedNotifications(true);
            $invoice->setTransactionSpeed("medium");

            $url = env('PAYMENTS_WEBHOOK_URL','https://bitpay.com') . '/bitpay/invoices';

            $invoice->setNotificationURL($url);
            $invoice->setRedirectURL(config('payments.bitpay.redirect_url'));
            $invoice->setPosData(json_encode(['code' => $checkCode]));
            $invoice->setItemDesc("Charge Balance for Sumra User");

            // Send data to bitpay and get created invoice
            $chargeObj = $this->gateway->createInvoice($invoice);

            // Update order data
            $transaction->document_id = $chargeObj->getId();
            $transaction->save();

            return [
                'status' => 'success',
                'title' => 'Create Invoice',
                'message' => 'Invoice successfully created',
                'invoice_url' => $chargeObj->getURL()
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'title' => 'Create Invoice',
                'message' => sprintf("Unable to create an invoice. Error: %s \n", $e->getMessage())
            ];
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed|void
     */
    public function handlerWebhookInvoice(Request $request)
    {
        // Check content type
        if (!$request->isJson()) {
            http_response_code(400);
            //return response()->json('Input data incorrect', 400);
        }

        if(!$request->has('event')){
            http_response_code(400);
        }

        // Get invoice data
        $invoiceData = $request->get('data', null);
        if ($invoiceData === null) {
            http_response_code(400);
            //return response()->json('Input data incorrect 2', 400);
        }

        // Find order
        $order = PaymentOrder::where('type', PaymentOrder::TYPE_ORDER_INVOICE)
            ->where('document_id', $invoiceData['id'])
            ->where('check_code', $invoiceData['posData']['code'])
            ->where('gateway', self::type())
            ->first();

        if (!$order) {
            http_response_code(400);
            //return response()->json('Order not found', 400);
        }

        $order->status = $request->event['code'];
        $order->payload = $invoiceData;
        $order->save();
    }

//    public function getInvoices()
//    {
//        $invoices = null;
//
//        try {
//            $date = new DateTime();
//            $today = $date->format("Y-m-d");
//            $sevenDaysAgo = $date->modify('-30 day')->format("Y-m-d");
//
//            $invoices = $this->gateway->getInvoices($sevenDaysAgo, $today, null, null, 50);
//
//            dd($invoices);
//        } catch (Exception $e) {
//            $e->getTraceAsString();
//            $e->getMessage();
//        }
//    }
}
