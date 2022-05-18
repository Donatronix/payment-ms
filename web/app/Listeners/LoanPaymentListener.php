<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Validator;
use App\Models\Payment as PaymentModel;
use App\Services\Payment as PaymentService;
use App\Models\LogPaymentRequest;
use App\Models\LogPaymentRequestError;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LoanPaymentListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    private $data;

    public function __construct($data)
    {
        //
        $this->data = $data;
    }

    /**
     * Handle the event.
     *
     * @param  LoanPaymentEvent  $event
     * @return void
     */
    public function handle()
    {
        // try{
        $request = new \Illuminate\Http\Request($this->data);
        //  return app('\App\Api\V1\Controllers\PaymentController')->charge($request);
        // } catch (\Exception $e) {
        //     echo $e->getMessage();
        // }
        // echo "I reach";
        // // Validate input
        // $validation = Validator::make($inputData, [
        //         'gateway' => 'string|required',
        //         'amount' => 'integer|required',
        //         'currency' => 'string',
        //         'service' => 'string',
        // ]);

        // if ($validation->fails()) {
        //     \PubSub::transaction(function () {})->publish(self::RECEIVER_LISTENER, [
        //         'status' => 'error',
        //         'order_id' => $inputData['order_id'],
        //         'message' => $validation->errors()
        //     ], $inputData['replay_to']);

        //     exit;
        // }

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
            return response()->json([
                'type' => 'danger',
                'message' => $e->getMessage()
            ], 400);
        }

        //dd($system);

        // Create internal order
        $payment = PaymentModel::create([
            'type' => PaymentModel::TYPE_PAYIN,
            'gateway' => $request->get('gateway'),
            'amount' => $request->get('amount'),
            'currency' => mb_strtoupper($request->get('currency')),
            'service' => $request->get('service'),
            'user_id' => Auth::user()->getAuthIdentifier()
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
        }

        // Return result
        return response()->json($result, $code);
    }
}
