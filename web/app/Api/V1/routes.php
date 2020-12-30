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

            $router->get('systems', 'PaymentSystemsController@index');
            $router->post('recharge', 'PaymentController@recharge');

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
                $router->get('/payment-orders', 'PaymentOrderController@index');
                $router->post('/payment-orders/{id:[\d]+}', 'PaymentOrderController@update');

//                $router->get('/payment-orders/{id:[\d]+}', 'PaymentOrderController@show');
//                $router->post('/payment-orders/{id:[\d]+}/update-status', 'PaymentOrderController@updateStatus');
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
