<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
    /**
     * Type of order
     */
    const TYPE_INVOICE = 1;
    const TYPE_PAYOUTS = 2;

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
        'type',
        'gateway',
        'amount',
        'currency',
        'check_code',
        'service',
        'document_id',
        'user_id',
        'status',
        'payload'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

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
