<?php

namespace App\Listeners;

use App\Models\Payment as PaymentModel;
use App\Services\Payment as PaymentService;
use App\Models\LogPaymentRequest;
use App\Models\LogPaymentRequestError;
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

        $request = new \Illuminate\Http\Request($data);

        $validation = Validator::make($request->all(), [
            'gateway' => 'string|required',
            'amount' => 'integer|required',
            'currency' => 'string|required',
            'service' => 'string|required',
            'replay_to' => 'string|required',
            'user_id' => 'string|required',
            'loan_id' => 'string|required',
            'payment_id' => 'string|required',
            'total_load' => 'integer|required',
            'token' => 'string',
        ]);

        if ($validation->fails()) {
            Log::info($validation->errors());
            exit;
        }

        $inputData = (object)$request->all();

        // Write log
        try {
            LogPaymentRequest::create([
                'gateway' => $inputData->gateway,
                'service' => $inputData->service,
                'payload' => $inputData
            ]);
        } catch (\Exception $e) {
            Log::info('Log of invoice failed: ' . $e->getMessage());
        }

        // Init manager
        try {
            $system = PaymentService::getInstance($inputData->gateway);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            exit;
        }

        // Create internal order
        $payment = PaymentModel::create([
            'type' => PaymentModel::TYPE_PAYIN,
            'gateway' => $request->get('gateway'),
            'amount' => $request->get('amount'),
            'currency' => mb_strtoupper($request->get('currency')),
            'service' => $request->get('service'),
            'user_id' => $request->get('user_id'),
        ]);

        // Create invoice
        $result = $system->charge($payment, $inputData);

        // Return response
        $code = 200;
        if ($result['type'] === 'danger') {
            $code = 400;

            LogPaymentRequestError::create([
                'gateway' => $inputData->gateway,
                'payload' => $result['message']
            ]);

            Log::info($result);
            exit;
        } else {
            // Return result

            \PubSub::transaction(function () {
            })->publish(self::RECEIVER_LISTENER, [
                'type' => 'success',
                'title' => "Payment sent",
                'data' => [
                    "reference_id" => $result["data"]["payment_id"],
                    "loan_id" => $inputData->loan_id,
                    "payment_id" => $inputData->payment_id,
                    'amount_paid' => $request->get('amount'),
                    'total_load' => $request->get('total_load'),
                    'token' => $request->get('token'),
                    'user_id' => $request->get('user_id'),
                ]
            ], $request['replay_to']);
            exit;
        }
    }
}
