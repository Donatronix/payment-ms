<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;



/**
 * Class PaymentController
 *
 * @package App\Api\V1\Controllers
 */
class PaymentSystemController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/payments/systems",
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
        $systems = $this->catalog();
        return response()->json(['success' => true, 'systems' => $systems], 200);
    }


    public function catalog()
        {

            $systems = $this->catalog_cache();

            if( count($systems) == 0 )
            {
                $systems = $this->catalog_fresh();
                $this->save_cache();
            }

            return $systems;
    }

    public function clear_cache()
    {

        // Stub

    }


    private function catalog_cache()
    {

        // Stub
        return [];

    }


    private function catalog_fresh()
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
                    $new_status = $class::getNewStatusId() ;
                } catch (\Exception $e) {
                    $gateway = 'error';
                    $name = 'error';
                    $description = $entry . ' ' . $e->getMessage();
                    $new_status = null;
                }

                $systems[] = ['gateway' => $gateway, 'name' => $name, 'description' => $description, 'new_status'=>$new_status];
            }

            closedir($handle);
        }

        return $systems;
    }

    private function save_cache()
    {

        // Stub

    }



}
