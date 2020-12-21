<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;

/**
 * Class VersionController
 *
 * @package App\Api\V1\Controllers
 */
class VersionController extends Controller
{
    /**
     * Show version of service
     *
     * @OA\Get(
     *     path="/v1/infinity-wallet/version",
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
        return response()->json(['date' => '2020-12-07'], 200);
    }
}
