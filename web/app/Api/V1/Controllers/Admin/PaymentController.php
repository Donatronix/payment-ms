<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class PaymentController
 *
 * @package App\Api\V1\Controllers
 */
class PaymentController extends Controller
{
    /**
     * Display payment orders list
     *
     * @OA\Get(
     *     path="/v1/payments/admin/payments",
     *     description="Display list of all patment orders",
     *     tags={"Admin - Payment Orders"},
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
     *         name="limit",
     *         description="count ot orders in return",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="integer",
     *              default = 20,
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         description="page of list",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="integer",
     *              default = 1,
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request){
        try {
            $data = Payment::paginate($request->get('limit', 20));

            return response()->json(array_merge(['success' => true], $data->toArray()), 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Method for proof of payment by payment order
     *
     * @OA\Post(
     *     path="/v1/payments/admin/payments",
     *     description="Method for proof of payment by payment order",
     *     tags={"Admin - Payment Orders"},
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
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @param                          $id
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id){
        // Validate input
        $this->validate($request, [
            //'status' => 'boolean'
        ]);

        // Get payment order model
        try {
            $order = Payment::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'error',
                'title' => 'Payment order not found',
                'message' => "Payment order #{$id} not found"
            ], 404);
        }

        try {
            // Create transaction for charge user balance
//            $charge = Transaction::create([
//                'name' => 'Activate card',
//                'sender_id' => env('SUMRA_USER'),
//                'receiver_id' => $order->user_id,
//                'amount' => $order->amount,
//                'currency_id' => $order->currency_id,
//                'status' => Transaction::STATUS_NOT_CONFIRMED
//            ]);

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Proof of payment',
                'message' => "Charge transaction created successfully"
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'error',
                'title' => 'Proof of payment',
                'message' => "Can't create transaction for charge user balance"
            ], 404);
        }
    }
}
