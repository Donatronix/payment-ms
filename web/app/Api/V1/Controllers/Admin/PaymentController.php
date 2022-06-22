<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\PaymentSystemController;
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
     * Display Payments list
     *
     * @OA\Get(
     *     path="/admin/payments",
     *     description="Display list of all patment orders",
     *     tags={"Admin / Payments"},
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

            return response()->json(array_merge([
                'success' => true
            ], $data->toArray()), 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display invoice list not finished
     *
     * @OA\Get(
     *     path="/admin/payments/lost",
     *     description="Display list of all patment orders",
     *     tags={"Admin / Payments"},
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
     *         description="filter by gateway",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="string",
     *              default = "",
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
    public function lost(Request $request)
    {
        $newStatuses = [];

        $ctrl = new PaymentSystemController();
        $systems = $ctrl->catalog();

        foreach ($systems as $system) {
            $newStatuses[$system['gateway']] = $system['new_status'];
        }

        $limit = intval($request->get('limit', 20));
        $page = intval($request->get('page', 1));

        if ($limit * $page == 0) {
            return response()->json([
                'success' => false,
                'error' => "Incorrect pagination: page or limit",
            ], 400);
        }

        $gateway = $request->get('gateway', "");
        $startItem = ($page - 1) * $limit;

        if ($gateway != "" && !isset($newStatuses[$gateway])) {
            return response()->json([
                'success' => false,
                'error' => "Gateway not defined",
            ], 400);
        }

        try {
            $payments = Payment::where('created_at', "<=", "DATE_SUB(NOW(), INTERVAL 1 HOUR)");

            if ($gateway == "") {
                foreach ($newStatuses as $gateway => $newstatus) {
                    $payments = $payments->orWhere(function ($query) use ($gateway, $newstatus) {
                        $query->where("gateway", $gateway)
                            ->where("status", $newstatus);
                    });
                }
            } else {
                $payments = $payments->where('gateway', "=", $gateway)
                    ->where('status', $newStatuses[$gateway]);
            }

            $payments = $payments->orderBy("id", "DESC")
                ->offset($startItem)
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'payments' => $payments
            ], 200);

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
     *     path="/admin/payments",
     *     description="Method for proof of payment by payment order",
     *     tags={"Admin / Payments"},
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
                'type' => 'danger',
                'title' => 'Payment order not found',
                'message' => "Payment order #{$id} not found"
            ], 404);
        }
    }
}
