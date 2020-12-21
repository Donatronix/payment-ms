<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LogInvoice;
use App\Models\LogInvoiceError;
use App\Models\LogWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Class PaymentController
 *
 * @package App\Api\V1\Controllers
 */
class PaymentController extends Controller
{
    /**
     * Charge wallet balance
     *
     * @OA\Post(
     *     path="/v1/payments/payments/charge",
     *     description="Charge wallet balance",
     *     tags={"Payments"},
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
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="gateway",
     *                 description="Payment gateway",
     *                 type="string",
     *                 default="bitpay"
     *             ),
     *             @OA\Property(
     *                 property="amount",
     *                 description="The amount of money replenished to the balance",
     *                 type="integer",
     *                 default=1000
     *             ),
     *             @OA\Property(
     *                 property="currency",
     *                 description="Currency of balance",
     *                 type="string",
     *                 default="GBP"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \ReflectionException
     */
    public function charge(Request $request)
    {
        // Validate input
        $input = $this->validate($request, [
            'gateway' => 'string|required',
            'amount' => 'integer|required',
            'currency' => 'string|required'
        ]);

        try {
            $log = new LogInvoice;
            $log->gateway = $request->get('gateway');
            $log->request = var_export($request, true);
            $log->save();
        } catch (\Exception $e) {
            \Log::info('Log of invoice failed');
        }

        // Init manager
        $system = $this->getServiceManager($request->get('gateway'));

        if ($system === null)
            return response()->json([
                'success' => false,
                'message' => 'No class for ' . $request->get('gateway'),
            ], 400);

        // Create invoice
        $result = $system->createInvoice($input);

        // Return response
        $code = 200;
        if ($result['type'] === 'error') {
            $code = 400;

            $log = new LogInvoiceError;
            $log->error = var_export($result, true);
            $log->save();
        }

        // Return result
        return response()->json($result, $code);
    }

    /**
     * Invoices webhook
     *
     * @OA\Post(
     *     path="/v1/payments/webhooks/{gateway}/invoices",
     *     description="Webhooks Notifications about invoices",
     *     tags={"Payments Webhooks"},
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
     *
     *     @OA\Parameter(
     *         name="gateway",
     *         description="Payment gateway",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *              default="bitpay"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @param                          $gateway
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function handlerWebhookInvoice(Request $request, string $gateway)
    {
        try {
            $log = new LogWebhook;
            $log->gateway = $gateway;
            $log->request = var_export($request, true);
            $log->save();
        } catch (\Exception $e) {
            \Log::info('Log of invoice failed');
        }

        $system = $this->getServiceManager($gateway);

        return $system->handlerWebhookInvoice($request);
    }

    /**
     * @param $gateway
     *
     * @return mixed
     * @throws \ReflectionException
     */
    private function getServiceManager($gateway)
    {
        $class = '\App\Services\Payments\\' . Str::ucfirst($gateway) . 'Manager';
        $reflector = new \ReflectionClass($class);

        if (!$reflector->isInstantiable()) {
            throw new \Exception("Payment gateway [$class] is not instantiable.");
        }

        if($reflector->getProperty('gateway') === null){
            throw new \Exception("Can't init gateway [$gateway].");
        }

        return $reflector->newInstance();
    }
}
