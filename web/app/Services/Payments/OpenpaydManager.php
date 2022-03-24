<?php

namespace App\Services\Payments;

use App\Contracts\PaymentSystemContract;
use App\Models\Payment;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;



class OpenpaydManager implements PaymentSystemContract
{   

    // https://apidocs.openpayd.com/docs/transaction-status-updated-webhook#transaction-types

    const TRANSACTION_TYPE_PAYIN = "PAYIN";
    const TRANSACTION_TYPE_PAYOUT = "PAYOUT";
    const TRANSACTION_TYPE_FEE = "FEE";
    const TRANSACTION_TYPE_TRANSFER = "TRANSFER";
    const TRANSACTION_TYPE_EXCHANGE = "EXCHANGE";
    const TRANSACTION_TYPE_RETURN_IN = "RETURN_IN";
    const TRANSACTION_TYPE_RETURN_OUT = "RETURN_OUT";


    const TRANSACTION_STATUS_PROCESSING = "PROCESSING";
    const TRANSACTION_STATUS_RELEASED = "RELEASED";
    const TRANSACTION_STATUS_COMPLETED = "COMPLETED";
    const TRANSACTION_STATUS_FAILED = "FAILED";
    const TRANSACTION_STATUS_CANCELLED = "CANCELLED";


    /**
     * @var  \GuzzleHttp\Client
     */
    private $openPaydClient;

    // Transaction statuses



    public function __construct()
    {
   
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
        // TODO not yet provided by openpayd
    }

    /**
     * @param Payment $payment
     * @param object $inputData
     *
     * @return mixed
     */
    public function createInvoice(Payment $payment, object $inputData): mixed
    {

        // TODO not yet provided by openpayd
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handlerWebhook(Request $request): array
    {
       
        $signature = $request->header("signature");
        $payload = $request->get("payload",null);

        if(!$this->isValidSignature($signature,$payload))
        {
             return [
                 "type" => "danger",
                 "message" => "Openpayd: Invalid signature"
             ];
        }
        
        $webhookPayload = json_decode($payload);
        
        $transactionStatus = strtoupper($webhookPayload["status"]);
        $transactionType = strtoupper($webhookPayload["type"]);

        if($transactionType == self::TRANSACTION_TYPE_PAYIN){
                
            //  retrieve payment and update status
            // TODO find a way to access webhook metadata. 
            $payment = Payment::where('type', Payment::TYPE_INVOICE)
            ->where('id', $webhookPayload["metadata"]['orderId'])
            ->where('document_id', $webhookPayload["metadata"]['documentId'])
            ->where('check_code', $webhookPayload["metadata"]['check_code'])
            ->where('gateway', self::gateway())
            ->first();

            if (!$payment) {
                return [
                    'type' => 'danger',
                    'message' => 'Payment transaction not found in Payment Microservice database'
                ];
            }
    
            $payment->status = $transactionStatus;
    
           // $payment->payload = $paymentData;
            $payment->save();
    
            // Return result
            return [
                'status' => 'success',
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'service' => $payment->service,
                'user_id' => $payment->user_id,
                'payment_completed' => (self::TRANSACTION_STATUS_COMPLETED === $payment->status),
            ];


        }else {

            //  we are not yet interested in other account webhooks not PAYIN

            return [
                'type' => 'danger',
                'message' => 'OpenPayd: Not a PAYIN webhook'
            ];
        }

    }

    private function isValidSignature($signature,$data): bool
    {
         $pubKeyPath = config("payments.openpayd.public_key_path");
         
         if ($signature == hash_hmac_file('sha256', $data, $pubKeyPath)){
              
            return true;
         }

         return false;
    }

}
