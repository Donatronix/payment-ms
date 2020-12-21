<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentOrder extends Model
{
    /**
     * Type of order
     */
    const TYPE_ORDER_INVOICE = 1;
    const TYPE_ORDER_PAYOUTS = 2;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'response' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'document_id',
        'amount',
        'check_code',
        'type',
        'gateway',
        'status',
        'response'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * @var string
     */
    protected $table = 'payment_orders';

    /**
     * Generate unique ID
     *
     * Copied from Wallet
     *
     * @return string
     */
    public static function getCheckCode(): string
    {
        $checkCode = (string)Str::orderedUuid();

        $checkCodeExists = self::where('check_code', $checkCode)->exists();
        if ($checkCodeExists) {
            return self::getCheckCode();
        }

        return $checkCode;
    }
}
