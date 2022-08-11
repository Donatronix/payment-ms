<?php

namespace App\Api\V1\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\PaymentOrder;
use App\Services\PaymentServiceManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class TransactionController
 *
 * @package App\Api\V1\Controllers
 */
class TransactionController extends Controller
{
    /**
     * Store payment order transaction result for confirmation
     *
     * @OA\Post(
     *     path="/app/transactions",
     *     summary="Store payment order transaction result for confirmation",
     *     description="Store payment order transaction result for confirmation",
     *     tags={"Application | Payment Orders Transactions"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PaymentOrderTransactionSave")
     *     ),
     *
     *     @OA\Response(
     *         response="201",
     *         description="Payment order transaction saved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found"
     *     )
     * )
     *
     * @param Request $request
     */
    public function __invoke(Request $request)
    {
        try {
            $inputData = (object)$this->validate($request, [
                'gateway' => 'required|string',
                'payment_order_id' => 'required|string',
                'meta' => 'required|array',
                'meta.trx_id' => 'sometimes|string',
                'meta.wallet' => 'sometimes|string',
                'meta.payment_intent' => 'sometimes|string',
                'meta.payment_intent_client_secret' => 'sometimes|string'
            ]);

            // Get payment order
            $order = PaymentOrder::findOrFail($inputData->payment_order_id);

            // Init payment service client
            $service = PaymentServiceManager::getInstance($inputData->gateway);

            // Checking transaction
            $result = $service->checkTransaction($inputData);

            // Save transaction status and data
            $metadata = $inputData->meta;
            $metadata['transaction_id'] = $result['transaction_id'] ?? null;

            $order->fill([
                'status' => PaymentOrder::$statuses[$result['status']],
                'metadata' => $metadata
            ]);
            $order->save();

            // If succeeded then add more data to response
            if($result['status'] === 'succeeded'){
                $result['amount'] = $order->amount;
                $result['currency'] = $order->currency;
                $result['date'] = Carbon::parse($order->created_at)->format('d M Y h:i A');
                $result['order_number'] = $order->number;
            }

            // Send through PUBSUB confirmation to document owner
            if($order->based_id !== config('settings.empty_uuid') && $order->based_object && $order->based_service){
                \PubSub::publish('PaymentUpdateRequest', [
                    'status' => $result['status'],
                    'document_id' => $order->based_id,
                    'document_object' => $order->based_object,
                    'payment_order_id' => $order->id
                ], $order->based_service);
            }

            // Return response
            return response()->jsonApi([
                'title' => 'Saving and verifying transaction data',
                'message' => 'Payment order transaction saved successfully',
                'data' => $result
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'title' => 'Saving and verifying transaction data',
                'message' => "Field validation error: " . $e->getMessage(),
                'data' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'title' => 'Saving and verifying transaction data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
