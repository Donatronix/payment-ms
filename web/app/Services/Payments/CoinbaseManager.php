<?php

namespace App\Services\Payments;

use App\Contracts\IPaymentSystemContract;
use App\Models\Currency;
use App\Models\PaymentOrderCoinbase;
use CoinbaseCommerce\ApiClient;
use CoinbaseCommerce\Resources\Charge;
use Illuminate\Http\Request;
use CoinbaseCommerce\Webhook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CoinbaseManager implements IPaymentSystemContract
{
    private $gateway;

    /**
     * CoinbaseManager constructor.
     */
    public function __construct()
    {
        try {
            $this->gateway = ApiClient::init(config('payments.coinbase.api_key'));
            $this->gateway->setTimeout(3);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function type(): string
    {
        return 'coinbase';
    }

    public static function name(): string
    {
        return 'Coinbase';
    }

    public static function description(): string
    {
        return 'Coinbase Commerce is..';
    }

    /**
     * Wrapper for create coinbase invoice for charge money
     *
     * @param array $data
     *
     * @return mixed|void
     */
    public function createInvoice(array $data)
    {
        try {
            // Create check code
            $checkCode = PaymentOrderCoinbase::getCheckCode();

            // Create new charge
            $chargeObj = Charge::create([
                'name' => 'Charge Balance',
                'description' => 'Charge Balance for Sumra User',
                'pricing_type' => 'fixed_price',
                'local_price' => [
                    'amount' => $data['amount'],
                    'currency' => $data['currency']
                ],
                'metadata' => [
                    'code' => $checkCode,
                ],
                'redirect_url' => config('payments.coinbase.redirect_url'),
                'cancel_url' => config('payments.coinbase.cancel_url')
            ]);

            // Create internal order
            $transaction = PaymentOrderCoinbase::create([
                'user_id' => Auth::user()->getAuthIdentifier(),
                'document_id' => $chargeObj->id,
                //'document_data' => ['code' => $chargeObj->code],
                'amount' => $data['amount'],
                'currency_id' => Currency::$currencies[mb_strtoupper($data['currency'])],
                'check_code' => $checkCode,
                'type' => PaymentOrderCoinbase::TYPE_ORDER_INVOICE,
                'gateway' => self::type(),
                'status' => PaymentOrderCoinbase::STATUS_CHARGE_CREATED
            ]);

            return [
                'type' => 'success',
                'title' => 'Create Invoice',
                'message' => 'Invoice successfully created',
                'invoice_url' => $chargeObj->hosted_url
            ];
        } catch (\Exception $e) {
            return [
                'type' => 'error',
                'title' => 'Create Invoice',
                'message' => sprintf("unable to create a charge. Error: %s \n", $e->getMessage())
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

        try {
            $event = Webhook::buildEvent(
                trim(file_get_contents('php://input')),
                $request->header('X-Cc-Webhook-Signature', null),
                config('payments.coinbase.webhook_key')
            );

            http_response_code(200);
        } catch (\Exception $exception) {
            http_response_code(400);
            //return response()->json($exception->getMessage(), 400);
        }

        // Get invoice data
        $invoiceData = $event->data;
        if ($invoiceData === null) {
            http_response_code(400);
            //return response()->json('Input data incorrect 2', 400);
        }

        // Find order
        $order = PaymentOrderCoinbase::where('type', PaymentOrderCoinbase::TYPE_ORDER_INVOICE)
            ->where('document_id', $invoiceData->id)
            ->where('check_code', $invoiceData->metadata->code ?? null)
            ->where('gateway', self::type())
            ->first();

        if (!$order) {
            http_response_code(400);
            //return response()->json('Order not found', 400);
        }

        $status = 'STATUS_' . mb_strtoupper(Str::snake(str_replace(':', ' ', $event->type)));
        $order->status = PaymentOrderCoinbase::$$status;
        $order->response = $invoiceData;
        $order->save();
    }
}
