<?php

namespace App\Listeners;

use App\Models\Payment as PaymentModel;
use App\Services\Payment as PaymentService;
use App\Models\LogPaymentRequest;
use App\Models\LogPaymentRequestError;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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

    // private const RECEIVER_LISTENER = 'LoanPayment';

    public function __construct($data)
    {
        dd($data);
        //
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
        // echo "Hi";
        // Log::info($data);


        // return $request = new \Illuminate\Http\Request($data);

        // $validation = Validator::make($request->all(), [
        //     'gateway' => 'string|required',
        //     'amount' => 'integer|required',
        //     'currency' => 'string',
        //     'service' => 'string',
        //     'replay_to' => 'string|required',
        //     'user_id' => 'integer|required',
        // ]);

        // if ($validation->fails()) {
        //     \PubSub::transaction(function () {
        //     })->publish(self::RECEIVER_LISTENER, [
        //         'type' => 'danger',
        //         'title' => "Validation Error",
        //         'message' => $validation->errors()
        //     ], $request['replay_to']);
        //     exit;
        // }

        // $inputData = (object)$request->all();

        // // Write log
        // try {
        //     LogPaymentRequest::create([
        //         'gateway' => $inputData->gateway,
        //         'service' => $inputData->service,
        //         'payload' => $inputData
        //     ]);
        // } catch (\Exception $e) {
        //     Log::info('Log of invoice failed: ' . $e->getMessage());
        // }

        // // Init manager
        // try {
        //     $system = PaymentService::getInstance($inputData->gateway);
        // } catch (\Exception $e) {
        //     \PubSub::transaction(function () {
        //     })->publish(self::RECEIVER_LISTENER, [
        //         'type' => 'danger',
        //         'title' => "Payment Service Error",
        //         'message' => $e->getMessage()
        //     ], $request['replay_to']);
        //     exit;
        // }

        // // Create internal order
        // $payment = PaymentModel::create([
        //     'type' => PaymentModel::TYPE_PAYIN,
        //     'gateway' => $request->get('gateway'),
        //     'amount' => $request->get('amount'),
        //     'currency' => mb_strtoupper($request->get('currency')),
        //     'service' => $request->get('service'),
        //     'user_id' => Auth::user()->getAuthIdentifier()
        // ]);

        // // Create invoice
        // $result = $system->charge($payment, $inputData);

        // // Return response
        // $code = 200;
        // if ($result['type'] === 'danger') {
        //     $code = 400;

        //     LogPaymentRequestError::create([
        //         'gateway' => $inputData->gateway,
        //         'payload' => $result['message']
        //     ]);

        //     \PubSub::transaction(function () {
        //     })->publish(self::RECEIVER_LISTENER, [
        //         'type' => 'danger',
        //         'title' => "An error occurred",
        //         'message' => $e->getMessage()
        //     ], $request['replay_to']);
        //     exit;
        // }

        // // Return result

        // \PubSub::transaction(function () {
        // })->publish(self::RECEIVER_LISTENER, array_merge($result, $code), $request['replay_to']);
        // exit;
    }
}
