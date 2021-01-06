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
        $router->get('systems', 'PaymentSystemController@index');
        $router->post('recharge', 'PaymentController@recharge');
        $router->get('{id:[\d]+}', 'PaymentController@get');

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
                $router->get('/payments', 'PaymentController@index');
                $router->post('/payments/{id:[\d]+}', 'PaymentController@update');
                $router->get('/paymentslost', 'PaymentLostController@index');
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
