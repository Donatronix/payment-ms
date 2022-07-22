<?php

namespace App\Api\V1\Controllers\Application;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class WithdrawController
 *
 * @package App\Api\V1\Controllers
 */
class WithdrawController extends Controller
{
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
    public function __invoke(Request $request): JsonResponse
    {
        //
    }
}
