<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentService;
use App\Models\Setting;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class PaymentServiceController
 *
 * @package App\Api\V1\Controllers
 */
class PaymentServiceController extends Controller
{
    /**
     * Display list of all payment system
     *
     * @OA\Get(
     *     path="/admin/payment-services",
     *     description="Display list of all payment gateway settings",
     *     tags={"Admin | Payment Services"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
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
     *         response="200",
     *         description="Success",
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $resp['data'] = PaymentService::orderBy('name', 'Asc')
                ->paginate($request->get('limit', 20));

            return response()->jsonApi([
                'title' => 'Display all payment system',
                'message' => 'List of all payment system'
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Display all payment system',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display payment system details
     *
     * @OA\Get(
     *     path="/admin/{id}/payment-services",
     *     description="show payment system details",
     *     tags={"Admin | Payment Services"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *            @OA\Property(
     *                 property="id",
     *                 type="string",
     *                 description="primary key to the record",
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $paymentSystem = PaymentService::with('settings')->findOrFail($id);

            return response()->jsonApi([
                'title' => 'Payment system details',
                'message' => 'Payment system details',
                'data' => $paymentSystem
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Payement system Details',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Method to add new payment system
     *
     * @OA\Post(
     *     path="/admin/payment-services",
     *     description="method to add new payment system",
     *     tags={"Admin | Payment Services"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 description="Name of the payment system",
     *             ),
     *             @OA\Property(
     *                 property="gateway",
     *                 type="string",
     *                 description="The payment system gateway",
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="details of the payment system",
     *             ),
     *             @OA\Property(
     *                 property="new_status",
     *                 type="integer",
     *                 description="Currently in use settings",
     *             )
     *
     *             @OA\Property(
     *                 property="settings",
     *                 type="array",
     *                 description="Payment service configuration",
     *                 @OA\Items(
     *                     type="object",
     *
     *                     @OA\Property(
     *                         property="key",
     *                         type="string",
     *                         description="Setting key",
     *                     ),
     *                     @OA\Property(
     *                         property="value",
     *                         type="string",
     *                         description="Setting value",
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     )
     * )
     *
     * @param Request $request
     *
     * @throws ValidationException
     */

    public function store(Request $request)
    {
        $is_saved = null;

        try {
            // Validate inputs
            $this->validate($request, [
                'name' => 'required|string',
                'gateway' => 'required|string',
                'description' => 'required|string',
                //'new_status'  => 'required|integer',
            ]);

//            $this->validate($request, [
//                'key' => 'required|string',
//                'value' => 'required|string',
//                'payment_service_id' => 'required|string'
//            ]);

        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'Payement system',
                'message' => 'Validation error',
                'data' => $e->getMessage()
            ], 400);
        }

        try {
            $paymentSystem = PaymentService::create($request->all());


            $setting = new Setting;
            $setting->key = $request['key'];
            $setting->value = $request['value'];
            $paymentSystem->settings()->save($setting);


            return response()->jsonApi([
                'title' => 'Payment system',
                'message' => 'New payment system was added',
                'data' => $paymentSystem
            ]);

            //'title' => 'Failed to add new payment setting',

        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Failed to add new payement system',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Method to update payment System
     *
     * @OA\Put(
     *     path="/admin/{id}/payment-services",
     *     description="method to update payment system",
     *     tags={"Admin | Payment Services"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="id",
     *                 type="string",
     *                 description="primary key to the record",
     *             ),
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 description="Name of the payment system",
     *             ),
     *             @OA\Property(
     *                 property="gateway",
     *                 type="string",
     *                 description="The payment system gateway",
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="details of the payment system",
     *             ),
     *             @OA\Property(
     *                 property="new_status",
     *                 type="integer",
     *                 description="Currently in use settings",
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     )
     * )
     *
     * @param Request $request
     * @param                          $id
     *
     * @throws ValidationException
     */

    public function update($id, Request $request)
    {
        $saved = null;
        $resp['data'] = [];
        // Validate inputs
        try {
            $this->validate($request, [
                'name' => 'required|string',
                'gateway' => 'required|string',
                'description' => 'required|string',
                //'new_status'  => 'required|integer',
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'title' => 'Payement system',
                'message' => 'Validation error',
                'data' => $e->getMessage()
            ], 400);
        }

        try {
            $paymentSystem = PaymentService::findOrFail($id);
            $saved = $paymentSystem->update($request->all());

            if ($saved) {
                $resp['message'] = "Successfully updated";
                $resp['title'] = "Payment system";

                $resp['data'] = PaymentService::findOrFail($id);
                return response()->jsonApi($resp, 200);
            } else {
                $resp['message'] = "Unable to update payment system";
                $resp['title'] = "Payment system";

                return response()->jsonApi($resp, 400);
            }
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Update payement system details',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Method to delete payment system
     *
     * @OA\Delete(
     *     path="/admin/{id}/payment-services",
     *     description="method to delete payment gateway settings",
     *     tags={"Admin | Payment Services"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="id",
     *                 type="string",
     *                 description="payment system id",
     *             ),
     *             @OA\Parameter(
     *             name="limit",
     *             description="Count of orders in response",
     *             in="query",
     *             required=false,
     *             @OA\Schema(
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
     *         response="200",
     *         description="Success",
     *     )
     * )
     * @param Request $request
     * @param                          $id
     *
     * @throws ValidationException
     */

    public function destroy($id, Request $request)
    {
        $saved = null;

        try {
            $deleted = PaymentService::findOrFail($id)->delete();
            if ($deleted) {
                $resp['message'] = "Payment system was deleted";
                $resp['title'] = "Payment system";

                $resp['data'] = PaymentService::orderBy('name', 'Asc')
                    ->paginate($request->get('limit', 20));
                return response()->jsonApi($resp, 200);
            } else {
                $resp['message'] = "Unable to delete payment system";
                $resp['title'] = "Payment system";

                return response()->jsonApi($resp, 400);
            }
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Delete payement system',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
