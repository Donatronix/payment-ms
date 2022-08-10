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
         * Payment services list
         */
        $router->get('payment-services', 'PaymentServiceController');
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
            $router->post('charge', 'PaymentOrderController@charge');

            /**
             * Init payment and withdraw wallet balance
             */
            $router->post('withdraw', 'PaymentOrderController@withdraw');

            /**
             * Payment actions
             */
            $router->get('/{id:[a-fA-F0-9\-]{36}}', 'PaymentOrderController@show');
        });

        /**
         * Transactions
         */
        $router->group([
            'prefix' => 'transactions'
        ], function ($router) {
            $router->get('/', 'TransactionController@index');
            $router->post('/', 'TransactionController@store');
            $router->get('/{id}', 'TransactionController@show');
        });
    });

    /**
     * ADMIN PANEL ACCESS
     *
     * Admin | Super admin access level (E.g CEO company)
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

        /**
         * Payment services
         */
        $router->group([
            'prefix' => 'payment-services'
        ], function ($router) {
            $router->get('/', 'PaymentServiceController@index');
            $router->post('/', 'PaymentServiceController@store');
            $router->get('{id}', 'PaymentServiceController@show');
            $router->put('/{id}', 'PaymentServiceController@update');
            $router->delete('/{id}', 'PaymentServiceController@destroy');
        });

        /**
         * Transactions
         */
        $router->group([
            'prefix' => 'transactions'
        ], function ($router) {
            $router->get('/', 'TransactionController@index');
            $router->post('/', 'TransactionController@store');
            $router->get('/{id}', 'TransactionController@show');
        });
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
        $router->post('{gateway}', 'WebhookController@handlerWebhook');
    });
});
