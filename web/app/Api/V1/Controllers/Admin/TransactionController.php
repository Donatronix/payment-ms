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
     *     path="/admin/transaction",
     *     description="Get all transactions",
     *     tags={"Admin | transactions"},
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(
     *                      property="id",
     *                      type="number",
     *                      description="id",
     *                      example="90000009-9009-9009-9009-900000000009"
     *                  ),
     *                  @OA\Property(
     *                      property="trx_id",
     *                      type="string",
     *                      description="trx_id",
     *                      example="PAY_INT_ULTRA62e19abcca0c5"
     *                  ),
     *                  @OA\Property(
     *                      property="payment_order_id",
     *                      type="string",
     *                      description="payment_order_id",
     *                      example="96e17ebc-5404-43ee-b1c9-323ed169f935"
     *                  )
     *              )
     *          )
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
    public function index(Request $request): mixed
    {
        try {
            $transaction = Transaction::with(['paymentOrders'])
                ->latest()
                ->paginate($request->get('limit', config('settings.pagination_limit')));

            return response()->jsonApi([
                'title' => 'Get Transaction List',
                'message' => 'Transaction List',
                'data' => $transaction
            ]);
        } catch (\Throwable $th) {
            return response()->jsonApi([
                'title' => 'Get Transaction List',
                'message' => 'Get Transaction List Failed: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Create transaction
     *
     * @OA\Post(
     *     path="/admin/transaction",
     *     summary="store transaction Record",
     *     description="store transaction Record",
     *     tags={"Admin | transactions"},
     *
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 description="Name of transaction",
     *                 example="A"
     *             ),
     *             @OA\Property(
     *                 property="trx_id",
     *                 type="string",
     *                 description="id of transaction",
     *                 example="383892830232320323-23232"
     *             ),
     *             @OA\Property(
     *                 property="payment_order_id",
     *                 type="string",
     *                 description="payment_order_id",
     *                 example="3927329382-3283203-23232"
     *             )
     *         ),
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="id",
     *                 type="number",
     *                 description="id",
     *                 example="90000009-9009-9009-9009-900000000009"
     *             ),
     *             @OA\Property(
     *                 property="trx_id",
     *                 type="string",
     *                 description="trx_id",
     *                 example="90000009-9009-9009-9009-900000000009"
     *             ),
     *             @OA\Property(
     *                 property="payment_order_id",
     *                 type="string",
     *                 description="payment_order_id",
     *                 example="90000009-9009-9009-9009-900000000009"
     *             )
     *         ),
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
     *     )
     * )
     *
     * @param Request $request
     *
     * @return transaction|JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse|Transaction
    {
        try {
            $this->validate($request, [
                'trx_id' => 'required|string',
                'payment_order_id' => 'required|string',
            ]);

            $transaction = new transaction();
            $transaction->trx_id = $request->trx_id;
            $transaction->payment_order_id = $request->payment_order_id;

            $transaction->save();

            return response()->jsonApi([
                'title' => 'Store transaction',
                'message' => 'transaction saved',
                'data' => $transaction
            ]);
        } catch (\Throwable $th) {
            return response()->jsonApi([
                'title' => 'Store transaction',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     *  Display a listing of the transaction
     *
     * @OA\Get(
     *     path="/admin/transaction/{id}",
     *     description="Get all transactions",
     *     tags={"Admin | transactions"},
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(
     *                  property="id",
     *                  type="number",
     *                  description="id",
     *                  example="90000009-9009-9009-9009-900000000009"
     *              ),
     *              @OA\Property(
     *                  property="trx_id",
     *                  type="string",
     *                  description="trx_id",
     *                  example="PAY_INT_ULTRA62e19abcca0c5"
     *              ),
     *              @OA\Property(
     *                  property="payment_order_id",
     *                  type="string",
     *                  description="payment_order_id",
     *                  example="96e17ebc-5404-43ee-b1c9-323ed169f935"
     *              )
     *          )
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
    public function show($id): mixed
    {
        try {
            $transaction = Transaction::with(['paymentOrders'])->where('id', $id)->first();

            return response()->jsonApi([
                'title' => 'Get Transaction',
                'message' => 'Transaction',
                'data' => $transaction
            ]);
        } catch (\Throwable $th) {
            return response()->jsonApi([
                'title' => 'Get a Transaction',
                'message' => 'Get a Transaction Failed: ' . $th->getMessage(),
            ], 500);
        }
    }
}
