<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSystem;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class PaymentSystemController
 *
 * @package App\Api\V1\Controllers
 */
class PaymentSystemController extends Controller
{
    /**
     * Display list of all payment system
     *
     * @OA\Get(
     *     path="/admin/payment-system",
     *     description="Display list of all payment gateway settings",
     *     tags={"Admin / Payment-System"},
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
        $resp['data'] = [];
        try {
            $resp['message'] = "List of all payment system";
            $resp['title'] = "Display all payment system";
            $resp['type'] = "Success";
            $resp['data'] = PaymentSystem::orderBy('name', 'Asc')->with('payment_settings')
                ->paginate($request->get('limit', 20));

            return response()->jsonApi($resp, 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Display all payment system',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display payment system details
     *
     * @OA\Get(
     *     path="/admin/{id}/payment-system",
     *     description="show payment system details",
     *     tags={"Admin / Payment-System"},
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
        $resp['data'] = [];
        try {
            $resp['message'] = "Payment system details";
            $resp['title'] = "Payment system details";
            $resp['type'] = "success";
            $paymentSystem = PaymentSystem::findOrFail($id);
            $resp['data'] = $paymentSystem->payment_settings;

            return response()->jsonApi($resp, 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Payement system Details',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Method to add new payment system
     *
     * @OA\Post(
     *     path="/admin/payment-system",
     *     description="method to add new payment system",
     *     tags={"Admin / Payment-System"},
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
                'type' => 'warning',
                'title' => 'Payement system',
                'message' => 'Validation error',
                'data' => $e->getMessage()
            ], 400);
        }
        try {
            $paymentSystem = PaymentSystem::create($request->all());
            if ($paymentSystem) {
                $resp['message'] = "New payment system was added";
                $resp['title'] = "Payment system";
                $resp['type'] = "success";
                $resp['data'] = $paymentSystem;
                return response()->jsonApi($resp, 200);
            } else {
                $resp['message'] = "Unable to create payment system";
                $resp['title'] = "Payment system";
                $resp['type'] = "warning";
                return response()->jsonApi($resp, 400);
            }
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Failed to add new payement system',
                'message' => $e->getMessage()
            ], 400);
        }
    }


    /**
     * Method to update payment System
     *
     * @OA\Put(
     *     path="/admin/{id}/payment-system",
     *     description="method to update payment system",
     *     tags={"Admin / Payment-System"},
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
                'type' => 'warning',
                'title' => 'Payement system',
                'message' => 'Validation error',
                'data' => $e->getMessage()
            ], 400);
        }
        try {
            $paymentSystem = PaymentSystem::findOrFail($id);
            $saved = $paymentSystem->update($request->all());
            if ($saved) {
                $resp['message'] = "Successfully updated";
                $resp['title'] = "Payment system";
                $resp['type'] = "success";
                $resp['data'] = PaymentSystem::findOrFail($id);
                return response()->jsonApi($resp, 200);
            } else {
                $resp['message'] = "Unable to update payment system";
                $resp['title'] = "Payment system";
                $resp['type'] = "warning";
                $resp['data'] = [];
                return response()->jsonApi($resp, 400);
            }
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Update payement system details',
                'message' => $e->getMessage()
            ], 400);
        }
    }


    /**
     * Method to delete payment system
     *
     * @OA\Delete(
     *     path="/admin/{id}/payment-system",
     *     description="method to delete payment gateway settings",
     *     tags={"Admin / Payment-System"},
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
        $resp['data'] = [];

        try {
            $deleted = PaymentSystem::findOrFail($id)->delete();
            if ($deleted) {
                $resp['message'] = "Payment system was deleted";
                $resp['title'] = "Payment system";
                $resp['type'] = "success";
                $resp['data'] = PaymentSystem::orderBy('name', 'Asc')
                    ->paginate($request->get('limit', 20));
                return response()->jsonApi($resp, 200);
            } else {
                $resp['message'] = "Unable to delete payment system";
                $resp['title'] = "Payment system";
                $resp['type'] = "warning";
                $resp['data'] = [];
                return response()->jsonApi($resp, 400);
            }
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Delete payement system',
                'message' => $e->getMessage()
            ], 400);
        }
    }


}
