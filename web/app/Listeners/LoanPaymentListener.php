<?php

namespace App\Listeners;

use App\Models\LogRequest;
use App\Models\LogError;
use App\Models\PaymentOrder;
use App\Services\PaymentServiceManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Class LoanPaymentListener
 * @package App\Listeners
 */
class LoanPaymentListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */

    private const RECEIVER_LISTENER = 'LoanPayment';

    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param array $data
     *
     * @return void
     */
    public function handle(array $data)
    {
        $validation = Validator::make($data, [
            'gateway' => 'string|required',
            'amount' => 'integer|required',
            'currency' => 'string|required',
            'service' => 'string|required',
            'replay_to' => 'string|required',
            'user_id' => 'string|required',
            'loan_id' => 'string|required',
            'payment_order_id' => 'string|required',
            'total_load' => 'integer|required',
            'token' => 'string',
        ]);

        if ($validation->fails()) {
            Log::info($validation->errors());
            exit;
        }

        $inputData = (object)$data;

        // Write log
        try {
            LogRequest::create([
                'source' => 'listener',
                'service' => $inputData->gateway,
                'payload' => $inputData
            ]);
        } catch (\Exception $e) {
            Log::info('Log of invoice failed: ' . $e->getMessage());
        }

        // Init manager
        try {
            $system = PaymentServiceManager::getInstance($inputData->gateway);
        } catch (\Exception $e) {
            Log::info($e->getMessage());

            exit;
        }

        // Create internal order
        $payment = PaymentOrder::create([
            'type' => PaymentOrder::TYPE_PAYIN,
            'gateway' => $inputData->gateway,
            'amount' => $inputData->amount,
            'currency' => mb_strtoupper($inputData->currency),
            'service' => $inputData->service,
            'user_id' => $inputData->user_id,
        ]);

        // Create invoice
        $result = $system->charge($payment, $inputData);

        // Return response
        $code = 200;
        if ($result['type'] === 'danger') {
            $code = 400;

            LogError::create([
                'source' => 'listener',
                'gateway' => $inputData->gateway,
                'message' => $result['message'],
                'payload' => $result
            ]);

            Log::info($result);
            exit;
        } else {
            // Return result
            \PubSub::publish(self::RECEIVER_LISTENER, [
                'type' => 'success',
                'title' => "Payment sent",
                'data' => [
                    "reference_id" => $result["data"]["payment_order_id"],
                    "loan_id" => $inputData->loan_id,
                    "payment_order_id" => $inputData->payment_order_id,
                    'amount_paid' => $inputData->amount,
                    'total_load' => $inputData->total_load,
                    'token' => $inputData->token,
                    'user_id' => $inputData->user_id,
                ]
            ], $inputData->replay_to);
            exit;
        }
    }
}
