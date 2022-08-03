<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
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

    /**
     * Payment Order Type
     */
    const TYPE_PAYIN = 1;
    const TYPE_PAYOUT = 2;
    const TYPE_ADJUSTMENT = 4;
    const TYPE_RETURN_IN = 5;
    const TYPE_RETURN_OUT = 6;
    const TYPE_FEE = 7;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'payload' => 'array',
        'based_meta' => 'array'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'gateway',
        'amount',
        'currency',
        'based_id',
        'based_type',
        'based_service',
        'based_meta',
        'status',
        'user_id',
        'document_id',
        'payload'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

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

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'payment_order_id', 'id');
    }
}
