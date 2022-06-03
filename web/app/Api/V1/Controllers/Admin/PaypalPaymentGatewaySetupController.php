<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaypalPaymentGatewaySetup as PaypalPaymentGatewayModel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
     *     tags={"Admin / Settings / Paypal"},
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
                $resp['title']      = "Display Paypal Payment gateway settings";
                $resp['type']       = "Success";
                $resp['data']       = PaypalPaymentGatewayModel::orderBy('created_at', 'Desc')
                                    ->paginate($request->get('limit', 20));
                return response()->json($resp, 200);
            } catch (\Exception $e) {
                    return response()->json([
                        'type'  => 'danger',
                        'title'  => 'List paypal payment gateway settings',
                        'message' => $e->getMessage()
                    ], 400);
            }
    }



     /**
     * Display paypal payment gateway settings details
     *
     * @OA\Get(
     *     path="/admin/settings/{id}/paypal",
     *     description="show paypal payment gateway settings details",
     *     tags={"Admin / Settings / Paypal"},
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
     * @param  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
            $resp['data']    = [];
            try {
                $resp['message']  = "Payment gateway setting details";
                $resp['title']    = "paypal Payment gateway settings";
                $resp['type']     = "success";
                $resp['data']     = PaypalPaymentGatewayModel::findOrFail($id);
                return response()->json($resp, 200);
            } catch (\Exception $e) {
                    return response()->json([
                        'type'      => 'danger',
                        'title'     => 'Paypal payement gateway Details',
                        'message'   => $e->getMessage()
                    ], 400);
            }
    }



    /**
     * Method to add new paypal payment gateway settings
     *
     * @OA\Post(
     *     path="/admin/settings/paypal",
     *     description="method to add new paypal payment gateway settings",
     *     tags={"Admin / Settings / Paypal"},
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
     *                 property="mode",
     *                 type="string",
     *                 description="payment mode",
     *             ),
     *             @OA\Property(
     *                 property="notify_url",
     *                 type="string",
     *                 description="Payment notify url",
     *             ),
     *             @OA\Property(
     *                 property="currency",
     *                 type="string",
     *                 description="payment currency type",
     *                 default= "GBP",
     *             ),
     *             @OA\Property(
     *                 property="sandbox_client_id",
     *                 type="string",
     *                 description="payment sandbox client ID"
     *             ),
     *             @OA\Property(
     *                 property="sandbox_client_secret",
     *                 type="string",
     *                 description="payment sandbox client secret"
     *             ),
     *             @OA\Property(
     *                 property="live_client_id",
     *                 type="string",
     *                 description="payment live client ID"
     *             ),
     *             @OA\Property(
     *                 property="live_client_secret",
     *                 type="string",
     *                 description="payment live client secret"
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
                'mode'                  => 'required|string',
                'notify_url'            => 'required|string',
                //'currency'              => 'required|string',
                //'sandbox_client_id'     => 'required|string',
                //'sandbox_client_secret' => 'required|string',
                'live_client_id'        => 'required|string',
                'live_client_secret'    => 'required|string',
                //'status'                => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type'      => 'warning',
                'title'     => 'paypal payement gateway details',
                'message'   => 'Validation error',
                'data'      => $e->getMessage() // $validation->errors()->toJson()
            ], 400);
        }
        try {
            $saved =  PaypalPaymentGatewayModel::create($request->all());
            if($saved)
            {
                $resp['message'] = "New payment gateway setting was added";
                $resp['title']   = "paypal Payment gateway settings";
                $resp['type']    = "success";
                $resp['data']    = PaypalPaymentGatewayModel::where('id', $saved->id)->first();
                return response()->json($resp, 200);
            }else{
                $resp['message']  = "Unable to create payment gateway settings";
                $resp['title']    = "paypal Payment gateway settings";
                $resp['type']     = "warning";
                return response()->json($resp, 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type'      => 'danger',
                'title'     => 'Failed to add new paypal payement gateway settings',
                'message'   => $e->getMessage()
            ], 400);
        }
    }


    /**
     * Method to update paypal payment gateway settings
     *
     * @OA\Put(
     *     path="/admin/settings/{id}/paypal",
     *     description="method to update paypal payment gateway settings",
     *     tags={"Admin / Settings / Paypal"},
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
     *             @OA\Property(
     *                 property="mode",
     *                 type="string",
     *                 description="payment mode",
     *             ),
     *             @OA\Property(
     *                 property="notify_url",
     *                 type="string",
     *                 description="Payment notify url",
     *             ),
     *             @OA\Property(
     *                 property="currency",
     *                 type="string",
     *                 description="payment currency type",
     *                 default= "GBP",
     *             ),
     *             @OA\Property(
     *                 property="sandbox_client_id",
     *                 type="string",
     *                 description="payment sandbox client ID"
     *             ),
     *             @OA\Property(
     *                 property="sandbox_client_secret",
     *                 type="string",
     *                 description="payment sandbox client secret"
     *             ),
     *             @OA\Property(
     *                 property="live_client_id",
     *                 type="string",
     *                 description="payment live client ID"
     *             ),
     *             @OA\Property(
     *                 property="live_client_secret",
     *                 type="string",
     *                 description="payment live client secret"
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
                'mode'                  => 'required|string',
                'notify_url'            => 'required|string',
                'live_client_id'        => 'required|string',
                'live_client_secret'    => 'required|string',
                'sandbox_api_url'       => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type'      => 'warning',
                'title'     => 'paypal payement gateway details',
                'message'   => 'Validation error',
                'data'      => $e->getMessage()
            ], 400);
        }
        try {
            $gatewaySettings = PaypalPaymentGatewayModel::findOrFail($id);
            $saved =  $gatewaySettings->update($request->all());
            if($saved)
            {
                $resp['message'] = "Successfully updated";
                $resp['title']   = "Update paypal Payment gateway settings";
                $resp['type']    = "success";
                $resp['data']    = PaypalPaymentGatewayModel::findOrFail($id);
                return response()->json($resp, 200);
            }else{
                $resp['message']    = "Unable to update payment gateway settings";
                $resp['title']      = "Unable to paypal Payment gateway settings";
                $resp['type']       = "warning";
                $resp['data']       = [];
                return response()->json($resp, 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type'      => 'danger',
                'title'     => 'Failed to update paypal payement gateway details',
                'message'   => $e->getMessage()
            ], 400);
        }
    }


     /**
     * Method to delete paypal payment gateway settings
     *
     * @OA\Delete(
     *     path="/admin/settings/{id}/paypal",
     *     description="method to delete paypal payment gateway settings",
     *     tags={"Admin / Settings / Paypal"},
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
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     )
     * )
     *
     * @param                          $id
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    public function destroy($id)
    {
        $saved              = null;
        $resp['data']       = [];

        try {
            $deleted =  PaypalPaymentGatewayModel::findOrFail($id)->delete();
            if($deleted)
            {
                $resp['message'] = "Payment gateway settings was deleted";
                $resp['title']   = "paypal Payment gateway settings";
                $resp['type']    = "success";
                $resp['data']    = PaypalPaymentGatewayModel::all();
                return response()->json($resp, 200);
            }else{
                $resp['message']    = "Unable to delete payment gateway settings";
                $resp['title']      = "paypal Payment gateway settings";
                $resp['type']       = "warning";
                $resp['data']       = [];
                return response()->json($resp, 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type'      => 'danger',
                'title'     => 'Delete paypal payement gateway details',
                'message'   => $e->getMessage()
            ], 400);
        }
    }


}
