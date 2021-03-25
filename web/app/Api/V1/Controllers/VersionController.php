<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;

/**
 * Class VersionController
 * Show version of service
 *
 * @package App\Api\V1\Controllers
 */
class VersionController extends Controller
{
    /**
     * Show version of service
     *
     * @OA\Get(
     *     path="/v1/payments/version",
     *     summary="Show version of service",
     *     description="Show version of service",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     )
     * )
     * @return \Sumra\JsonApi\
     */
    public function version()
    {
        return response()->json(['date' => '2021-03-18'], 200);
    }
}
