<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LogPaymentRequest;
use App\Models\LogPaymentRequestError;
use App\Models\LogPaymentWebhook;
use App\Models\LogPaymentWebhookError;
use App\Services\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Class WebhookController
 *
 * @package App\Api\V1\Controllers
 */
class WebhookController extends Controller
{
    /**
     * @var string
     */
    private const RECEIVER_LISTENER = 'rechargeBalanceWebhook';

    /**
     * Invoices webhook
     *
     * @OA\Post(
     *     path="/webhooks/{gateway}/invoices",
     *     description="Webhooks Notifications about invoices",
     *     tags={"Webhooks"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\Parameter(
     *         name="gateway",
     *         description="Payment gateway",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *              default="bitpay"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $gateway
     *
     * @return mixed
     */
    public function handlerWebhookInvoice(Request $request, string $gateway): mixed
    {
        // Check content type
        if (!$request->isJson()) {
            LogPaymentWebhookError::create([
                'gateway' => $gateway,
                'payload' => $request->getContent()
            ]);

            http_response_code(400);
            exit();
        }

        // Init manager
        try{
            $system = Payment::getInstance($gateway);
        } catch(\Exception $e){
            Log::info($e->getMessage());

            exit;
        }

        // Handle webhook
        $result = $system->handlerWebhookInvoice($request);

        // If error, logging and send status 400
        if ($result['status'] === 'error') {
            LogPaymentWebhookError::create([
                'gateway' => $gateway,
                'payload' => $result['message']
            ]);

            http_response_code(400);
            exit();
        }

        // Logging success request content
        try {
            LogPaymentWebhook::create([
                'gateway' => $gateway,
                'payment_id' => $result['payment_id'],
                'payload' => $request->all(),
            ]);
        } catch (\Exception $e) {
            Log::info('Log of invoice failed: ' . $e->getMessage());
        }

        // If paid complete, than send notification
        if($result['payment_completed']){
            \PubSub::transaction(function () {})->publish(self::RECEIVER_LISTENER, $result, $result['service']);
        }

        // Send status OK
        http_response_code(200);
    }
}
