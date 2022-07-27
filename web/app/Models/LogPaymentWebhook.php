<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sumra\SDK\Traits\UuidTrait;

/**
 * Model Balance
 *
 * @package App\Models
 */
class LogPaymentWebhook extends Model
{
    use UuidTrait;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'gateway',
        'payment_order_id',
        'payload'
    ];
}
