<?php

namespace App\Services\Payments;

use App\Contracts\PaymentSystemContract;
use SafeCharge\Api\RestClient;
use SafeCharge\Tests\SimpleData;
use SafeCharge\Tests\TestCaseHelper;

class NumeiManager implements PaymentSystemContract{


    protected $config= [];
    protected $gateway;

    public function __construct()
    {
        $this->config = [
            'enviroment' =>  \SafeCharge\Api\Environment::INT,
            'merchantId'        => '<your merchantId>',
            'merchantSiteId'    => '<your merchantSiteId>',
            'merchantSecretKey' => '<your merchantSecretKey>',
        ];

        $this->gateway = new RestClient();
        $this->gateway->initialize($this->config);


    }

    public function openOrder($data = [])
    {
        $order = $gateway->getPaymentService()->openOrder([
            'userTokenId'       => $data['userTokenId'],
            'clientUniqueId'    => $data['clientUniqueId'],
            'clientRequestId'   => $data['clientRequestId'],
            'currency'          => $data['currency'],
            'amount'            => $data['amount'],
            'billingAddress' => [
                'country'   => $data['country'],
                "email"     => $data['email'],
            ],
        ]);

        return $order;
    }

    public function initPayment(Array $data){
        $response = $gateway->getPaymentService()->initPayment([
            'currency'       => $data['currency'],
            'amount'         => $data['amount'],
            'userTokenId'    => $data['userTokenId'],
            'clientUniqueId' => $data['clientUniqueId'],
            'clientRequestId'  => $data['clientRequestId'],
            'paymentOption'  => [
                'card' => [
                    'cardNumber'      => $data['cardNumber'],
                    'cardHolderName'  => $data['cardHolderName'],
                    'expirationMonth' => $data['expirationMonth'],
                    'expirationYear'  => $data['expirationYear'],
                    'CVV'             => $data['CVV'],
                    'threeD' =>[
                        'methodNotificationUrl'=> $data['methodNotificationUrl'],
                        ]
                ]
            ],
            'deviceDetails'     => [
                "ipAddress"  => $data['ipAddress'],
            ],
        ]);

    }

    public function payment(Array $data){
            $payment =  $safeCharge->getPaymentService()->createPayment([
                'currency'       => $data['currency'],
                'amount'         => $data['amount'],
                'userTokenId'    => $data['userTokenId'],
                'clientRequestId'=> $data['clientRequestId'],
                'clientUniqueId'=> $data['clientUniqueId'],
                'paymentOption'  => [
                    'card' => [
                        'cardNumber'      => $data['cardNumber'],
                        'cardHolderName'  => $data['cardHolderName'],
                        'expirationMonth' => $data['expirationMonth'],
                        'expirationYear'  => $data['expirationYear'],
                        'CVV'             => $data['CVV'],
                    ]
                ],
                'billingAddress' => [
                    'country'   => $data['country'],
                    "email"     => $data['email'],
                ],
                'deviceDetails'  => [
                    'ipAddress'  => $data['ipAddress'],
                ]
            ]);

            return $payment;

    }

    public function getPaymentStatus($paymentId){
        $paymentStatus = $gateway->getPaymentService()->getPaymentStatus([
            'paymentId' => $paymentId,
        ]);

        return $paymentStatus;
    }
}
