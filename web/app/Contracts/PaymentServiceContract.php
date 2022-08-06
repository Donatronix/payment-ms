<?php

namespace App\Contracts;

use App\Models\PaymentOrder;
use Illuminate\Http\Request;

interface PaymentServiceContract
{
    /**
     * @return string
     */
    public static function key(): string;

    /**
     * @return string
     */
    public static function title(): string;

    /**
     * @return string
     */
    public static function description(): string;

    /**
     * Make one-time charge money to system
     *
     * @param PaymentOrder $order
     * @param object $inputData
     * @return mixed
     */
    public function charge(PaymentOrder $order, object $inputData): mixed;

    /**
     * @param object $payload
     * @return mixed
     */
    public function checkTransaction(object $payload): mixed;

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handlerWebhook(Request $request): mixed;
}
