<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AllPaymentGatewaySettings as ManageSettingsModel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

/**
 * Class ManageAllPaymentGatewayController
 *
 * @package App\Api\V1\Controllers
 */

class ManageAllPaymentGatewayController extends Controller
{

     /**
     * Display list of all payment gateway settings
     *
     * @OA\Get(
     *     path="/admin/gateway/settings",
     *     description="Display list of all payment gateway settings",
     *     tags={"Admin / Gateway / Settings"},
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
                $resp['message']    = "List of all payment gateway settings";
                $resp['title']      = "Display all payment gateway settings";
                $resp['type']       = "Success";
                $resp['data']       = ManageSettingsModel::orderBy('created_at', 'Desc')
                                    ->paginate($request->get('limit', 20));
                return response()->json($resp, 200);
            } catch (\Exception $e) {
                    return response()->json([
                        'type'  => 'danger',
                        'title'  => 'Display all payment gateway settings',
                        'message' => $e->getMessage()
                    ], 400);
            }
    }



     /**
     * Display all payment gateway settings details
     *
     * @OA\Get(
     *     path="/admin/gateway/{id}/settings",
     *     description="show all payment gateway settings details",
     *     tags={"Admin / Gateway / Settings"},
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
    public function show($id): JsonResponse
    {
            $resp['data']    = [];
            try {
                $resp['message']  = "Payment gateway setting details";
                $resp['title']    = "Payment gateway settings";
                $resp['type']     = "success";
                $resp['data']     = ManageSettingsModel::findOrFail($id);
                return response()->json($resp, 200);
            } catch (\Exception $e) {
                    return response()->json([
                        'type'      => 'danger',
                        'title'     => 'Payement gateway Details',
                        'message'   => $e->getMessage()
                    ], 400);
            }
    }


    /**
     * Method to add new payment gateway settings
     *
     * @OA\Post(
     *     path="/admin/gateway/settings",
     *     description="method to add new payment gateway settings",
     *     tags={"Admin / Gateway / Settings"},
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
     *             @OA\Property(
     *                 property="payment_gateway_name",
     *                 type="string",
     *                 description="Name of the payment gateway",
     *             ),
     *             @OA\Property(
     *                 property="stripe_webhook_secret",
     *                 type="string",
     *                 description="The stripe webhook secret",
     *             ),
     *             @OA\Property(
     *                 property="stripe_public_key",
     *                 type="string",
     *                 description="Stripe Publick key",
     *             ),
     *             @OA\Property(
     *                 property="stripe_secret_key",
     *                 type="string",
     *                 description="Stripe Secret key"
     *             ),
     *              @OA\Property(
     *                 property="paypal_mode",
     *                 type="string",
     *                 description="Paypal payment mode",
     *             ),
     *             @OA\Property(
     *                 property="paypal_notify_url",
     *                 type="string",
     *                 description="Paypal Payment notify url",
     *             ),
     *             @OA\Property(
     *                 property="paypal_currency",
     *                 type="string",
     *                 description="Paypal payment currency type",
     *                 default= "USD",
     *             ),
     *             @OA\Property(
     *                 property="paypal_sandbox_client_id",
     *                 type="string",
     *                 description="Paypal payment sandbox client ID"
     *             ),
     *             @OA\Property(
     *                 property="paypal_sandbox_client_secret",
     *                 type="string",
     *                 description="Paypal payment sandbox client secret"
     *             ),
     *             @OA\Property(
     *                 property="paypal_live_client_id",
     *                 type="string",
     *                 description="Paypal paypal_payment live client ID"
     *             ),
     *             @OA\Property(
     *                 property="paypal_live_client_secret",
     *                 type="string",
     *                 description="Paypal payment live client secret"
     *             ),
     *              @OA\Property(
     *                 property="openpayd_username",
     *                 type="string",
     *                 description="Openpayd payment username",
     *             ),
     *             @OA\Property(
     *                 property="openpayd_password",
     *                 type="string",
     *                 description="Openpayd payment password",
     *             ),
     *             @OA\Property(
     *                 property="openpayd_url",
     *                 type="string",
     *                 description="Openpayd payment url",
     *             ),
     *             @OA\Property(
     *                 property="openpayd_public_key_path",
     *                 type="string",
     *                 description="Openpayd payment public key path"
     *             ),
     *             @OA\Property(
     *                 property="coinbase_api_key",
     *                 type="string",
     *                 description="Coinbase API key",
     *             ),
     *             @OA\Property(
     *                 property="coinbase_webhook_key",
     *                 type="string",
     *                 description="Coinbase webhook key",
     *             ),
     *             @OA\Property(
     *                 property="coinbase_redirect_url",
     *                 type="string",
     *                 description="Coinbase redirect URL",
     *             ),
     *             @OA\Property(
     *                 property="coinbase_cancel_url",
     *                 type="string",
     *                 description="Coinbase cancel url"
     *             ),
     *             @OA\Property(
     *                 property="bitpay_environment",
     *                 type="string",
     *                 description="Bitpay environment"
     *             ),
     *             @OA\Property(
     *                 property="bitpay_api_token_merchant",
     *                 type="string",
     *                 description="Bitpay api token merchant"
     *             ),
     *             @OA\Property(
     *                 property="bitpay_api_token_payroll",
     *                 type="string",
     *                 description="Bitpay api token payroll"
     *             ),
     *              @OA\Property(
     *                 property="bitpay_key_path",
     *                 type="string",
     *                 description="Bitpay key path"
     *             ),
     *             @OA\Property(
     *                 property="bitpay_private_key_password",
     *                 type="string",
     *                 description="Bitpay private key password"
     *             ),
     *             @OA\Property(
     *                 property="bitpay_payment_webhook_url",
     *                 type="string",
     *                 description="Bitpay payment webhook url"
     *             ),
     *             @OA\Property(
     *                 property="bitpay_redirect_url",
     *                 type="string",
     *                 description="Bitpay redirect url"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 description="Currently in use settings",
     *                 default= 1,
     *             )
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
        $saved            = null;
        $resp['data']     = [];
        // Validate inputs
         try {
            $this->validate($request, [
                'payment_gateway_name'  => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type'      => 'warning',
                'title'     => 'Payement gateway settings',
                'message'   => 'Validation error',
                'data'      => $e->getMessage()
            ], 400);
        }
        try {
            $saved =  ManageSettingsModel::create($request->all());
            if($saved)
            {
                $resp['message'] = "New payment gateway setting was added";
                $resp['title']   = "Payment gateway settings";
                $resp['type']    = "success";
                $resp['data']    = ManageSettingsModel::where('id', $saved->id)->first();
                return response()->json($resp, 200);
            }else{
                $resp['message']  = "Unable to create payment gateway settings";
                $resp['title']    = "Payment gateway settings";
                $resp['type']     = "warning";
                return response()->json($resp, 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type'      => 'danger',
                'title'     => 'Failed to add new payement gateway settings',
                'message'   => $e->getMessage()
            ], 400);
        }
    }


    /**
     * Method to update payment gateway settings
     *
     * @OA\Put(
     *     path="/admin/gateway/{id}/settings",
     *     description="method to update payment gateway settings",
     *     tags={"Admin / Gateway / Settings"},
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
     *                 property="id",
     *                 type="string",
     *                 description="primary key to the record",
     *             ),
     *            @OA\Property(
     *                 property="payment_gateway_name",
     *                 type="string",
     *                 description="Name of the payment gateway",
     *             ),
     *             @OA\Property(
     *                 property="stripe_webhook_secret",
     *                 type="string",
     *                 description="The stripe webhook secret",
     *             ),
     *             @OA\Property(
     *                 property="stripe_public_key",
     *                 type="string",
     *                 description="Stripe Publick key",
     *             ),
     *             @OA\Property(
     *                 property="stripe_secret_key",
     *                 type="string",
     *                 description="Stripe Secret key"
     *             ),
     *              @OA\Property(
     *                 property="paypal_mode",
     *                 type="string",
     *                 description="Paypal payment mode",
     *             ),
     *             @OA\Property(
     *                 property="paypal_notify_url",
     *                 type="string",
     *                 description="Paypal Payment notify url",
     *             ),
     *             @OA\Property(
     *                 property="paypal_currency",
     *                 type="string",
     *                 description="Paypal payment currency type",
     *                 default= "USD",
     *             ),
     *             @OA\Property(
     *                 property="paypal_sandbox_client_id",
     *                 type="string",
     *                 description="Paypal payment sandbox client ID"
     *             ),
     *             @OA\Property(
     *                 property="paypal_sandbox_client_secret",
     *                 type="string",
     *                 description="Paypal payment sandbox client secret"
     *             ),
     *             @OA\Property(
     *                 property="paypal_live_client_id",
     *                 type="string",
     *                 description="Paypal paypal_payment live client ID"
     *             ),
     *             @OA\Property(
     *                 property="paypal_live_client_secret",
     *                 type="string",
     *                 description="Paypal payment live client secret"
     *             ),
     *              @OA\Property(
     *                 property="openpayd_username",
     *                 type="string",
     *                 description="Openpayd payment username",
     *             ),
     *             @OA\Property(
     *                 property="openpayd_password",
     *                 type="string",
     *                 description="Openpayd payment password",
     *             ),
     *             @OA\Property(
     *                 property="openpayd_url",
     *                 type="string",
     *                 description="Openpayd payment url",
     *             ),
     *             @OA\Property(
     *                 property="openpayd_public_key_path",
     *                 type="string",
     *                 description="Openpayd payment public key path"
     *             ),
     *             @OA\Property(
     *                 property="coinbase_api_key",
     *                 type="string",
     *                 description="Coinbase API key",
     *             ),
     *             @OA\Property(
     *                 property="coinbase_webhook_key",
     *                 type="string",
     *                 description="Coinbase webhook key",
     *             ),
     *             @OA\Property(
     *                 property="coinbase_redirect_url",
     *                 type="string",
     *                 description="Coinbase redirect URL",
     *             ),
     *             @OA\Property(
     *                 property="coinbase_cancel_url",
     *                 type="string",
     *                 description="Coinbase cancel url"
     *             ),
     *             @OA\Property(
     *                 property="bitpay_environment",
     *                 type="string",
     *                 description="Bitpay environment"
     *             ),
     *             @OA\Property(
     *                 property="bitpay_api_token_merchant",
     *                 type="string",
     *                 description="Bitpay api token merchant"
     *             ),
     *             @OA\Property(
     *                 property="bitpay_api_token_payroll",
     *                 type="string",
     *                 description="Bitpay api token payroll"
     *             ),
     *              @OA\Property(
     *                 property="bitpay_key_path",
     *                 type="string",
     *                 description="Bitpay key path"
     *             ),
     *             @OA\Property(
     *                 property="bitpay_private_key_password",
     *                 type="string",
     *                 description="Bitpay private key password"
     *             ),
     *             @OA\Property(
     *                 property="bitpay_payment_webhook_url",
     *                 type="string",
     *                 description="Bitpay payment webhook url"
     *             ),
     *             @OA\Property(
     *                 property="bitpay_redirect_url",
     *                 type="string",
     *                 description="Bitpay redirect url"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 description="Currently in use settings",
     *                 default= 1,
     *             )
     *         )
     *     ),
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

    public function update($id, Request $request)
    {
        $saved              = null;
        $resp['data']       = [];
         // Validate inputs
         try {
            $this->validate($request, [
                'payment_gateway_name'  => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type'      => 'warning',
                'title'     => 'Payement gateway settings',
                'message'   => 'Validation error',
                'data'      => $e->getMessage()
            ], 400);
        }
        try {
            $gatewaySettings = ManageSettingsModel::findOrFail($id);
            $saved =  $gatewaySettings->update($request->all());
            if($saved)
            {
                $resp['message'] = "Successfully updated";
                $resp['title']   = "Payment gateway settings";
                $resp['type']    = "success";
                $resp['data']    = ManageSettingsModel::findOrFail($id);
                return response()->json($resp, 200);
            }else{
                $resp['message']    = "Unable to update payment gateway settings";
                $resp['title']      = "Payment gateway settings";
                $resp['type']       = "warning";
                $resp['data']       = [];
                return response()->json($resp, 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type'      => 'danger',
                'title'     => 'Update payement gateway details',
                'message'   => $e->getMessage()
            ], 400);
        }
    }


     /**
     * Method to delete payment gateway settings
     *
     * @OA\Delete(
     *     path="/admin/gateway/{id}/settings",
     *     description="method to delete payment gateway settings",
     *     tags={"Admin / Gateway / Settings"},
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
     *             @OA\Property(
     *                 property="id",
     *                 type="string",
     *                 description="primary key to the record",
     *             ),
     *         @OA\Parameter(
     *          name="limit",
    *           description="Count of orders in response",
    *           in="query",
    *           required=false,
    *           @OA\Schema(
    *              type="integer",
    *              default=20,
    *           )
    *        ),
    *        @OA\Parameter(
    *           name="page",
    *           description="Page of list",
    *           in="query",
    *           required=false,
    *           @OA\Schema(
    *              type="integer",
    *              default=1,
    *           )
    *           ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     )
     * )
     * @param \Illuminate\Http\Request $request
     * @param                          $id
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    public function destroy($id, Request $request)
    {
        $saved              = null;
        $resp['data']       = [];

        try {
            $deleted =  ManageSettingsModel::findOrFail($id)->delete();
            if($deleted)
            {
                $resp['message']    = "Payment gateway settings was deleted";
                $resp['title']      = "Payment gateway settings";
                $resp['type']       = "success";
                $resp['data']       = ManageSettingsModel::orderBy('created_at', 'Desc')
                                    ->paginate($request->get('limit', 20));
                return response()->json($resp, 200);
            }else{
                $resp['message']    = "Unable to delete payment gateway settings";
                $resp['title']      = "Payment gateway settings";
                $resp['type']       = "warning";
                $resp['data']       = [];
                return response()->json($resp, 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type'      => 'danger',
                'title'     => 'Delete payement gateway details',
                'message'   => $e->getMessage()
            ], 400);
        }
    }


}
