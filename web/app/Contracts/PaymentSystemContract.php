<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface PaymentSystemContract
{
    /**
     * @return mixed
     */
    public static function type();

    /**
     * @return mixed
     */
    public static function name();

    /**
     * @return mixed
     */
    public static function description();

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function createInvoice(array $data);

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handlerWebhookInvoice(Request $request);
}
