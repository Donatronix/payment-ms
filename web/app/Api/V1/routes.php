<?php

/**
 * @var Laravel\Lumen\Routing\Router $router
 */
$router->group([
    'prefix' => env('APP_API_VERSION', ''),
    'namespace' => '\App\Api\V1\Controllers'
], function ($router) {
    /**
     * PUBLIC ACCESS
     *
     * level with free access to the endpoint
     */
    $router->group([
        'namespace' => 'Public'
    ], function ($router) {
        /**
         * Payment systems list
         */
        $router->get('payment-systems', 'PaymentSystemController');
    });

    /**
     * USER APPLICATION PRIVATE ACCESS
     *
     * Application level for users
     */
    $router->group([
        'prefix' => 'app',
        'namespace' => 'Application',
        'middleware' => 'checkUser'
    ], function ($router) {
        /**
         * Payment Orders
         */
        $router->group([
            'prefix' => 'orders',
        ], function ($router) {
            /**
             * Init payment and charge wallet balance or invoice
             */
            $router->post('charge', 'ChargeController');

            /**
             * Init payment and withdraw wallet balance
             */
            $router->post('withdraw', 'WithdrawController');

            /**
             * Payment actions
             */
            $router->get('{id:[\d]+}', 'PaymentOrderController@show');
        });
    });

    /**
     * ADMIN PANEL ACCESS
     *
     * Admin / super admin access level (E.g CEO company)
     */
    $router->group([
        'prefix' => 'admin',
        'namespace' => 'Admin',
        'middleware' => [
            'checkUser',
            'checkAdmin'
        ]
    ], function ($router) {
        /**
         * Payment Orders
         */
        $router->group([
            'prefix' => 'orders',
        ], function ($router) {
            $router->get('/', 'PaymentOrderController@index');
            $router->get('/lost', 'PaymentOrderController@lost');
            $router->post('/{id:[\d]+}', 'PaymentOrderController@update');
        });

        //Manage all payment system
        $router->get('/payment-system',          'PaymentSystemController@index');
        $router->get('/payment-system/{id}',     'PaymentSystemController@show');
        $router->post('/payment-system',         'PaymentSystemController@store');
        $router->put('/payment-system/{id}',     'PaymentSystemController@update');
        $router->delete('/payment-system/{id}',  'PaymentSystemController@destroy');

        //Manage all payment setting
        $router->get('/payment-setting',          'PaymentSettingController@index');
        $router->post('/payment-setting',         'PaymentSettingController@index');
    });

    /**
     * WEBHOOKS
     *
     * Access level of external / internal software services
     */
    $router->group([
        'prefix' => 'webhooks',
        'namespace' => 'Webhooks'
    ], function ($router) {
        $router->post('{gateway}/invoices', 'WebhookController@handlerWebhook');
    });
});
