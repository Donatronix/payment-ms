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
                'title' => 'Display all payment service',
                'message' => 'List of all payment service'
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Display all payment service',
                'message' => $e->getMessage()
            ], 400);
        }
    }


    const CACHE_ID = "PAYSYSTEMLIST";

    public function __invoke2(): JsonResponse
    {
        $systems = Cache::get(self::CACHE_ID, []);

        if (!is_array($systems) || count($systems) == 0) {
            $systems = $this->catalog();

            Cache::put(self::CACHE_ID, $systems);
        }

        return response()->jsonApi([
            'type' => 'success',
            'title' => "Get Payment services list",
            'message' => "Payment services data successfully",
            'data' => $systems
        ]);
    }

    public function clear_cache()
    {
        Cache::forget(self::CACHE_ID);
    }

    public function catalog(): array
    {
        $systems = [];

        $dir = base_path('app/Services/PaymentServiceProviders');

        if ($handle = opendir($dir)) {
            /* Именно такой способ чтения элементов каталога является правильным. */
            while (false !== ($entry = readdir($handle))) {
                if (($entry == '.') || ($entry == '..'))
                    continue;

                $class = '\App\Services\PaymentServiceProviders\\' . preg_replace('/\.php/', '', $entry);

                if (!class_exists($class))
                    continue;

                try {
                    $gateway = $class::service();
                    $name = $class::name();
                    $description = $class::description();
                    $new_status = $class::newStatus();
                } catch (\Exception $e) {
                    $gateway = 'error';
                    $name = 'error';
                    $description = $entry . ' ' . $e->getMessage();
                    $new_status = null;
                }

                $systems[] = [
                    'label' => $name,
                    'value' => $gateway,
                    'icon' => 'yyy',
                    'description' => $description,
                    'new_status' => $new_status
                ];
            }

            closedir($handle);
        }

        return $systems;
    }




    /**
     * Display payment service details
     *
     * @OA\Get(
     *     path="/admin/{id}/payment-services",
     *     description="show payment service details",
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
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $paymentService = PaymentService::with('settings')->findOrFail($id);

            return response()->jsonApi([
                'title' => 'Payment service details',
                'message' => 'Payment service details',
                'data' => $paymentService
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Payment service Details',
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
     *                 property="gateway",
     *                 type="string",
     *                 description="The payment service gateway",
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="details of the payment service",
     *             ),
     *             @OA\Property(
     *                 property="new_status",
     *                 type="integer",
     *                 description="Currently in use settings",
     *             ),
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
     * Method to update payment System
     *
     * @OA\Put(
     *     path="/admin/{id}/payment-services",
     *     description="method to update payment service",
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
     *                 description="Name of the payment service",
     *             ),
     *             @OA\Property(
     *                 property="gateway",
     *                 type="string",
     *                 description="The payment service gateway",
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="details of the payment service",
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
     *                 description="payment service id",
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
                $resp['message'] = "Payment service was deleted";
                $resp['title'] = "Payment service";

                $resp['data'] = PaymentService::orderBy('name', 'Asc')
                    ->paginate($request->get('limit', 20));
                return response()->jsonApi($resp, 200);
            } else {
                $resp['message'] = "Unable to delete payment service";
                $resp['title'] = "Payment service";

                return response()->jsonApi($resp, 400);
            }
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Delete payment service',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
