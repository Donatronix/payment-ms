<?php

namespace App\Api\V1\Controllers\Application;

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
 * Class ChargeController
 *
 * @package App\Api\V1\Controllers
 */
class ChargeController extends Controller
{
    /**
     * Init payment and charge wallet balance or invoice
     *
     * @OA\Post(
     *     path="/payments/charge",
     *     description="Init payment and charge wallet balance or invoice",
     *     tags={"Payments | Charge"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
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
     *                 property="document_based_on",
     *                 type="string",
     *                 description="The document on which the deposit is based"
     *             ),
     *             @OA\Property(
     *                 property="redirect_url",
     *                 type="string",
     *                 description="An address where the user will be redirected after payment",
     *                 default="GBP"
     *             ),
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
    public function __invoke(Request $request): JsonResponse
    {
        // Validate input
        try {
            $this->validate($request, [
                'gateway' => 'string|required',
                'amount' => 'integer|required',
                'currency' => 'integer|required',
                'document_based_on' => 'string',
                'redirect_url' => 'string|required'
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
                'service' => $inputData->document_based_on,
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
            'service' => $request->get('document_based_on'),
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
}
