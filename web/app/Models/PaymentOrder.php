<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Sumra\SDK\Traits\NumeratorTrait;
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
 * Class PaymentOrder
 * @package App\Models
 */
class PaymentOrder extends Model
{
    use HasFactory;
    use OwnerTrait;
    use UuidTrait;
    use SoftDeletes;
    use NumeratorTrait;

    /**
     * Payment Order Type
     */
    const TYPE_CHARGE = 1;
    const TYPE_PAYOUT = 2;

    /**
     * Payment Order Statuses
     */
    // Occurs when a new Payment Order is created.
    const STATUS_ORDER_CREATED = 1000;

    // Occurs when a Payment Order has started processing.
    const STATUS_ORDER_PROCESSING = 2000;

    // Occurs when a Payment Order has processing and partially funded
    const STATUS_ORDER_PARTIALLY_FUNDED = 2010;

    // Occurs when been confirmed and the associated payment is completed
    const STATUS_ORDER_CONFIRMED = 2020;

    // Occurs when received a payment after it had been expired
    const STATUS_ORDER_DELAYED = 2030;

    // Occurs when a Payment Order has successfully completed payment
    const STATUS_ORDER_SUCCEEDED = 3000;

    // Occurs when a Payment Order is canceled
    const STATUS_ORDER_CANCELED = 4000;

    // Occurs when a Payment Order has failed
    const STATUS_ORDER_FAILED = 5000;

    /**
     * @var int[]
     */
    public static array $statuses = [
        'created' => self::STATUS_ORDER_CREATED,
        'processing' => self::STATUS_ORDER_PROCESSING,
        'partially_funded' => self::STATUS_ORDER_PARTIALLY_FUNDED,
        'confirmed' => self::STATUS_ORDER_CONFIRMED,
        'delayed' => self::STATUS_ORDER_DELAYED,
        'failed' => self::STATUS_ORDER_FAILED,
        'succeeded' => self::STATUS_ORDER_SUCCEEDED,
        'canceled' => self::STATUS_ORDER_CANCELED
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'payload' => 'array',
        'based_metadata' => 'array'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'amount',
        'currency',
        'user_id',
        'status',
        'based_id',
        'based_object',
        'based_service',
        'based_metadata',
        'service_key',
        'service_document_id',
        'service_document_type',
        'service_payload',
        'metadata'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Get the numerator prefix for the model.
     *
     * @return string
     */
    protected function getNumeratorPrefix(): string
    {
        return 'POR';
    }

    /**
     * Boot the model.
     *
     * @return  void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($obj) {
            do {
                // generate a random uuid
                $checkCode = Str::orderedUuid();
            } //check if the code already exists, try again
            while (self::where('check_code', $checkCode)->first());

            $obj->setAttribute('check_code', $checkCode);
        });
    }
}
