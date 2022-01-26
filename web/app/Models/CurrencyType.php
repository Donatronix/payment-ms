<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrencyType extends Model
{
    use HasFactory;

    /**
     * Currency status
     */
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * Currency types
     */
    const TYPE_FIAT = 1;
    const TYPE_CRYPTO = 2;

    /**
     * Currency statuses array
     *
     * @var int[]
     */
    public static $statuses = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE
    ];

    /**
     * Currency types array
     *
     * @var int[]
     */
    public static $types = [
        self::TYPE_FIAT,
        self::TYPE_CRYPTO
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'symbol',
        'type',
        'status'
    ];
}
