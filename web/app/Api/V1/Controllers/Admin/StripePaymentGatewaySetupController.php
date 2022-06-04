<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StripePaymentGatewaySetup as StripePaymentGatewayModel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

/**
 * Class StripePaymentGatewaySetupController
 *
 * @package App\Api\V1\Controllers
 */

class StripePaymentGatewaySetupController extends Controller
{


     /**
     * Display list of all stripe payment gateway settings
     *
     * @OA\Get(
     *     path="/admin/settings/stripe",
     *     description="Display list of all stripe payment gateway settings",
     *     tags={"Admin / Settings / Stripe"},
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
                $resp['title']      = "Display Stripe Payment gateway setting";
                $resp['type']       = "Success";
                $resp['data']       = StripePaymentGatewayModel::orderBy('created_at', 'Desc')
                                    ->paginate($request->get('limit', 20));
                return response()->json($resp, 200);
            } catch (\Exception $e) {
                    return response()->json([
                        'type'  => 'danger',
                        'title'  => 'Display Stripe payment gateway settings',
                        'message' => $e->getMessage()
                    ], 400);
            }
    }



     /**
     * Display stripe payment gateway settings details
     *
     * @OA\Get(
     *     path="/admin/settings/{id}/stripe",
     *     description="show stripe payment gateway settings details",
     *     tags={"Admin / Settings / Stripe"},
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
    public function show($id)
    {
            $resp['data']    = [];
            try {
                $resp['message']  = "Payment gateway setting details";
                $resp['title']    = "Stripe Payment gateway settings";
                $resp['type']     = "success";
                $resp['data']     = StripePaymentGatewayModel::findOrFail($id);
                return response()->json($resp, 200);
            } catch (\Exception $e) {
                    return response()->json([
                        'type'  => 'danger',
                        'title'     => 'Stripe payement gateway Details',
                        'message' => $e->getMessage()
                    ], 400);
            }
    }



    /**
     * Method to add new stripe payment gateway settings
     *
     * @OA\Post(
     *     path="/admin/settings/stripe",
     *     description="method to add new stripe payment gateway settings",
     *     tags={"Admin / Settings / Stripe"},
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
     *                 property="gateway_name",
     *                 type="string",
     *                 description="Name of the payment gateway",
     *                 default="stripe",
     *
     *             ),
     *             @OA\Property(
     *                 property="webhook_secret",
     *                 type="string",
     *                 description="The web hook secret",
     *             ),
     *             @OA\Property(
     *                 property="public_key",
     *                 type="string",
     *                 description="Publick key",
     *             ),
     *             @OA\Property(
     *                 property="secret_key",
     *                 type="string",
     *                 description="Secret key"
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
                'webhook_secret' => 'required|string',
                'public_key'     => 'required|string',
                'secret_key'     => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type'      => 'warning',
                'title'     => 'Stripe payement gateway details',
                'message'   => 'Validation error',
                'data'      => $e->getMessage() // $validation->errors()->toJson()
            ], 400);
        }
        try {
            $saved =  StripePaymentGatewayModel::create($request->all());
            if($saved)
            {
                $resp['message'] = "New payment gateway setting was added";
                $resp['title']   = "Stripe Payment gateway settings";
                $resp['type']    = "success";
                $resp['data']    = StripePaymentGatewayModel::where('id', $saved->id)->first();
                return response()->json($resp, 200);
            }else{
                $resp['message']  = "Unable to create payment gateway settings";
                $resp['title']    = "Stripe Payment gateway settings";
                $resp['type']     = "warning";
                return response()->json($resp, 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type'      => 'danger',
                'title'     => 'Failed to add new Stripe payement gateway settings',
                'message'   => $e->getMessage()
            ], 400);
        }
    }


    /**
     * Method to update stripe payment gateway settings
     *
     * @OA\Put(
     *     path="/admin/settings/{id}/stripe",
     *     description="method to update stripe payment gateway settings",
     *     tags={"Admin / Settings / Stripe"},
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
     *                 property="gateway_name",
     *                 type="string",
     *                 description="Name of the payment gateway",
     *                 default="stripe",
     *             ),
     *             @OA\Property(
     *                 property="webhook_secret",
     *                 type="string",
     *                 description="The web hook secret",
     *             ),
     *             @OA\Property(
     *                 property="public_key",
     *                 type="string",
     *                 description="Publick key",
     *             ),
     *             @OA\Property(
     *                 property="secret_key",
     *                 type="string",
     *                 description="Secret key"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 description="The currently in use settings",
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
                'webhook_secret' => 'required|string',
                'public_key'     => 'required|string',
                'secret_key'     => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type'      => 'warning',
                'title'     => 'Stripe payement gateway details',
                'message'   => "Validation error",
                'data'      => $e->getMessage() // $validation->errors()->toJson()
            ], 400);
        }
        try {
            $gatewaySettings = StripePaymentGatewayModel::findOrFail($id);
            $saved =  $gatewaySettings->update($request->all());
            if($saved)
            {
                $resp['message'] = "Successfully updated";
                $resp['title']   = "Stripe Payment gateway settings";
                $resp['type']    = "success";
                $resp['data']    = StripePaymentGatewayModel::findOrFail($id);
                return response()->json($resp, 200);
            }else{
                $resp['message']    = "Unable to update payment gateway settings";
                $resp['title']      = "Stripe Payment gateway settings";
                $resp['type']       = "warning";
                $resp['data']       = [];
                return response()->json($resp, 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type'      => 'danger',
                'title'     => 'Update Stripe payement gateway details',
                'message'   => $e->getMessage()
            ], 400);
        }
    }


     /**
     * Method to delete stripe payment gateway settings
     *
     * @OA\Delete(
     *     path="/admin/settings/{id}/stripe",
     *     description="method to delete stripe payment gateway settings",
     *     tags={"Admin / Settings / Stripe"},
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
            $deleted =  StripePaymentGatewayModel::findOrFail($id)->delete();
            if($deleted)
            {
                $resp['message'] = "Payment gateway settings was deleted";
                $resp['title']   = "Stripe Payment gateway settings";
                $resp['type']    = "success";
                $resp['data']    = StripePaymentGatewayModel::all();
                return response()->json($resp, 200);
            }else{
                $resp['message']    = "Unable to delete payment gateway settings";
                $resp['title']      = "Stripe Payment gateway settings";
                $resp['type']       = "warning";
                $resp['data']       = [];
                return response()->json($resp, 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type'      => 'danger',
                'title'     => 'Delete Stripe payement gateway details',
                'message'   => $e->getMessage()
            ], 400);
        }
    }


}
