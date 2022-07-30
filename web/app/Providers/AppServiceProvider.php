<?php

namespace App\Providers;

use App\Contracts\PaymentServiceContract;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function register()
    {
//        $this->app->bind('PaymentService', function(PaymentServiceContract $service){
//            try {
//                $class = '\App\Services\PaymentServiceProviders\\' . Str::ucfirst($service) . 'Provider';
//                $reflector = new \ReflectionClass($class);
//            } catch (\Exception $e) {
//                throw $e;
//            }
//
//            if (!$reflector->isInstantiable()) {
//                throw new \Exception("Payment service [$class] is not instantiable.");
//            }
//
//            if ($reflector->getProperty('service') === null) {
//                throw new \Exception("Can't init service [$service].");
//            }
//
//            return $reflector->newInstance();
//        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }
}
