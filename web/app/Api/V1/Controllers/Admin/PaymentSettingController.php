<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSystem as PaymentSystemModel;
use App\Models\PaymentSettings as PaymentSettingsModel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

/**
 * Class PaymentSettingController
 *
 * @package App\Api\V1\Controllers
 */

class PaymentSettingController extends Controller
{

     /**
     * Display list of all payment setting
     *
     * @OA\Get(
     *     path="/admin/payment-setting",
     *     description="Display list of all payment settings",
     *     tags={"Admin / Payment-Setting"},
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
     *         description="Count of orders in response",
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
            $resp['message']    = "List of all payment setting";
            $resp['title']      = "Display all payment setting";
            $resp['type']       = "Success";
            $resp['data']       = PaymentSettingsModel::orderBy('name', 'Asc')
                                ->paginate($request->get('limit', 20));
            return response()->json($resp, 200);
        } catch (\Exception $e) {
            return response()->json([
                'type'  => 'danger',
                'title'  => 'Display all payment setting',
                'message' => $e->getMessage()
            ], 400);
        }
    }



     /**
     * Method to add new payment setting
     *
     * @OA\Post(
     *     path="/admin/payment-setting",
     *     description="method to add new payment setting",
     *     tags={"Admin / Payment-Setting"},
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
     * @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *            @OA\Property(
     *                 property="payment_system_id",
     *                 type="string",
     *                 description="payment system id",
     *             ),
     *             @OA\Property(
     *                 property="setting_key",
     *                 type="string",
     *                 description="payment setting key",
     *             ),
     *             @OA\Property(
     *                 property="setting_value",
     *                 type="string",
     *                 description="payment setting value",
     *             ),
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
     * @throws \Illuminate\Validation\ValidationException
     */

    public function store(Request $request)
    {
        $resp['data']     = [];
        // Validate inputs
         try {
            $this->validate($request, [
                'setting_key'       => 'required|string',
                'setting_value'     => 'required|string',
                'payment_system_id' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type'      => 'warning',
                'title'     => 'Payement system',
                'message'   => 'Validation error',
                'data'      => $e->getMessage()
            ], 400);
        }
        try {
            $paymentSystem =  PaymentSystemModel::findOrFail($request['payment_system_id']);
            if($paymentSystem)
            {
                $setting = new PaymentSettingsModel;
                $setting->setting_key = $request['setting_key'];
                $setting->setting_value = $request['setting_value'];
                $paymentSystem->paymentsettings()->save($setting);

                $resp['message'] = "New payment setting was added";
                $resp['title']   = "Payment setting";
                $resp['type']    = "success";
                $resp['data']     = $paymentSystem->paymentsettings;
                return response()->json($resp, 200);
            }else{
                $resp['message']  = "Unable to create payment setting";
                $resp['title']    = "Payment setting";
                $resp['type']     = "warning";
                return response()->json($resp, 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type'      => 'danger',
                'title'     => 'Failed to add new payment setting',
                'message'   => $e->getMessage()
            ], 400);
        }
    }

}
