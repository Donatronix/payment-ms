<?php

namespace App\Services\Payments;

use App\Contracts\PaymentSystemContract;
use App\Models\Currency;
use App\Models\PaymentOrder;
use CoinbaseCommerce\ApiClient;
use CoinbaseCommerce\Resources\Charge;
use Illuminate\Http\Request;
use CoinbaseCommerce\Webhook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CoinbaseManager implements PaymentSystemContract
{
    /**
     * Charge / Invoice statuses
     */
    // New charge is created
    const STATUS_CHARGE_CREATED = 1;

    //	Charge has been detected but has not been confirmed yet
    const STATUS_CHARGE_PENDING = 2;

    //	Charge has been confirmed and the associated payment is completed
    const STATUS_CHARGE_CONFIRMED = 3;

    //	Charge failed to complete
    const STATUS_CHARGE_FAILED = 4;

    //	Charge received a payment after it had been expired
    const STATUS_CHARGE_DELAYED = 5;

    //	Charge has been resolved
    const STATUS_CHARGE_RESOLVED = 6;

    /**
     * @var int[]
     */
    public static $statuses = [
        self::STATUS_CHARGE_CREATED,
        self::STATUS_CHARGE_PENDING,
        self::STATUS_CHARGE_CONFIRMED,
        self::STATUS_CHARGE_FAILED,
        self::STATUS_CHARGE_DELAYED,
        self::STATUS_CHARGE_RESOLVED,
    ];

    /**
     * @var \CoinbaseCommerce\ApiClient
     */
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
            $checkCode = PaymentOrder::getCheckCode();

            // Create new charge
            $chargeObj = Charge::create([
                'name' => 'Charge Balance',
                'description' => 'Charge Balance for Sumra User',
                'pricing_type' => 'fixed_price',
                'local_price' => [
                    'amount' => $data['amount'],
                    'currency' => $data['currency']['code']
                ],
                'metadata' => [
                    'code' => $checkCode,
                ],
                'redirect_url' => config('payments.coinbase.redirect_url'),
                'cancel_url' => config('payments.coinbase.cancel_url')
            ]);

            $currency_id = $data['currency']['id'] ?? Currency::$currencies[mb_strtoupper($data['currency']['code'])];

            // Create internal order
            $transaction = PaymentOrder::create([
                'user_id' => $data['user_id'] ?? Auth::user()->getAuthIdentifier(),
                'document_id' => $chargeObj->id,
                //'document_data' => ['code' => $chargeObj->code],
                'amount' => $data['amount'],
                'currency_id' => $currency_id,
                'check_code' => $checkCode,
                'type' => PaymentOrder::TYPE_ORDER_INVOICE,
                'gateway' => self::type(),
                'status' => self::STATUS_CHARGE_CREATED
            ]);

            return [
                'status' => 'success',
                'title' => 'Create Invoice',
                'message' => 'Invoice successfully created',
                'invoice_url' => $chargeObj->hosted_url
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
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
        $order = PaymentOrder::where('type', PaymentOrder::TYPE_ORDER_INVOICE)
            ->where('document_id', $invoiceData->id)
            ->where('check_code', $invoiceData->metadata->code ?? null)
            ->where('gateway', self::type())
            ->first();

        if (!$order) {
            http_response_code(400);
            //return response()->json('Order not found', 400);
        }

        $status = 'STATUS_' . mb_strtoupper(Str::snake(str_replace(':', ' ', $event->type)));
        $order->status = self::$$status;
        $order->payload = $invoiceData;
        $order->save();
    }
}
