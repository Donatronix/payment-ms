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
use Illuminate\Validation\ValidationException;

/**
 * Class PaymentController
 *
 * @package App\Api\V1\Controllers
 */
class PaymentController extends Controller
{
    /**
     * Recharge wallet balance
     *
     * @OA\Post(
     *     path="/recharge",
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
     *                 description="Target service: ultaInfinityWallets | divitExchange",
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
    public function recharge(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate input
        try {
            $this->validate($request, [
                'gateway' => 'string|required',
                'amount' => 'integer|required',
                'currency' => 'string|required',
                'service' => 'string|required',
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'Contributor details data',
                'message' => "Validation error",
                'data' => $e->getMessage() // $validation->errors()->toJson()
            ], 400);
        }

        $inputData = $request->all();

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
                'type' => 'danger',
                'message' => $e->getMessage()
            ], 400);
        }

       // dd($system);

        // Create invoice
        $result = $system->createInvoice($inputData);

        // Return response
        $code = 200;
        if ($result['type'] === 'danger') {
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

        $payment = \App\Models\Payment::where('id', $id)
        ->where('user_id', $user_id)
        ->first();

        return $payment;
    }
}
