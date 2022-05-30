<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\PaymentSystemController;
use App\Http\Controllers\Controller;
use App\Models\StripePaymentGatewaySetup;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class StripePaymentGatewaySetupController
 *
 * @package App\Api\V1\Controllers
 */

class StripePaymentGatewaySetupController extends Controller
{


        /**
        * Display Stripe Payment Gateway Setup list
        *
            @OA\Get(
        *     path="/admin/stripe/settings",
        *     description="list payment gateway settings",
        *     tags={"admin / stripe / settings"},
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
        *         name="gateway_name",
        *         description="captures the name of the gateway",
        *         in="query",
        *         required=true,
        *         @OA\Schema(
        *              type="string",
        *              default = Stripe,
        *         )
        *     ),
        *     @OA\Parameter(
        *         name="webhook_secret",
        *         description="captures the web hook secret",
        *         in="query",
        *         required=false,
        *         @OA\Schema(
        *              type="string",
        *              default = 12345,
        *         )
        *     ),
        *     @OA\Parameter(
        *         name="public_key",
        *         description="captures the public key",
        *         in="query",
        *         required=false,
        *         @OA\Schema(
        *              type="string",
        *              default = 12345,
        *         )
        *     ),
        *     @OA\Parameter(
        *         name="secret_key",
        *         description="captures the secret key",
        *         in="query",
        *         required=false,
        *         @OA\Schema(
        *              type="string",
        *              default = 12345,
        *         )
        *     ),
        *     @OA\Parameter(
        *         name="status",
        *         description="captures the activeness of the record",
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

        public function index()
        {
            $data               = array();
            $data['message']    = "Unable to add payment gateway settings";
            $data['success']    = false;
            $data['gateway']    = [];

            try {
                $data['message'] = "Payment gateway settings were added successfully";
                $data['success'] = true;
                $data['gateway'] = StripePaymentGatewaySetup::where('status', 1)->first();

                return response()->json($data, 200);
            } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'error' => $e->getMessage()
                    ], 400);
            }
        }



  /**
     * Add new or update payment gateway settings
     *
     * @OA\Post(
     *     path="/admin/stripe/settings",
     *     description="Add new or update payment gateway settings",
     *     tags="admin / stripe / settings",
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
     * @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="gateway_name",
     *                 type="string",
     *                 description="Payment gateway name",
     *                 default="stripe",
     *
     *             ),
     *             @OA\Property(
     *                 property="webhook_secret",
     *                 type="string",
     *                 description="web hook secret of the gateway",
     *             ),
     *             @OA\Property(
     *                 property="public_key",
     *                 type="string",
     *                 description="public key of the gateway",
     *             ),
     *             @OA\Property(
     *                 property="secret_key",
     *                 type="string",
     *                 description="secret key of the gateway",
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 description="makes the payment gateway active",
     *                 default= 1,
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     )
     * )
     *
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(Request $request, $id)
    {
        $saved              = null;
        $data               = array();
        $data['message']    = "Unable to add payment gateway settings";
        $data['success']    = false;
        $data['gateway']    = [];

        try {
            $saved = StripePaymentGatewaySetup::updateOrCreate(
            [
                'id'                => $id
            ],
            [
                'gateway_name'      => $request['gateway_name'],
                'webhook_secret'    => $request['webhook_secret'],
                'public_key'        => $request['public_key'],
                'secret_key'        => $request['secret_key'],
                'status'            => $request['status'],
            ]);

            if($saved)
            {
                $data['message'] = "Payment gateway settings were added successfully";
                $data['success'] = true;
                $data['gateway'] = StripePaymentGatewaySetup::where('status', 1)->first();
                return response()->json($data, 200);
            }else{
                return response()->json($data, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }


}
