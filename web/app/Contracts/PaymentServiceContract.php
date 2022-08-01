<?php

namespace App\Contracts;

use App\Models\PaymentOrder;
use Illuminate\Http\Request;

interface PaymentServiceContract
{
    /**
     * @return string
     */
    public static function service(): string;

    /**
     * @return string
     */
    public static function name(): string;

    /**
     * @return string
     */
    public static function description(): string;

    /**
     * @return integer
     */
    public static function newStatus(): int;

    /**
     * Make one-time charge money to system
     *
     * @param PaymentOrder $payment
     * @param object $inputData
     * @return mixed
     */
    public function charge(PaymentOrder $payment, object $inputData): mixed;

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handlerWebhook(Request $request): mixed;
}