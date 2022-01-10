<?php

/**
 * @var Laravel\Lumen\Routing\Router $router
 */
$router->group([
    'prefix' => env('APP_API_VERSION', ''),
    'namespace' => '\App\Api\V1\Controllers'
], function ($router) {
    /**
     * Internal access
     */
    $router->group([
        'middleware' => 'checkUser'
    ], function ($router) {
        $router->get('systems', 'PaymentSystemController@index');
        $router->post('recharge', 'PaymentController@recharge');
        $router->get('{id:[\d]+}', 'PaymentController@get');


        $router->group(['prefix' => 'currencies'], function ($router) {
            $router->get('/', 'CurrenciesController@index');
            $router->post('/{id:[\d]+}/update-status', 'CurrenciesController@updateStatus');
        });

        $router->get('/currencies', 'CurrenciesController@reference');


        /**
         * ADMIN PANEL
         */
        $router->group([
            'prefix' => 'admin',
            'namespace' => 'Admin',
            'middleware' => 'checkAdmin'
        ], function ($router) {
            // Wallets Admin
            $router->get('/payments', 'PaymentController@index');
            $router->get('/payments/lost', 'PaymentController@lost');
            $router->post('/payments/{id:[\d]+}', 'PaymentController@update');

            $router->group(['prefix' => 'currencies'], function ($router) {
                $router->post('/', 'CurrenciesController@store');
            });
        });
    });

    /**
     * BitPay webhooks
     */
    $router->group([
        'prefix' => 'webhooks'
    ], function () use ($router) {
        $router->post('{gateway}/invoices', 'WebhookController@handlerWebhookInvoice');
    });
});
