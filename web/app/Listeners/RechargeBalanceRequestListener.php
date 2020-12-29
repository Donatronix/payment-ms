<?php

namespace App\Listeners;

use App\Models\LogPaymentRequest;
use App\Models\LogPaymentRequestError;
use App\Services\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
    private const RECEIVER_LISTENER = 'rechargeBalance';

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(array $inputData): JsonResponse
    {
        // Validate input
        $validation = Validator::make($inputData, [
            'gateway' => 'string|required',
            'amount' => 'integer|required',
            'currency.id' => 'integer|required',
            'currency.code' => 'string|required',
            'replay_to' => 'string|required',
            'order_id' => 'integer|required',
            'user_id' => 'integer|required',
        ]);

        if ($validation->fails()) {
            \PubSub::transaction(function () {})->publish(self::RECEIVER_LISTENER, [
                'status' => 'error',
                'order_id' => $inputData['order_id'],
                'message' => $validation->errors()
            ], $inputData['replay_to']);

            exit;
        }

        // Write log
        try {
            LogPaymentRequest::create([
                'gateway' => $inputData['gateway'],
                'service' => $inputData['replay_to'],
                'payload' => $inputData
            ]);
        } catch (\Exception $e) {
            Log::info('Log of invoice failed: ' . $e->getMessage());
        }

        // Init manager
        try{
            $system = Payment::getServiceManager($inputData['gateway']);
        } catch(\Exception $e){
            \PubSub::transaction(function () {})->publish(self::RECEIVER_LISTENER, [
                'status' => 'error',
                'order_id' => $inputData['order_id'],
                'message' => $e->getMessage(),
            ], $inputData['replay_to']);

            exit;
        }

        // Create invoice
        $result = $system->createInvoice($inputData);

        // Return response
        if ($result['status'] === 'error') {
            LogPaymentRequestError::create([
                'error' => $result['message']
            ]);
        }

        // Send payment request to payment gateway
        \PubSub::transaction(function () {})->publish(self::RECEIVER_LISTENER, array_merge($result, [
            'order_id' => $inputData['order_id'],
        ]), $inputData['replay_to']);
    }
}
