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

        //Manage all payment system
        $router->get('/admin/payment-system',          'PaymentSystemController@index');
        $router->get('/admin/{id}/payment-system',     'PaymentSystemController@show');
        $router->post('/admin/payment-system',         'PaymentSystemController@store');
        $router->put('/admin/{id}/payment-system',     'PaymentSystemController@update');
        $router->delete('/admin/{id}/payment-system',  'PaymentSystemController@destroy');
        //Manage all payment setting
        $router->get('/admin/payment-setting',          'PaymentSettingController@index');
        $router->post('/admin/payment-setting',         'PaymentSettingController@index');

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
