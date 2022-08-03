<?php

namespace App\Services;

use App\Models\PaymentService;
use App\Models\Setting;
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
        $object = PaymentService::query()
            ->with('settings')
            ->where('key', $service)
            ->first();

        try {
            // For blockchain networks transform to
            $service = Str::of($service)->whenStartsWith('network', function ($str) {
                return $str->replace('-', ' ')->camel()->replace('Bnb', 'BNB');
            })->ucfirst();

            // Get Provider class
            $class = (string) $service->append('Provider')
                ->prepend('\App\Services\PaymentServiceProviders\\');
            $reflector = new \ReflectionClass($class);
        } catch (\Exception $e) {
            throw $e;
        }

        if (!$reflector->isInstantiable()) {
            throw new \Exception("Payment service [$service] is not instantiable.");
        }

        if ($reflector->getProperty('service') === null) {
            throw new \Exception("Can't init service [$service].");
        }

        // Get providers settings
        $settings = self::get($object->settings, $service);
        $settings->is_develop = $object->is_develop;

        return $reflector->newInstance($settings);
    }

    // Get payment service settings
    public static function get($settings, $service = null): mixed
    {
        $list = [];
        foreach ($settings as $row) {
            $key = Str::replaceFirst("{$service}_", '', $row->key);

            $list[$key] = $row->value ?? null;
        }

        return (object) $list;
    }
}
