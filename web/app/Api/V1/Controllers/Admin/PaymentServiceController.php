<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentService;
use App\Models\Setting;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

/**
 * Class PaymentServiceController
 *
 * @package App\Api\V1\Controllers
 */
class PaymentServiceController extends Controller
{
    /**
     * Display list of all payment service
     *
     * @OA\Get(
     *     path="/admin/payment-services",
     *     description="Display list of all payment service",
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
            $services = PaymentService::query()
                ->orderBy('title')
                ->paginate($request->get('limit', config('settings.pagination_limit')));

            return response()->jsonApi([
                'title' => 'Display all payment service',
                'message' => 'List of all payment service successfully received',
                'data' => $services
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Display all payment service',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Method to add new payment service
     *
     * @OA\Post(
     *     path="/admin/payment-services",
     *     description="method to add new payment service",
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
     *                 description="Name of the payment service",
     *             ),
     *             @OA\Property(
     *                 property="key",
     *                 type="string",
     *                 description="The payment service processing key",
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="Details of the payment service",
     *             ),
     *             @OA\Property(
     *                 property="new_order_status",
     *                 type="integer",
     *                 description="Currently in use settings",
     *             ),
     *             @OA\Property(
     *                 property="settings",
     *                 type="array",
     *                 description="Payment service configuration",
     *
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
     */
    public function store(Request $request)
    {
        $is_saved = null;

        try {
            // Validate inputs
            $this->validate($request, [
                'name' => 'required|string',
                'key' => 'required|string',
                'description' => 'required|string',
                //'new_order_status'  => 'required|integer',
            ]);

//            $this->validate($request, [
//                'key' => 'required|string',
//                'value' => 'required|string',
//                'payment_service_id' => 'required|string'
//            ]);

        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'Payment service',
                'message' => 'Validation error',
                'data' => $e->getMessage()
            ], 400);
        }

        try {
            $paymentService = PaymentService::create($request->all());


            $setting = new Setting;
            $setting->key = $request['key'];
            $setting->value = $request['value'];
            $paymentService->settings()->save($setting);


            return response()->jsonApi([
                'title' => 'Payment service',
                'message' => 'New payment service was added',
                'data' => $paymentService
            ]);

            //'title' => 'Failed to add new payment setting',

        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Failed to add new payment service',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display payment service details
     *
     * @OA\Get(
     *     path="/admin/payment-services/{id}",
     *     description="show payment service details",
     *     tags={"Admin | Payment Services"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Payment service provider ID",
     *         required=true,
     *         example="96c890e5-7246-4714-a4db-70b63b16c8ef"
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     )
     * )
     *
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $paymentService = PaymentService::query()
                ->with('settings')
                ->where('id', $id)
                ->first();

            if ($paymentService) {
                return response()->jsonApi([
                    'title' => 'Payment service details',
                    'message' => 'Payment service details',
                    'data' => $paymentService
                ]);
            } else {
                return response()->jsonApi([
                    'title' => 'Payment service details',
                    'message' => 'Payment service with given ID not found',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Payment service details',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Method to update payment System
     *
     * @OA\Put(
     *     path="/admin/payment-services/{id}",
     *     description="method to update payment service",
     *     tags={"Admin | Payment Services"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Payment service provider ID",
     *         required=true,
     *         example="96c890e5-7246-4714-a4db-70b63b16c8ef"
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 description="Name of the payment service",
     *             ),
     *             @OA\Property(
     *                 property="key",
     *                 type="string",
     *                 description="The payment service processing key",
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="details of the payment service",
     *             ),
     *             @OA\Property(
     *                 property="new_order_status",
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
                'key' => 'required|string',
                'description' => 'required|string',
                //'new_order_status'  => 'required|integer',
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'title' => 'Payment service',
                'message' => 'Validation error',
                'data' => $e->getMessage()
            ], 400);
        }

        try {
            $paymentService = PaymentService::findOrFail($id);
            $saved = $paymentService->update($request->all());

            if ($saved) {
                $resp['message'] = "Successfully updated";
                $resp['title'] = "Payment service";

                $resp['data'] = PaymentService::findOrFail($id);

                return response()->jsonApi($resp, 200);
            } else {
                $resp['message'] = "Unable to update payment service";
                $resp['title'] = "Payment service";

                return response()->jsonApi($resp, 400);
            }
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Update payment service details',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Method to delete payment service
     *
     * @OA\Delete(
     *     path="/admin/payment-services/{id}",
     *     description="Delete payment service",
     *     tags={"Admin | Payment Services"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Payment service provider ID",
     *         required=true,
     *         example="96c890e5-7246-4714-a4db-70b63b16c8ef"
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            PaymentService::findOrFail($id)->delete();

            return response()->jsonApi([
                'title' => 'Delete payment service',
                'message' => 'Payment service was deleted'
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Delete payment service',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
