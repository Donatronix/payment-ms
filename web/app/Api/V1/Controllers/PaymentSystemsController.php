<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Class PaymentController
 *
 * @package App\Api\V1\Controllers
 */
class PaymentSystemsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/payments/payments/systems",
     *     description="List of payment systems",
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
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $systems = [];

        $dir = base_path('app/Services/Payments');

        if ($handle = opendir($dir)) {
            /* Именно такой способ чтения элементов каталога является правильным. */
            while (false !== ($entry = readdir($handle))) {
                if (($entry == '.') || ($entry == '..'))
                    continue;

                $class = '\App\Services\Payments\\' . preg_replace('/\.php/', '', $entry);

                if (!class_exists($class))
                    continue;

                try {
                    $type = $class::type();
                    $name = $class::name();
                    $description = $class::description();
                } catch (\Exception $e) {
                    $name = 'error';
                    $description = $entry . ' ' . $e->getMessage();
                }

                $systems[] = ['type' => $type, 'name' => $name, 'description' => $description];
            }

            closedir($handle);
        }

        return response()->json(['success' => true, 'systems' => $systems], 200);
    }
}
