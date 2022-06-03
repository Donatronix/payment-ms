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
     *     tags={"Admin / Settings / Openpayd"},
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

    /**
     * Display openpayd payment gateway settings details
     *
     * @OA\Get(
     *     path="/admin/settings/{id}/openpayd",
     *     description="show openpayd payment gateway settings details",
     *     tags={"Admin / Settings / Openpayd"},
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
                $resp['title']    = "Openpayd Payment gateway settings";
                $resp['type']     = "success";
                $resp['data']     = OpenpaydPaymentGatewayModel::findOrFail($id);
                return response()->json($resp, 200);
            } catch (\Exception $e) {
                    return response()->json([
                        'type'      => 'danger',
                        'title'     => 'Openpayd payement gateway Details',
                        'message'   => $e->getMessage()
                    ], 400);
            }
    }



    /**
     * Method to add new openpayd payment gateway settings
     *
     * @OA\Post(
     *     path="/admin/settings/openpayd",
     *     description="method to add new openpayd payment gateway settings",
     *     tags={"Admin / Settings / Openpayd"},
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
     *                 property="username",
     *                 type="string",
     *                 description="Openpayd payment username",
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 description="Openpayd payment password",
     *             ),
     *             @OA\Property(
     *                 property="url",
     *                 type="string",
     *                 description="Openpayd payment url",
     *             ),
     *             @OA\Property(
     *                 property="public_key_path",
     *                 type="string",
     *                 description="Openpayd payment public key path"
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
                'username'          => 'required|string',
                'password'          => 'required|string',
                'public_key_path'   => 'required|string',
                'url'               => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type'      => 'warning',
                'title'     => 'Openpayd payement gateway details',
                'message'   => 'Validation error',
                'data'      => $e->getMessage()
            ], 400);
        }
        try {
            $saved =  OpenpaydPaymentGatewayModel::create($request->all());
            if($saved)
            {
                $resp['message'] = "New payment gateway setting was added";
                $resp['title']   = "Openpayd Payment gateway settings";
                $resp['type']    = "success";
                $resp['data']    = OpenpaydPaymentGatewayModel::where('id', $saved->id)->first();
                return response()->json($resp, 200);
            }else{
                $resp['message']  = "Unable to create payment gateway settings";
                $resp['title']    = "Openpayd Payment gateway settings";
                $resp['type']     = "warning";
                return response()->json($resp, 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type'      => 'danger',
                'title'     => 'Failed to add new openpayd payement gateway settings',
                'message'   => $e->getMessage()
            ], 400);
        }
    }


    /**
     * Method to update openpayd payment gateway settings
     *
     * @OA\Put(
     *     path="/admin/settings/{id}/openpayd",
     *     description="method to update openpayd payment gateway settings",
     *     tags={"Admin / Settings / Openpayd"},
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
     *                 property="username",
     *                 type="string",
     *                 description="Openpayd payment username",
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 description="Openpayd payment password",
     *             ),
     *             @OA\Property(
     *                 property="url",
     *                 type="string",
     *                 description="Openpayd payment url",
     *             ),
     *             @OA\Property(
     *                 property="public_key_path",
     *                 type="string",
     *                 description="Openpayd payment public key path"
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
                'username'          => 'required|string',
                'password'          => 'required|string',
                'public_key_path'   => 'required|string',
                'url'               => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type'      => 'warning',
                'title'     => 'Openpayd payement gateway details',
                'message'   => 'Validation error',
                'data'      => $e->getMessage()
            ], 400);
        }
        try {
            $gatewaySettings = OpenpaydPaymentGatewayModel::findOrFail($id);
            $saved =  $gatewaySettings->update($request->all());
            if($saved)
            {
                $resp['message'] = "Successfully updated";
                $resp['title']   = "Update openpayd Payment gateway settings";
                $resp['type']    = "success";
                $resp['data']    = OpenpaydPaymentGatewayModel::findOrFail($id);
                return response()->json($resp, 200);
            }else{
                $resp['message']    = "Unable to update payment gateway settings";
                $resp['title']      = "Unable to openpayd Payment gateway settings";
                $resp['type']       = "warning";
                $resp['data']       = [];
                return response()->json($resp, 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type'      => 'danger',
                'title'     => 'Failed to update openpayd payement gateway details',
                'message'   => $e->getMessage()
            ], 400);
        }
    }


     /**
     * Method to delete openpayd payment gateway settings
     *
     * @OA\Delete(
     *     path="/admin/settings/{id}/openpayd",
     *     description="method to delete openpayd payment gateway settings",
     *     tags={"Admin / Settings / Openpayd"},
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
            $deleted =  OpenpaydPaymentGatewayModel::findOrFail($id)->delete();
            if($deleted)
            {
                $resp['message'] = "Payment gateway settings was deleted";
                $resp['title']   = "Openpayd Payment gateway settings";
                $resp['type']    = "success";
                $resp['data']    = OpenpaydPaymentGatewayModel::all();
                return response()->json($resp, 200);
            }else{
                $resp['message']    = "Unable to delete payment gateway settings";
                $resp['title']      = "Openpayd Payment gateway settings";
                $resp['type']       = "warning";
                $resp['data']       = [];
                return response()->json($resp, 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type'      => 'danger',
                'title'     => 'Delete openpayd payement gateway details',
                'message'   => $e->getMessage()
            ], 400);
        }
    }

}//end class
