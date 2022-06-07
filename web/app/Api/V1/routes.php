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
        /**
         * Payment actions
         */
        $router->get('payments/{id:[\d]+}', 'PaymentController@show');
        $router->post('payments/charge', 'PaymentController@charge');

        /**
         * Payment systems list
         */
        $router->get('payment-systems', 'PaymentSystemController');
    });

    /**
     * ADMIN PANEL
     */
    $router->group([
        'prefix' => 'admin',
        'namespace' => 'Admin',
        'middleware' => [
            'checkUser',
            'checkAdmin'
        ]
    ], function ($router) {
        $router->get('/payments', 'PaymentController@index');
        $router->get('/payments/lost', 'PaymentController@lost');
        $router->post('/payments/{id:[\d]+}', 'PaymentController@update');

        //Manage all payment gateway settings
        $router->get('/gateway/settings',          'ManageAllPaymentGatewayController@index');
        $router->get('/gateway/{id}/settings',     'ManageAllPaymentGatewayController@show');
        $router->post('/gateway/settings',         'ManageAllPaymentGatewayController@store');
        $router->put('/gateway/{id}/settings',     'ManageAllPaymentGatewayController@update');
        $router->delete('/gateway/{id}/settings',  'ManageAllPaymentGatewayController@destroy');

    });

    /**
     * Payments webhooks
     */
    $router->group([
        'prefix' => 'webhooks'
    ], function ($router) {
        $router->post('{gateway}/invoices', 'WebhookController@handlerWebhook');
    });
});
