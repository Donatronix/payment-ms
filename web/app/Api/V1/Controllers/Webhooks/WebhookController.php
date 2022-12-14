<?php

namespace App\Api\V1\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\LogError;
use App\Models\LogRequest;
use App\Models\PaymentOrder;
use App\Services\PaymentServiceManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class WebhookController
 *
 * @package App\Api\V1\Controllers
 */
class WebhookController extends Controller
{
    /**
     * Invoices webhook
     *
     * @OA\Post(
     *     path="/webhooks/{gateway}",
     *     description="Webhooks notifications from payment service provider",
     *     tags={"Webhooks"},
     *
     *     @OA\Parameter(
     *         name="gateway",
     *         description="Payment service provider key",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *              default="bitpay"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     )
     * )
     *
     * @param Request $request
     * @param string $gateway
     *
     * @return mixed
     */
    public function handlerWebhook(Request $request, string $gateway): mixed
    {
        // Check content type
        if (!$request->isJson()) {
            LogError::create([
                'source' => 'webhook',
                'service' => $gateway,
                'message' => 'Is not JSON content',
                'payload' => $request->getContent()
            ]);

            return response('Is not JSON content', 400);
        }

        try {
            // Init manager
            $system = PaymentServiceManager::getInstance($gateway);

            // Handle webhook
            $result = $system->handlerWebhook($request);



        } catch (Exception $e) {
            Log::info($e->getMessage());

            if (env("APP_DEBUG", 0)) {
                Log::error($result);
            }

            exit;
        }



        // If error, logging and send status 400
        if ($result['type'] === 'danger') {
            LogError::create([
                'source' => 'webhook',
                'service' => $gateway,
                'message' => $result['message'],
                'payload' => $result['payload']
            ]);

            return response($result['message'], 400);
        }

        // Get document data
        $orderData = $stripeDocument->metadata;
        if (!$orderData || !is_object($orderData)) {
            return [
                'type' => 'danger',
                'message' => 'No order data'
            ];
        }


        // Find order
        $order = PaymentOrder::where('id', $orderData->payment_order)
            ->where('check_code', $orderData->check_code)
            ->first();

        if (!$order) {
            \Log::error("Order not found: " . $payload);
            return [
                'type' => 'danger',
                'message' => 'Order not found'
            ];
        }

        $status = 'STATUS_ORDER_' . mb_strtoupper($stripeDocument->payment_status);
        if (!defined("self::{$status}")) {
            \Log::error("Status error: " . $payload);
            return [
                'type' => 'danger',
                'message' => 'Status error: ' . mb_strtoupper($stripeDocument->payment_status)
            ];
        }

        $order->status = intval(constant("self::{$status}"));
        // $order->payload = $request;
        $order->save();

        // Logging success request content
        try {
            LogRequest::create([
                'source' => 'webhook',
                'service' => $gateway,
                'payload' => $request->all(),
            ]);
        } catch (\Exception $e) {
            Log::info('Log of invoice failed: ' . $e->getMessage());
        }

        // If paid complete, than send notification
        if($result['payment_completed']){
            \PubSub::publish('rechargeBalanceWebhook', $result, $result['service']);
        }

        // Send status 200 OK
        return response('');
    }
}
