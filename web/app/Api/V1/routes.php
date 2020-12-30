<?php

/**
 * @var Laravel\Lumen\Routing\Router $router
 */
$router->group([
    'prefix' => 'payments',
    'namespace' => '\App\Api\V1\Controllers'
], function () use ($router) {
    /**
     * Internal access
     */
    $router->group([
        'middleware' => 'checkUser'
    ], function () use ($router) {
        $router->group(['prefix' => 'payments'], function () use ($router) {
            $router->get('systems', 'PaymentSystemController@index');
            $router->post('recharge', 'PaymentController@recharge');
        });

        /**
         * ADMIN PANEL
         */
        $router->group(
            [
                'prefix' => 'admin',
                'namespace' => 'Admin',
                'middleware' => 'checkAdmin'
            ],
            function ($router) {
                // Wallets Admin
                $router->get('/payment-orders', 'PaymentController@index');
                $router->post('/payment-orders/{id:[\d]+}', 'PaymentController@update');
            }
        );
    });

    /**
     * BitPay webhooks
     */
    $router->group(['prefix' => 'webhooks'], function() use ($router) {
        $router->post('{gateway}/invoices', 'PaymentController@handlerWebhookInvoice');
    });

    /**
     * Show version of service
     */
    $router->get('version', 'VersionController@version');
});
