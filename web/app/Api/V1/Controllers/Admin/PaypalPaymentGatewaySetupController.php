<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaypalPaymentGatewaySetup as PaypalPaymentGatewayModel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class PaypalPaymentGatewaySetupController
 *
 * @package App\Api\V1\Controllers
 */

class PaypalPaymentGatewaySetupController extends Controller
{


     /**
     * Display list of all paypal payment gateway settings
     *
     * @OA\Get(
     *     path="/admin/settings/paypal",
     *     description="Display list of all paypal payment gateway settings",
     *     tags={"Admin / Settings / paypal"},
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
    public function index()
    {
            $resp['data']    = [];
            try {
                $resp['message']    = "List of all payment gateway settings";
                $resp['title']      = "Paypal Payment gateway setting";
                $resp['type']       = "Success";
                $resp['data']       = PaypalPaymentGatewayModel::orderBy('created_at', 'Desc')->get();
                return response()->json($resp, 200);
            } catch (\Exception $e) {
                    return response()->json([
                        'type'  => 'danger',
                        'error' => $e->getMessage()
                    ], 400);
            }
    }


}
