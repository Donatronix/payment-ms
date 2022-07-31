<?php

namespace App\Listeners;

use App\Models\LogRequest;
use App\Models\LogError;
use App\Services\PaymentServiceManager;
use App\Models\PaymentOrder as PayModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * Class RechargeBalanceRequestListener
 *
 * @package App\Listeners
 */
class RechargeBalanceRequestListener
{
    /**
     * @var string
     */
    private const RECEIVER_LISTENER = 'rechargeBalanceResponse';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param array $inputData
     *
     * @return void
     */
    public function handle(array $inputData)
    {
        $validation = Validator::make($inputData, [
            'gateway' => 'string|required',
            'amount' => 'integer|required',
            'currency' => 'string|required',
            'replay_to' => 'string|required',
            'order_id' => 'string|required',
            'user_id' => 'string|required',
        ]);

        if ($validation->fails()) {
            \PubSub::publish(self::RECEIVER_LISTENER, [
                'status' => 'error',
                'order_id' => $inputData['order_id'],
                'message' => $validation->errors()
            ], $inputData['replay_to']);

            exit();
        }

        // Payment Log
        try {
            LogRequest::create([
                'source' => 'listener',
                'gateway' => $inputData['gateway'],
                'service' => $inputData['replay_to'],
                'payload' => $inputData
            ]);
        }
        catch (\Exception $e) {
            Log::info('Log of invoice failed: ' . $e->getMessage());
        }

        // Init manager
        try {
            $payment = PayModel::create([
                'type' => PayModel::TYPE_PAYIN,
                'amount' => $inputData['amount'],
                'gateway' => $inputData['gateway'],
                'user_id' => $inputData['user_id'],
                'service' => $inputData['replay_to'],
                'currency' => $inputData['currency'],
                'payload' => $inputData
            ]);

            $paymentGateway = PaymentServiceManager::getInstance($inputData['gateway']);
        }
        catch(\Exception $e) {
            \PubSub::publish(self::RECEIVER_LISTENER, [
                'status' => 'error',
                'order_id' => $inputData['order_id'],
                'message' => $e->getMessage(),
            ], $inputData['replay_to']);

            exit();
        }

        // Create invoice
        $result = $paymentGateway->charge($payment, (object) $inputData);

        // Return response
        if ($result['type'] === 'error') {
            LogError::create([
                'source' => 'listener',
                'gateway' => $inputData['gateway'],
                'message' => $result['message'],
                'payload' => $result
            ]);
        }

        // Send payment request to payment gateway
        \PubSub::publish(self::RECEIVER_LISTENER, array_merge($result, [
            'order_id' => $inputData['order_id'],
        ]), $inputData['replay_to']);
    }
}
