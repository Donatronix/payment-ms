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
 * Class PaymentController
 *
 * @package App\Api\V1\Controllers
 */
class PaymentController extends Controller
{
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
