<?php

namespace App\Services\Payments;

use App\Contracts\IPaymentSystemContract;
use App\Models\Currency;
use App\Models\PaymentOrderBitPay;
use BitPaySDK\Client;
use BitPaySDK\Exceptions\BitPayException;
use BitPaySDK\Model\Invoice\Invoice;
use BitPaySDK\Tokens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BitpayManager implements IPaymentSystemContract
{
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
            $checkCode = PaymentOrderBitPay::getCheckCode();

            // Create internal order
            $transaction = PaymentOrderBitPay::create([
                'user_id' => Auth::user()->getAuthIdentifier(),
                'amount' => $data['amount'],
                'currency_id' => Currency::$currencies[mb_strtoupper($data['currency'])],
                'check_code' => $checkCode,
                'type' => PaymentOrderBitPay::TYPE_ORDER_INVOICE,
                'gateway' => self::type(),
                'status' => PaymentOrderBitPay::STATUS_INVOICE_NEW
            ]);

            // Set invoice detail
            $invoice = new Invoice($data['amount'], $data['currency']);

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
                'type' => 'success',
                'title' => 'Create Invoice',
                'message' => 'Invoice successfully created',
                'invoice_url' => $chargeObj->getURL()
            ];
        } catch (\Exception $e) {
            return [
                'type' => 'error',
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

        // Get invoice data
        $invoiceData = $request->get('data', null);
        if ($invoiceData === null) {
            http_response_code(400);
            //return response()->json('Input data incorrect 2', 400);
        }

        // Find order
        $order = PaymentOrderBitPay::where('type', PaymentOrderBitPay::TYPE_ORDER_INVOICE)
            ->where('document_id', $invoiceData['id'])
            ->where('check_code', $invoiceData['posData']['code'])
            ->where('gateway', self::type())
            ->first();

        if (!$order) {
            http_response_code(400);
            //return response()->json('Order not found', 400);
        }

        $order->status = $request->event['code'];
        $order->response = $invoiceData;
        $order->save();
    }

    /**
     *
     */
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
