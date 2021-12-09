<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface PaymentSystemContract
{
    /**
     * @return mixed
     */
    public static function gateway();

    /**
     * @return mixed
     */
    public static function name();

    /**
     * @return mixed
     */
    public static function description();

    /**
     * @return integer
     */
    public static function getNewStatusId();

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
