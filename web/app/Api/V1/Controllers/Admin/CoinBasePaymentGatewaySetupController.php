<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoinBasePaymentGatewaySetup as CoinBasePaymentGatewayModel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Class CoinBasePaymentGatewaySetupController
 *
 * @package App\Api\V1\Controllers
 */

class CoinBasePaymentGatewaySetupController extends Controller
{


     /**
     * Display list of all coinbase payment gateway settings
     *
     * @OA\Get(
     *     path="/admin/settings/coinbase",
     *     description="Display list of all coinbase payment gateway settings",
     *     tags={"Admin / Settings / Coinbase"},
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
                $resp['message']    = "List of all coinbase payment gateway settings";
                $resp['title']      = "Display coinbase Payment gateway settings";
                $resp['type']       = "Success";
                $resp['data']       = CoinBasePaymentGatewayModel::orderBy('created_at', 'Desc')
                                    ->paginate($request->get('limit', 20));
                return response()->json($resp, 200);
            } catch (\Exception $e) {
                    return response()->json([
                        'type'  => 'danger',
                        'title'  => 'List coinbase payment gateway settings',
                        'message' => $e->getMessage()
                    ], 400);
            }
    }

    /**
     * Display coinbase payment gateway settings details
     *
     * @OA\Get(
     *     path="/admin/settings/{id}/coinbase",
     *     description="show coinbase payment gateway settings details",
     *     tags={"Admin / Settings / Coinbase"},
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
     * @param    $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
            $resp['data']    = [];
            try {
                $resp['message']  = "Payment gateway setting details";
                $resp['title']    = "Coinbase Payment gateway settings";
                $resp['type']     = "success";
                $resp['data']     = CoinBasePaymentGatewayModel::findOrFail($id);
                return response()->json($resp, 200);
            } catch (\Exception $e) {
                    return response()->json([
                        'type'      => 'danger',
                        'title'     => 'Coinbase payement gateway Details',
                        'message'   => $e->getMessage()
                    ], 400);
            }
    }



    /**
     * Method to add new coinbase payment gateway settings
     *
     * @OA\Post(
     *     path="/admin/settings/coinbase",
     *     description="method to add new coinbase payment gateway settings",
     *     tags={"Admin / Settings / Coinbase"},
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
     *                 property="api_key",
     *                 type="string",
     *                 description="Coinbase API key",
     *             ),
     *             @OA\Property(
     *                 property="webhook_key",
     *                 type="string",
     *                 description="Coinbase webhook key",
     *             ),
     *             @OA\Property(
     *                 property="redirect_url",
     *                 type="string",
     *                 description="Coinbase redirect URL",
     *             ),
     *             @OA\Property(
     *                 property="cancel_url",
     *                 type="string",
     *                 description="Coinbase cancel url"
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
                'api_key'          => 'required|string',
                'webhook_key'      => 'required|string',
                'redirect_url'     => 'required|string',
                'cancel_url'       => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type'      => 'warning',
                'title'     => 'Coinbase payement gateway details',
                'message'   => 'Validation error',
                'data'      => $e->getMessage()
            ], 400);
        }
        try {
            $saved =  CoinBasePaymentGatewayModel::create($request->all());
            if($saved)
            {
                $resp['message'] = "New payment gateway setting was added";
                $resp['title']   = "Coinbase Payment gateway settings";
                $resp['type']    = "success";
                $resp['data']    = CoinBasePaymentGatewayModel::where('id', $saved->id)->first();
                return response()->json($resp, 200);
            }else{
                $resp['message']  = "Unable to create payment gateway settings";
                $resp['title']    = "Coinbase Payment gateway settings";
                $resp['type']     = "warning";
                return response()->json($resp, 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type'      => 'danger',
                'title'     => 'Failed to add new coinbase payement gateway settings',
                'message'   => $e->getMessage()
            ], 400);
        }
    }


    /**
     * Method to update coinbase payment gateway settings
     *
     * @OA\Put(
     *     path="/admin/settings/{id}/coinbase",
     *     description="method to update coinbase payment gateway settings",
     *     tags={"Admin / Settings / Coinbase"},
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
     *                 property="api_key",
     *                 type="string",
     *                 description="Coinbase API key",
     *             ),
     *             @OA\Property(
     *                 property="webhook_key",
     *                 type="string",
     *                 description="Coinbase webhook key",
     *             ),
     *             @OA\Property(
     *                 property="redirect_url",
     *                 type="string",
     *                 description="Coinbase redirect URL",
     *             ),
     *             @OA\Property(
     *                 property="cancel_url",
     *                 type="string",
     *                 description="Coinbase cancel url"
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
                'api_key'          => 'required|string',
                'webhook_key'      => 'required|string',
                'redirect_url'     => 'required|string',
                'cancel_url'       => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type'      => 'warning',
                'title'     => 'Coinbase payement gateway details',
                'message'   => 'Validation error',
                'data'      => $e->getMessage()
            ], 400);
        }
        try {
            $gatewaySettings = CoinBasePaymentGatewayModel::findOrFail($id);
            $saved =  $gatewaySettings->update($request->all());
            if($saved)
            {
                $resp['message'] = "Successfully updated";
                $resp['title']   = "Update coinbase Payment gateway settings";
                $resp['type']    = "success";
                $resp['data']    = CoinBasePaymentGatewayModel::findOrFail($id);
                return response()->json($resp, 200);
            }else{
                $resp['message']    = "Unable to update payment gateway settings";
                $resp['title']      = "Unable to coinbase Payment gateway settings";
                $resp['type']       = "warning";
                $resp['data']       = [];
                return response()->json($resp, 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type'      => 'danger',
                'title'     => 'Failed to update coinbase payement gateway details',
                'message'   => $e->getMessage()
            ], 400);
        }
    }


     /**
     * Method to delete coinbase payment gateway settings
     *
     * @OA\Delete(
     *     path="/admin/settings/{id}/coinbase",
     *     description="method to delete coinbase payment gateway settings",
     *     tags={"Admin / Settings / Coinbase"},
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
            $deleted =  CoinBasePaymentGatewayModel::findOrFail($id)->delete();
            if($deleted)
            {
                $resp['message'] = "Payment gateway settings was deleted";
                $resp['title']   = "Coinbase Payment gateway settings";
                $resp['type']    = "success";
                $resp['data']    = CoinBasePaymentGatewayModel::all();
                return response()->json($resp, 200);
            }else{
                $resp['message']    = "Unable to delete payment gateway settings";
                $resp['title']      = "Coinbase Payment gateway settings";
                $resp['type']       = "warning";
                $resp['data']       = [];
                return response()->json($resp, 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type'      => 'danger',
                'title'     => 'Delete coinbase payement gateway details',
                'message'   => $e->getMessage()
            ], 400);
        }
    }

}//end class
