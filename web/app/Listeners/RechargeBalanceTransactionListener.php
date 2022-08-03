<?php

namespace App\Listeners;

use App\Models\PaymentOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Class RechargeBalanceTransactionListener
 *
 * @package App\Listeners
 */
class RechargeBalanceTransactionListener
{
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
        // Validate input
        $validation = Validator::make($inputData, [
            'payment_order_id' => 'integer|required',
        ]);

        if ($validation->fails()) {
            Log::info($validation->errors());

            exit;
        }

        // Get or check order
        try{
            $payment = PaymentOrder::findOrFail($inputData['payment_order_id']);
        }catch(\Exception $e){
            Log::info('Recharge balance transaction listener error: ' . $e->getMessage());

            exit;
        }

        // Update payment/ Set flag, transaction is created
       // $payment->status = true;
        $payment->save();
    }
}
