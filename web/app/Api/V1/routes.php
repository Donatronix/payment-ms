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
        //stripe
        $router->get('/settings/stripe',          'StripePaymentGatewaySetupController@index');
        $router->get('/settings/{id}/stripe',     'StripePaymentGatewaySetupController@show');
        $router->post('/settings/stripe',         'StripePaymentGatewaySetupController@store');
        $router->put('/settings/{id}/stripe',     'StripePaymentGatewaySetupController@update');
        $router->delete('/settings/{id}/stripe',  'StripePaymentGatewaySetupController@destroy');
        //Paypal
        $router->get('/settings/paypal',          'PaypalPaymentGatewaySetupController@index');
        $router->get('/settings/{id}/paypal',     'PaypalPaymentGatewaySetupController@show');
        $router->post('/settings/paypal',         'PaypalPaymentGatewaySetupController@store');
        $router->put('/settings/{id}/paypal',     'PaypalPaymentGatewaySetupController@update');
        $router->delete('/settings/{id}/paypal',  'PaypalPaymentGatewaySetupController@destroy');
        //Openpayd
        $router->get('/settings/openpayd',          'OpenpaydPaymentGatewaySetupController@index');
        $router->get('/settings/{id}/openpayd',     'OpenpaydPaymentGatewaySetupController@show');
        $router->post('/settings/openpayd',         'OpenpaydPaymentGatewaySetupController@store');
        $router->put('/settings/{id}/openpayd',     'OpenpaydPaymentGatewaySetupController@update');
        $router->delete('/settings/{id}/openpayd',  'OpenpaydPaymentGatewaySetupController@destroy');
        //CoinBase
        $router->get('/settings/coinbase',          'CoinBasePaymentGatewaySetupController@index');
        $router->get('/settings/{id}/coinbase',     'CoinBasePaymentGatewaySetupController@show');
        $router->post('/settings/coinbase',         'CoinBasePaymentGatewaySetupController@store');
        $router->put('/settings/{id}/coinbase',     'CoinBasePaymentGatewaySetupController@update');
        $router->delete('/settings/{id}/coinbase',  'CoinBasePaymentGatewaySetupController@destroy');

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
