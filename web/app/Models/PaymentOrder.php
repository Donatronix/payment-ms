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
    const TYPE_ADJUSTMENT = 4;
    const TYPE_RETURN_IN = 5;
    const TYPE_RETURN_OUT = 6;
    const TYPE_FEE = 7;

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
        'based_type',
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
        return 'PO';
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

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'payment_order_id', 'id');
    }
}
