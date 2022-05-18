<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;
use App\Listeners\RechargeBalanceTransactionListener;
use App\Listeners\LoanPaymentListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'rechargeBalanceRequest' => [
            'App\Listeners\RechargeBalanceRequestListener',
        ],
        'rechargeBalanceTransaction' => [
            RechargeBalanceTransactionListener::class,
        ],
        'LoanPayment' => [
            'App\Listener\LoanPaymentListener',
        ],
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
