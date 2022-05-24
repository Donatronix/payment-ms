<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LogPaymentRequest;
use App\Models\LogPaymentRequestError;
use App\Models\Payment as PaymentModel;
use App\Services\Payment as PaymentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Class PaymentController
 *
 * @package App\Api\V1\Controllers
 */
class PaymentController extends Controller
{
    /**
     * Make payment and charge wallet balance or invoice
     *
     * @OA\Post(
     *     path="/payments/charge",
     *     description="Make payment and charge wallet balance or invoice",
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
     *                 type="string",
     *                 description="Payment gateway",
     *                 default="bitpay",
     *
     *             ),
     *             @OA\Property(
     *                 property="amount",
     *                 type="integer",
     *                 description="The amount of money replenished to the balance",
     *                 default=1000
     *             ),
     *             @OA\Property(
     *                 property="currency",
     *                 type="string",
     *                 description="Currency of balance",
     *                 default="GBP"
     *             ),
     *             @OA\Property(
     *                 property="service",
     *                 type="string",
     *                 description="Target service: "
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
     * @param Request $request
     * @return JsonResponse
     */
    public function charge(Request $request): JsonResponse
    {
        // Validate input
        try {
            $this->validate($request, [
                'gateway' => 'string|required',
                'amount' => 'integer|required',
                'currency' => 'string',
                'service' => 'string',
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'Contributor details data',
                'message' => "Validation error",
                'data' => $e->getMessage() // $validation->errors()->toJson()
            ], 400);
        }

        $inputData = (object)$request->all();

        // Write log
        try {
            LogPaymentRequest::create([
                'gateway' => $inputData->gateway,
                'service' => $inputData->service,
                'payload' => $inputData
            ]);
        } catch (\Exception $e) {
            Log::info('Log of invoice failed: ' . $e->getMessage());
        }

        // Init manager
        try {
            $system = PaymentService::getInstance($inputData->gateway);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'danger',
                'message' => $e->getMessage()
            ], 400);
        }

        //dd($system);

        // Create internal order
        $payment = PaymentModel::create([
            'type' => PaymentModel::TYPE_PAYIN,
            'gateway' => $request->get('gateway'),
            'amount' => $request->get('amount'),
            'currency' => mb_strtoupper($request->get('currency')),
            'service' => $request->get('service'),
            'user_id' => Auth::user()->getAuthIdentifier()
        ]);

        // Create invoice
        $result = $system->charge($payment, $inputData);

        // Return response
        $code = 200;
        if ($result['type'] === 'danger') {
            $code = 400;

            LogPaymentRequestError::create([
                'gateway' => $inputData->gateway,
                'payload' => $result['message']
            ]);
        }

        // Return result
        return response()->json($result, $code);
    }

    /**
     * Get detail info about transaction
     *
     * @OA\Get(
     *     path="/payments/{id}",
     *     summary="Get detail info about payment transaction",
     *     description="Get detail info about payment transaction",
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
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Payment transaction ID",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Data of payment transaction"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Payment transaction not found",
     *     )
     * )
     *
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        try {
            $payment = PaymentModel::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Get payment transaction object",
                'message' => "payment transaction with id #{$id} not found: {$e->getMessage()}"
            ], 404);
        }

        return response()->jsonApi([
            'type' => 'success',
            'title' => 'payment transaction details',
            'message' => "payment transaction details received",
            'data' => $payment->toArray()
        ], 200);
    }

    function calculateOrderAmount(array $items): int
    {
        // Replace this constant with a calculation of the order's amount
        // Calculate the order total on the server to prevent
        // people from directly manipulating the amount on the client
        return 1400;
    }
}
