<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentOrder;
use App\Models\PaymentService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class PaymentOrderController
 *
 * @package App\Api\V1\Controllers
 */
class PaymentOrderController extends Controller
{
    /**
     * Display Payments list
     *
     * @OA\Get(
     *     path="/admin/orders",
     *     description="Display list of all patment orders",
     *     tags={"Admin | Payment Orders"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
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
     *         response="200",
     *         description="Success",
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $data = PaymentOrder::paginate($request->get('limit', config('settings.pagination_limit')));

            return response()->jsonApi([
                'title' => 'Getting payment orders collection',
                'message' => 'Payment orders list received successfully',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Getting payment orders collection',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display invoice list not finished
     *
     * @OA\Get(
     *     path="/admin/orders/lost",
     *     description="Display list of all patment orders",
     *     tags={"Admin | Payment Orders"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="limit",
     *         description="count at orders in return",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="integer",
     *              default = 20,
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         description="page of list, 1 - first page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="integer",
     *              default = 1,
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="gateway",
     *         description="filter by payment service",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="string",
     *              default = "",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function lost(Request $request)
    {
        $services = PaymentService::catalog();

        $newStatuses = [];
        $limit = intval($request->get('limit', 20));
        $page = intval($request->get('page', 1));

        if ($limit * $page == 0) {
            return response()->jsonApi([
                'success' => false,
                'error' => "Incorrect pagination: page or limit",
            ], 400);
        }

        $gateway = $request->get('gateway', "");
        $startItem = ($page - 1) * $limit;

        if ($gateway != "" && !isset($newStatuses[$gateway])) {
            return response()->jsonApi([
                'success' => false,
                'error' => "Gateway not defined",
            ], 400);
        }

        try {
            $payments = PaymentOrder::where('created_at', "<=", "DATE_SUB(NOW(), INTERVAL 1 HOUR)");

            if ($gateway == "") {
                foreach ($newStatuses as $gateway => $newstatus) {
                    $payments = $payments->orWhere(function ($query) use ($gateway, $newstatus) {
                        $query->where("service_key", $gateway)
                            ->where("status", $newstatus);
                    });
                }
            } else {
                $payments = $payments->where('service_key', "=", $gateway)
                    ->where('status', $newStatuses[$gateway]);
            }

            $payments = $payments->orderBy("id", "DESC")
                ->offset($startItem)
                ->limit($limit)
                ->get();

            return response()->jsonApi([
                'success' => true,
                'payments' => $payments
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Method for proof of payment by payment order
     *
     * @OA\Post(
     *     path="/admin/orders",
     *     description="Method for proof of payment by payment order",
     *     tags={"Admin | Payment Orders"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     )
     * )
     *
     * @param Request $request
     * @param                          $id
     *
     * @throws ValidationException
     */
    public function update(Request $request, $id)
    {
        // Validate input
        $this->validate($request, [
            //'status' => 'boolean'
        ]);

        // Get payment order model
        try {
            $order = PaymentOrder::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'title' => 'Payment order not found',
                'message' => "Payment order #{$id} not found"
            ], 404);
        }
    }
}
