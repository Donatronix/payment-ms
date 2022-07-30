<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Models\PaymentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
     *              default=20
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         description="Page of list",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="integer",
     *              default=1
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success"
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
            $resp['message'] = "List of all payment setting";
            $resp['title'] = "Display all payment setting";
            $resp['type'] = "Success";
            $resp['data'] = PaymentSetting::orderBy('name', 'Asc')
                ->paginate($request->get('limit', 20));

            return response()->jsonApi($resp, 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Display all payment setting',
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
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *            @OA\Property(
     *                 property="payment_service_id",
     *                 type="string",
     *                 description="payment system id",
     *             ),
     *             @OA\Property(
     *                 property="key",
     *                 type="string",
     *                 description="payment setting key",
     *             ),
     *             @OA\Property(
     *                 property="value",
     *                 type="string",
     *                 description="payment setting value",
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
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $resp['data'] = [];

        // Validate inputs
        try {
            $this->validate($request, [
                'key' => 'required|string',
                'value' => 'required|string',
                'payment_service_id' => 'required|string'
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
            $paymentSystem = PaymentService::findOrFail($request['payment_service_id']);

            if ($paymentSystem) {
                $setting = new PaymentSetting;
                $setting->key = $request['key'];
                $setting->value = $request['value'];
                $paymentSystem->payment_settings()->save($setting);

                $resp['message'] = "New payment setting was added";
                $resp['title'] = "Payment setting";
                $resp['type'] = "success";
                $resp['data'] = $paymentSystem->payment_settings;

                return response()->jsonApi($resp, 200);
            } else {
                $resp['message'] = "Unable to create payment setting";
                $resp['title'] = "Payment setting";
                $resp['type'] = "warning";

                return response()->jsonApi($resp, 400);
            }
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Failed to add new payment setting',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
