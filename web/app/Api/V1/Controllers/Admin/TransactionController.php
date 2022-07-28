<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

/**
 * Class TransactionController
 *
 * @package App\Api\V1\Controllers
 */
class TransactionController extends Controller
{
    /**
     *  Display a listing of the transaction
     *
     * @OA\Get(
     *     path="/app/transaction",
     *     description="Get all transactions",
     *     tags={"Admin | transactions"},
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *
     *             @OA\Property(
     *                  property="id",
     *                  type="number",
     *                  description="id",
     *                  example="90000009-9009-9009-9009-900000000009"
     *              ),
     *              @OA\Property(
     *                  property="transaction_id",
     *                  type="string",
     *                  description="transaction_id",
     *                  example="PAY_INT_ULTRA62e19abcca0c5"
     *              ),
     *
     *              @OA\Property(
     *                  property="payment_order_id",
     *                  type="string",
     *                  description="payment_order_id",
     *                  example="96e17ebc-5404-43ee-b1c9-323ed169f935"
     *              )
     *              )
     *          ),
     *     ),
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found"
     *     ),
     * )
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        try {
            $transaction = Transaction::with(['paymentOrders'])->latest()->paginate($request->get('limit', config('settings.pagination_limit')));

            return response()->json([
                'type' => 'success',
                'title' => 'Get Transaction List',
                'message' => 'Transaction List',
                'data' => $transaction
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'danger',
                'title' => 'Get Transaction List',
                'message' => 'Get Transaction List Failed',
                'data' => $th->getMessage()
            ], 500);
        }
    }


     /**
     *  Display a listing of the transaction
     *
     * @OA\Get(
     *     path="/app/transaction/{id}",
     *     description="Get all transactions",
     *     tags={"Admin | transactions"},
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Property(
     *                  property="id",
     *                  type="number",
     *                  description="id",
     *                  example="90000009-9009-9009-9009-900000000009"
     *              ),
     *              @OA\Property(
     *                  property="transaction_id",
     *                  type="string",
     *                  description="transaction_id",
     *                  example="PAY_INT_ULTRA62e19abcca0c5"
     *              ),
     *
     *              @OA\Property(
     *                  property="payment_order_id",
     *                  type="string",
     *                  description="payment_order_id",
     *                  example="96e17ebc-5404-43ee-b1c9-323ed169f935"
     *              )
     *          ),
     *     ),
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found"
     *     ),
     * )
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function show($id)
    {
        try {
            $transaction = Transaction::with(['paymentOrders'])->where('id', $id)->first();
            return response()->json([
                'type' => 'success',
                'title' => 'Get Transaction',
                'message' => 'Transaction',
                'data' => $transaction
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'danger',
                'title' => 'Get a Transaction',
                'message' => 'Get a Transaction Failed',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    /**
    *    Create transaction
    *
    *    @OA\Post(
    *        path="/admin/transaction",
    *        summary="store transaction Record",
    *        description="store transaction Record",
    *        tags={"Admin | transactions"},
    *        @OA\RequestBody(
    *            @OA\JsonContent(
    *                type="object",
    *                @OA\Property(
    *                    property="name",
    *                    type="string",
    *                    description="Name of transaction",
    *                    example="A"
    *                ),
    *           ),
    *           @OA\JsonContent(
    *                type="object",
    *                @OA\Property(
    *                    property="transaction_id",
    *                    type="string",
    *                    description="id of transaction",
    *                    example="383892830232320323-23232"
    *                ),
    *           ),
    *           @OA\JsonContent(
    *                type="object",
    *                @OA\Property(
    *                    property="payment_order_id",
    *                    type="string",
    *                    description="payment_order_id",
    *                    example="3927329382-3283203-23232"
    *                ),
    *           ),
    *        ),
    *        @OA\Response(
    *          response="200",
    *          description="Success",
    *          @OA\JsonContent(
    *              type="object",
    *             @OA\Property(
    *                  property="id",
    *                  type="number",
    *                  description="id",
    *                  example="90000009-9009-9009-9009-900000000009"
    *              ),
    *              @OA\Property(
    *                  property="transaction_id",
    *                  type="string",
    *                  description="transaction_id",
    *                  example="90000009-9009-9009-9009-900000000009"
    *              ),
    *              @OA\Property(
    *                  property="payment_order_id",
    *                  type="string",
    *                  description="payment_order_id",
    *                  example="90000009-9009-9009-9009-900000000009"
    *              ),
    *
    *              @OA\Property(
    *                  property="created_at",
    *                  type="string",
    *                  description="timestamp of data entry",
    *                  example="2022-05-09T12:45:46.000000Z"
    *              )
    *          ),
    *     ),
    *     @OA\Response(
    *         response="500",
    *         description="Unknown error"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="Invalid request"
    *     ),
    *
    *     @OA\Response(
    *         response="404",
    *         description="Not Found"
    *     ),
    * )
    *
    * @param Request $request
    *
    * @return transaction|JsonResponse
    * @throws ValidationException
    */

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'transaction_id' => 'required|string',
                'payment_order_id' => 'required|string',
            ]);

            $transaction = new transaction();
            $transaction->transaction_id = $request->transaction_id;
            $transaction->payment_order_id = $request->payment_order_id;

            $transaction->save();

            return response()->json([
                'type' => 'success',
                'title' => 'Store transaction',
                'message' => 'transaction saved',
                'data' => $transaction
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'success',
                'title' => 'Store transaction',
                'message' => 'transaction saved',
                'data' => $th->getMessage()
            ], 500);
        }
    }

}
