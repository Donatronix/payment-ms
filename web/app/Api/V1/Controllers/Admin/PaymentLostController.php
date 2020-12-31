<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\BitpayManager;
use App\Services\Payments\CoinbaseManager;
use App\Services\Payments\PaypalManager;
use App\Services\Payments\StripeManager;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class PaymentLostController
 *
 * @package App\Api\V1\Controllers
 */
class PaymentLostController extends Controller
{
    /**
     * Display invoice list not finished
     *
     * @OA\Get(
     *     path="/v1/payments/admin/paymentslost",
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
    public function index(Request $request) {

        $newStatuses = [
            "bitpay" => BitpayManager::STATUS_INVOICE_NEW,
            "coinbase" => CoinbaseManager::STATUS_CHARGE_CREATED,
            "paypal" => PaypalManager::STATUS_ORDER_CREATED,
            "stripe" => StripeManager::STATUS_ORDER_REQUIRES_PAYMENT_METHOD,
        ];

        $limit = intval($request->get('limit', 20));
        $page = intval($request->get('page', 1));
        $gateway = $request->get('gateway', "");
        $startItem = ($page-1)*$limit;

        if($gateway!="" && !isset($newStatuses[$gateway])) {
            return response()->json([
                'success' => false,
                'error' => "Gateway not defined",
            ], 400);
        }

        try {
            $payments = Payment::where('created_at', "<=", "DATE_SUB(NOW(), INTERVAL 1 HOUR)");
            if($gateway=="") {
                foreach ($newStatuses as $gateway=>$newstatus) {
                    $payments = $payments->orWhere(function($query) use ($gateway, $newstatus) {
                        $query->where("gateway", $gateway)
                            ->where("status", $newstatus);
                    });
                }
            } else {
                $payments = $payments->where('gateway', "=", $gateway)
                    ->where('status', $newStatuses[$gateway]);
            }
            $payments = $payments->orderBy("id", "DESC")->offset($startItem)->limit($limit)->get();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
        return response()->json([
            'success' => true,
            'payments' => $payments
        ], 200);
    }
}
