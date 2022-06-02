<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OpenpaydPaymentGatewaySetup as OpenpaydPaymentGatewayModel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Class OpenpaydPaymentGatewaySetupController
 *
 * @package App\Api\V1\Controllers
 */

class OpenpaydPaymentGatewaySetupController extends Controller
{


     /**
     * Display list of all openpayd payment gateway settings
     *
     * @OA\Get(
     *     path="/admin/settings/openpayd",
     *     description="Display list of all openpayd payment gateway settings",
     *     tags={"Admin / Settings / openpayd"},
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
     * *     @OA\Parameter(
     *         name="limit",
     *         description="Count of orders / currencies pair in response",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="integer",
     *              default=20,
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         description="Page of list",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="integer",
     *              default=1,
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
    public function index(Request $request): JsonResponse
    {
            $resp['data']    = [];
            try {
                $resp['message']    = "List of all payment gateway settings";
                $resp['title']      = "Display Openpayd Payment gateway settings";
                $resp['type']       = "Success";
                $resp['data']       = OpenpaydPaymentGatewayModel::orderBy('created_at', 'Desc')
                                    ->paginate($request->get('limit', 20));
                return response()->json($resp, 200);
            } catch (\Exception $e) {
                    return response()->json([
                        'type'  => 'danger',
                        'title'  => 'List Openpayd payment gateway settings',
                        'message' => $e->getMessage()
                    ], 400);
            }
    }




}
