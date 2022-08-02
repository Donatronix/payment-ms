<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sumra\SDK\Traits\OwnerTrait;
use Sumra\SDK\Traits\UuidTrait;

/**
 * Payment Order transaction result save
 *
 * @package App\Models
 *
 * @OA\Schema(
 *     schema="PaymentOrderTransactionSave",
 *
 *     @OA\Property(
 *         property="gateway",
 *         type="string",
 *         description="Payment service provider",
 *         default="stripe",
 *     ),
 *     @OA\Property(
 *         property="payment_order_id",
 *         type="string",
 *         description="Payment Order ID",
 *         example="80000000-8000-8000-8000-000000000008"
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         description="Transaction metadata",
 *
 *         @OA\Property(
 *             property="trx_id",
 *             type="string",
 *             description="TRX_ID from payment service provider",
 *             example="383892830232320323-23232"
 *         ),
 *         @OA\Property(
 *             property="wallet",
 *             type="string",
 *             description="Wallet address for blockchain payment",
 *             example="0x5225522..225222"
 *         ),
 *         @OA\Property(
 *             property="payment_intent",
 *             type="string",
 *             description="Stripe payment intent ID",
 *             example="pi_3LORsbKkrmrXUD8m0R64dRIj"
 *         ),
 *         @OA\Property(
 *             property="payment_intent_client_secret",
 *             type="string",
 *             description="Stripe payment intent client secret",
 *             example="pi_3LORsbKkrmrXUD8m0R64dRIj_secret_aD7IFpw1THIpAllpEbG4zUm2p"
 *         )
 *     )
 * )
 */

/**
 * Class Transaction
 *
 * @package App\Models
 */
class Transaction extends Model
{
    use HasFactory;
    use OwnerTrait;
    use SoftDeletes;
    use UuidTrait;

    public function paymentOrders(): BelongsTo
    {
        return $this->belongsTo(PaymentOrder::class);
    }
}
