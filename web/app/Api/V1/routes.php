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
        /**
         * User balances
         */
        $router->group(['prefix' => 'balances'], function () use ($router) {
            $router->get('/{user_id}', 'UserBalanceController@index');
            $router->get('/{user_id}/{currency_id}', 'UserBalanceController@show');
            $router->get('/{user_id}/{currency_id}/compare', 'UserBalanceController@compare');
        });

        $router->group(['prefix' => 'payments'], function () use ($router) {
            $router->get('systems', 'PaymentSystemsController@index');
            $router->post('charge', 'PaymentController@charge');
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
