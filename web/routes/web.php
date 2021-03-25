<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

Route::get('/', function () use ($router) {
    return $router->app->version();
});

Route::group(
    ['prefix' => env('API_PREFIX') . '/v1'],
    function ($router) {
        include base_path('app/Api/V1/routes.php');
    }
);

if (file_exists(__DIR__ . '/tests.php'))
    require_once(__DIR__ . '/tests.php');
