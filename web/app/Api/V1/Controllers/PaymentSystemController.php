<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Class PaymentSystemController
 *
 * @package App\Api\V1\Controllers
 */
class PaymentSystemController extends Controller
{
    const CACHE_ID = "PAYSYSTEMLIST";

    /**
     * @OA\Get(
     *     path="/payment-systems",
     *     description="List of payment systems",
     *     tags={"Payment Systems"},
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
    public function __invoke(): JsonResponse
    {
        $systems = Cache::get(self::CACHE_ID, []);

        if (!is_array($systems) || count($systems) == 0) {

            $systems = $this->catalog();

            Cache::put(self::CACHE_ID, $systems);
        }

        return response()->json([
            'success' => true,
            'systems' => $systems
        ], 200);
    }

    public function clear_cache()
    {
        Cache::forget(self::CACHE_ID);
    }

    public function catalog(): array
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
                    $gateway = $class::gateway();
                    $name = $class::name();
                    $description = $class::description();
                    $new_status = $class::getNewStatusId();
                } catch (\Exception $e) {
                    $gateway = 'error';
                    $name = 'error';
                    $description = $entry . ' ' . $e->getMessage();
                    $new_status = null;
                }

                $systems[] = [
                    'gateway' => $gateway,
                    'name' => $name,
                    'description' => $description,
                    'new_status' => $new_status
                ];
            }

            closedir($handle);
        }

        return $systems;
    }
}
