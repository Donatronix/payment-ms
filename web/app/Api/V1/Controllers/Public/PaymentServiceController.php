<?php

namespace App\Api\V1\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class PaymentServiceController
 *
 * @package App\Api\V1\Controllers
 */
class PaymentServiceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/payment-services",
     *     description="List of Payment services",
     *     tags={"Public | Payment Services"},
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
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $resp['data'] = PaymentService::where('status', true)->orderBy('name', 'Asc');

            return response()->jsonApi([
                'title' => 'Display all payment service',
                'message' => 'List of all payment service'
            ]);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'title' => 'Display all payment service',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
