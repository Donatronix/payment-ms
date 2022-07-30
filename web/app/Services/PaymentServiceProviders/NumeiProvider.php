<?php

namespace App\Services\PaymentServiceProviders;

use App\Contracts\PaymentServiceContract;
use App\Models\PaymentOrder;
use Illuminate\Http\Request;
use SafeCharge\Api\RestClient;

class NumeiProvider implements PaymentServiceContract
{
    protected $config = [];

    protected $service;

    public function __construct()
    {
        $this->config = [
            'enviroment' => \SafeCharge\Api\Environment::INT,
            'merchantId' => '<your merchantId>',
            'merchantSiteId' => '<your merchantSiteId>',
            'merchantSecretKey' => '<your merchantSecretKey>',
        ];

        $this->service = new RestClient();
        $this->service->initialize($this->config);
    }

    public function openOrder($data = [])
    {
        $order = $this->service->getPaymentService()->openOrder([
            'userTokenId' => $data['userTokenId'],
            'clientUniqueId' => $data['clientUniqueId'],
            'clientRequestId' => $data['clientRequestId'],
            'currency' => $data['currency'],
            'amount' => $data['amount'],
            'billingAddress' => [
                'country' => $data['country'],
                "email" => $data['email'],
            ],
        ]);

        return $order;
    }

    public function initPayment(array $data)
    {
        $response = $this->service->getPaymentService()->initPayment([
            'currency' => $data['currency'],
            'amount' => $data['amount'],
            'userTokenId' => $data['userTokenId'],
            'clientUniqueId' => $data['clientUniqueId'],
            'clientRequestId' => $data['clientRequestId'],
            'paymentOption' => [
                'card' => [
                    'cardNumber' => $data['cardNumber'],
                    'cardHolderName' => $data['cardHolderName'],
                    'expirationMonth' => $data['expirationMonth'],
                    'expirationYear' => $data['expirationYear'],
                    'CVV' => $data['CVV'],
                    'threeD' => [
                        'methodNotificationUrl' => $data['methodNotificationUrl'],
                    ]
                ]
            ],
            'deviceDetails' => [
                "ipAddress" => $data['ipAddress'],
            ],
        ]);

    }

    public function payment(array $data)
    {
        $payment = $this->service->getPaymentService()->createPayment([
            'currency' => $data['currency'],
            'amount' => $data['amount'],
            'userTokenId' => $data['userTokenId'],
            'clientRequestId' => $data['clientRequestId'],
            'clientUniqueId' => $data['clientUniqueId'],
            'paymentOption' => [
                'card' => [
                    'cardNumber' => $data['cardNumber'],
                    'cardHolderName' => $data['cardHolderName'],
                    'expirationMonth' => $data['expirationMonth'],
                    'expirationYear' => $data['expirationYear'],
                    'CVV' => $data['CVV'],
                ]
            ],
            'billingAddress' => [
                'country' => $data['country'],
                "email" => $data['email'],
            ],
            'deviceDetails' => [
                'ipAddress' => $data['ipAddress'],
            ]
        ]);

        return $payment;

    }

    public function getPaymentStatus($paymentId)
    {
        $paymentStatus = $this->service->getPaymentService()->getPaymentStatus([
            'paymentId' => $paymentId,
        ]);

        return $paymentStatus;
    }

    public static function service()
    {
        // TODO: Implement service() method.
    }

    public static function name()
    {
        // TODO: Implement name() method.
    }

    public static function description()
    {
        // TODO: Implement description() method.
    }

    public static function getNewStatusId()
    {
        // TODO: Implement getNewStatusId() method.
    }

    public function charge(PaymentOrder $payment, object $inputData): mixed
    {
        // TODO: Implement charge() method.
    }

    public function createInvoice(PaymentOrder $payment, object $inputData): mixed
    {
        // TODO: Implement createInvoice() method.
    }

    public function handlerWebhook(Request $request): mixed
    {
        // TODO: Implement handlerWebhook() method.
    }
}
