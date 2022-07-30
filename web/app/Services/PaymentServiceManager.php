<?php

namespace App\Services;

use Illuminate\Support\Str;

class PaymentServiceManager
{
    /**
     * @param $service
     *
     * @return object
     * @throws \ReflectionException
     * @throws \Exception
     */
    public static function getInstance($service): object
    {
        try {
            $class = '\App\Services\PaymentServiceProviders\\' . Str::ucfirst($service) . 'Provider';
            $reflector = new \ReflectionClass($class);
        } catch (\Exception $e) {
            throw $e;
        }

        if (!$reflector->isInstantiable()) {
            throw new \Exception("Payment service [$class] is not instantiable.");
        }

        if ($reflector->getProperty('service') === null) {
            throw new \Exception("Can't init service [$service].");
        }

        return $reflector->newInstance();
    }
}
