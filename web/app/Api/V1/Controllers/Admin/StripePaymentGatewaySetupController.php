<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StripePaymentGatewaySetup;
use Illuminate\Http\Request;

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
            $data['message']    = "Unable to get payment gateway settings";
            $data['success']    = false;
            $data['gateway']    = [];
            try {
                $data['message'] = "Success";
                $data['success'] = true;
                $data['gateway'] = StripePaymentGatewaySetup::orderBy('created_at', 'Desc')->where('status', 1)->get();
                return response()->json($data, 200);
            } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'error' => $e->getMessage()
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
            $data               = array();
            $data['message']    = "Unable to get payment gateway settings";
            $data['success']    = false;
            $data['gateway']    = [];
            try {
                $data['message'] = "Success";
                $data['success'] = true;
                $data['gateway'] = StripePaymentGatewaySetup::where('id', $id)->where('status', 1)->first();
                return response()->json($data, 200);
            } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'error' => $e->getMessage()
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
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    public function store(Request $request)
    {
        $saved              = null;
        $data               = array();
        $data['message']    = "Unable to add payment gateway settings";
        $data['success']    = false;
        $data['gateway']    = [];

         // Validate input
         $this->validate($request, [
            'webhook_secret' => 'required|string',
            'public_key'     => 'required|string',
            'secret_key'     => 'required|string',
        ]);
        try {
            $saved = StripePaymentGatewaySetup::create(
            [
                'gateway_name'      => $request['gateway_name'],
                'webhook_secret'    => $request['webhook_secret'],
                'public_key'        => $request['public_key'],
                'secret_key'        => $request['secret_key'],
                'status'            => $request['status'],
            ]);
            if($saved)
            {
                $data['message'] = "Success";
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
        $data               = array();
        $data['message']    = "Unable to update payment gateway settings";
        $data['success']    = false;
        $data['gateway']    = [];

         // Validate inputs
         $this->validate($request, [
            'webhook_secret' => 'required|string',
            'public_key'     => 'required|string',
            'secret_key'     => 'required|string',
        ]);
        try {
            $gatewaySettings = StripePaymentGatewaySetup::findOrFail($id);
            $saved =  $gatewaySettings->update($request->all());
            if($saved)
            {
                $data['message'] = "Success";
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

    public function delete($id)
    {
        $saved              = null;
        $data               = array();
        $data['message']    = "Unable to delete payment gateway settings";
        $data['success']    = false;
        $data['gateway']    = [];

        try {
            $deleted =  StripePaymentGatewaySetup::findOrFail($id)->delete();
            if($deleted)
            {
                $data['message'] = "Success";
                $data['success'] = true;
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