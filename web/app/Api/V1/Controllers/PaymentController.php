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
 * Class PaymentController
 *
 * @package App\Api\V1\Controllers
 */
class PaymentController extends Controller
{
    /**
     * @var string
     */
    private const RECEIVER_LISTENER = 'rechargeBalanceWebhook';

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
                'payload' => $request->getContent()
            ]);

            http_response_code(400);
            exit();
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

    /**
     * Recharge wallet balance
     *
     * @OA\Post(
     *     path="/v1/payments/recharge",
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
     *             ),
     *             @OA\Property(
     *                 property="service",
     *                 description="Target service: ultaInfinityWallet | divitExchange",
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
                'status' => 'error',
                'message' => $validation->errors()->toJson()
            ], 400);
        }

        // Write log
        try {
            LogPaymentRequest::create([
                'gateway' => $inputData['gateway'],
                'service' => $inputData['service'],
                'payload' => $inputData
            ]);
        } catch (\Exception $e) {
            Log::info('Log of invoice failed: ' . $e->getMessage());
        }

        // Init manager
        try{
            $system = Payment::getServiceManager($inputData['gateway']);
        } catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }

        // Create invoice
        $result = $system->createInvoice($inputData);

        // Return response
        $code = 200;
        if ($result['status'] === 'error') {
            $code = 400;

            LogPaymentRequestError::create([
                'gateway' => $inputData['gateway'],
                'payload' => $result['message']
            ]);
        }

        // Return result
        return response()->json($result, $code);
    }

    public function get(Request $request, $id)
    {
        $user_id = Auth::user()->getAuthIdentifier();
        $payment = \App\Models\Payment::where('id', $id)->where('user_id', $user_id)->first();
        return $payment;
    }
}
