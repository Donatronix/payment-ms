<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LogPaymentWebhook;
use App\Models\LogPaymentWebhookError;
use App\Services\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Class PaymentController
 *
 * @package App\Api\V1\Controllers
 */
class PaymentController extends Controller
{
    /**
     * Invoices webhook
     *
     * @OA\Post(
     *     path="/v1/payments/webhooks/{gateway}/invoices",
     *     description="Webhooks Notifications about invoices",
     *     tags={"Payments Webhooks"},
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
    public function handlerWebhookInvoice(Request $request, string $gateway)
    {
        // Check content type
        if (!$request->isJson()) {
            LogPaymentWebhookError::create([
                'gateway' => $gateway,
                'payload' => $request->all()
            ]);

            http_response_code(400);
        }

        try {
            LogPaymentWebhook::create([
                'gateway' => $gateway,
                'payload' => $request->all(),
            ]);
        } catch (\Exception $e) {
            Log::info('Log of invoice failed: ' . $e->getMessage());
        }

        // Init manager
        try{
            $system = Payment::getServiceManager($gateway);
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

        // Send payment request to payment gateway
        \PubSub::transaction(function () {})->publish('rechargeBalanceWebhook', $result, $result['service']);

        // Send status OK
        http_response_code(200);
    }

    /**
     * Recharge wallet balance
     *
     * @OA\Post(
     *     path="/v1/payments/payments/charge",
     *     description="Recharge wallet balance",
     *     tags={"Payments"},
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
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="gateway",
     *                 description="Payment gateway",
     *                 type="string",
     *                 default="bitpay"
     *             ),
     *             @OA\Property(
     *                 property="amount",
     *                 description="The amount of money replenished to the balance",
     *                 type="integer",
     *                 default=1000
     *             ),
     *             @OA\Property(
     *                 property="currency",
     *                 description="Currency of balance",
     *                 type="string",
     *                 default="GBP"
     *             )
     *             @OA\Property(
     *                 property="service",
     *                 description="Target service: infinityWallet | divitExchange",
     *                 type="string"
     *             )
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
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \ReflectionException
     */
    public function recharge(Request $request)
    {
        $inputData = $request->all();

        // Validate input
        $validation = Validator::make($inputData, [
            'gateway' => 'string|required',
            'amount' => 'integer|required',
            'currency' => 'string|required',
            'service' => 'string|required',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'error' => $validation->errors()->toJson()
            ], 400);
        }

        // Write log
        try {
            $log = new LogPaymentRequest;
            $log->gateway = $inputData['gateway'];
            $log->payload = $inputData;
            $log->save();
        } catch (\Exception $e) {
            Log::info('Log of invoice failed: ' . $e->getMessage());
        }

        // Init manager
        $system = Payment::getServiceManager($inputData['gateway']);

        if ($system === null)
            return response()->json([
                'success' => false,
                'message' => 'No class for ' . $inputData['gateway'],
            ], 400);

        // Create invoice
        $result = $system->createInvoice($inputData);

        // Return response
        $code = 200;
        if ($result['status'] === 'error') {
            $code = 400;

            $log = new LogPaymentRequestError;
            $log->error = var_export($result, true);
            $log->save();
        }

        // Return result
        return response()->json($result, $code);
    }
}
