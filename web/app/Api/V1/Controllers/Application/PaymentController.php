<?php

namespace App\Api\V1\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\PaymentOrder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class PaymentOrderController
 *
 * @package App\Api\V1\Controllers
 */
class PaymentController extends Controller
{
    /**
     * Get detail info about transaction
     *
     * @OA\Get(
     *     path="/app/orders/{id}",
     *     summary="Detail | Get detail info about payment transaction",
     *     description="Detail | Get detail info about payment transaction",
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
    public function show($id): mixed
    {
        try {
            $payment = PaymentOrder::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'title' => 'Payment transaction',
                'message' => "Payment transaction not found: {$e->getMessage()}"
            ], 404);
        }

        return response()->jsonApi([
            'title' => 'Payment transaction',
            'message' => "Payment transaction detail received",
            'data' => $payment
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
