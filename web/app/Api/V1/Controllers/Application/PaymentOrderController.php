<?php

namespace App\Api\V1\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\LogPaymentRequest;
use App\Models\LogPaymentRequestError;
use App\Models\PaymentOrder;
use App\Services\PaymentServiceManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Class PaymentOrderController
 *
 * @package App\Api\V1\Controllers
 */
class PaymentOrderController extends Controller
{
    /**
     * Init payment and charge wallet balance or invoice
     *
     * @OA\Post(
     *     path="/app/orders/charge",
     *     summary="Charge | Init payment and charge wallet balance or invoice",
     *     description="Charge | Init payment and charge wallet balance or invoice",
     *     tags={"Application | Payment Orders"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="gateway",
     *                 type="string",
     *                 description="Payment service provider",
     *                 default="stripe",
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
     *                 default="USD"
     *             ),
     *             @OA\Property(
     *                 property="document",
     *                 type="object",
     *                 description="The document on which the deposit is based",
     *                 @OA\Property(
     *                     property="id",
     *                     type="string",
     *                     description="Document ID",
     *                     example="80000000-8000-8000-8000-000000000008"
     *                 ),
     *                 @OA\Property(
     *                     property="object",
     *                     type="string",
     *                     description="Document model",
     *                     example="Deposit"
     *                 ),
     *                 @OA\Property(
     *                     property="service",
     *                     type="string",
     *                     description="Service which was generated document",
     *                     example="CryptoLaunchpadMS"
     *                 ),
     *                 @OA\Property(
     *                     property="meta",
     *                     type="object",
     *                     description="Document metadata",
     *                     example="{}"
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="redirect_url",
     *                 type="string",
     *                 description="URL where the user will be redirected after payment",
     *                 default="https://domain.com"
     *             )
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
     * @return JsonResponse
     */
    public function charge(Request $request): JsonResponse
    {
        // Validate input
        try {
            $rules = [
                'gateway' => 'required|string',
                'amount' => 'required|integer',
                'currency' => 'required|string',
                'redirect_url' => 'sometimes|string'
            ];

            // If based on document, then
            if($request->has('document')){
                $rules += [
                    'document' => 'sometimes|array:id,object,service,meta',
                    'document.id' => 'required|string|min:36|max:36',
                    'document.object' => 'required|string',
                    'document.service' => 'required|string',
                    'document.meta' => 'nullable|array'
                ];
            }

            $this->validate($request, $rules);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'title' => 'Creating a charge payment',
                'message' => "Field validation error: " . $e->getMessage(),
                'data' => $e->errors()
            ], 422);
        }

        $inputData = (object)$request->all();

        // Write log
        try {
            LogPaymentRequest::create([
                'gateway' => $request->get('gateway'),
                'service' => $request->get('document.service', null),
                'payload' => $request->all()
            ]);
        } catch (\Exception $e) {
            Log::info('Log of invoice failed: ' . $e->getMessage());
        }

        // Init manager
        try {
            $system = PaymentServiceManager::getInstance($request->get('gateway'));
        } catch (\Exception $e) {
            return response()->jsonApi([
                'title' => 'Creating a charge payment',
                'message' => $e->getMessage()
            ], 400);
        }

        // Create internal order
        $payment = PaymentOrder::create([
            'type' => PaymentOrder::TYPE_PAYIN,
            'gateway' => $request->get('gateway'),
            'amount' => $request->get('amount'),
            'currency' => mb_strtoupper($request->get('currency')),
            'service' => $request->get('document.service', null),
            'user_id' => Auth::user()->getAuthIdentifier()
        ]);

        // Create invoice
        $result = $system->charge($payment, $inputData);

        // Return response
        $code = 200;
        if ($result['type'] === 'danger') {
            $code = 400;

            LogPaymentRequestError::create([
                'gateway' => $request->get('gateway'),
                'payload' => $result['message']
            ]);
        }

        // Return result
        return response()->jsonApi($result, $code);
    }

    /**
     * Init payment and withdraw wallet balance
     *
     * @OA\Post(
     *     path="/app/orders/withdraw",
     *     summary="Withdraw | Init payment and withdraw wallet balance",
     *     description="Withdraw | Init payment and withdraw wallet balance",
     *     tags={"Application | Payment Orders"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
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
     *                 default="bitpay"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success"
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function withdraw(Request $request): JsonResponse
    {
        //
    }

    /**
     * Get detail info about transaction
     *
     * @OA\Get(
     *     path="/app/orders/{id}",
     *     summary="Detail | Get detail info about Payment Order",
     *     description="Detail | Get detail info about Payment Order",
     *     tags={"Application | Payment Orders"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Payment Order ID",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Data of Payment Order"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Payment Order not found",
     *     )
     * )
     *
     * @param $id
     * @return mixed
     */
    public function show($id): mixed
    {
        try {
            $payment = PaymentOrder::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'title' => 'Payment Order',
                'message' => "Payment Order not found: {$e->getMessage()}"
            ], 404);
        }

        return response()->jsonApi([
            'title' => 'Payment Order',
            'message' => "Payment Order detail received",
            'data' => $payment
        ]);
    }

    function calculateOrderAmount(array $items): int
    {
        // Replace this constant with a calculation of the order's amount
        // Calculate the order total on the server to prevent
        // people from directly manipulating the amount on the client
        return 1400;
    }
}
