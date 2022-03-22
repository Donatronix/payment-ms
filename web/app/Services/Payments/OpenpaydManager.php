<?php

namespace App\Services\Payments;

use App\Contracts\PaymentSystemContract;
use App\Models\Payment;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class OpenpaydManager implements PaymentSystemContract
{
    /**
     * @var  \GuzzleHttp\Client
     */
    private $openPaydClient;

    public function __construct()
    {
        try {
            //code...
            $this->openPaydClient = new Client(['base_uri' => config("payments.openpayd.base_url")]);

        } catch (Exception $e) {

            throw new Exception($e->getMessage());
        }
    }

    public function getAccessToken()
    {

        try {
            $payload = [
                "form_params" => [
                    "username" => config("payments.openpayd.username"),
                    "password" => config("payments.openpayd.password"),
                ],
            ];
            $response = $this->openPaydClient->post("/oauth/token?grant_type=client_credentials", $payload);

            return $response;

        } catch (Exception $e) {
            
            throw new Exception($e->getMessage());

        }

    }

    public static function name(): string
    {
        return 'OpenPayd';
    }

    public static function description(): string
    {
        return 'OpenPayd is..';
    }

    public static function gateway(): string
    {
        return 'openpayd';
    }

    /**
     * @return integer
     */
    public static function getNewStatusId()
    {

    }

    /**
     * Make one-time charge money to system
     *
     * @param Payment $payment
     * @param object $inputData
     * @return mixed
     */
    public function charge(Payment $payment, object $inputData): mixed
    {

    }

    /**
     * @param Payment $payment
     * @param object $inputData
     *
     * @return mixed
     */
    public function createInvoice(Payment $payment, object $inputData): mixed
    {

    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handlerWebhook(Request $request): mixed
    {

    }

}
