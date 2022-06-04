<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sumra\SDK\Traits\OwnerTrait;
use Sumra\SDK\Traits\UuidTrait;

class BitPayPaymentGatewaySetup extends Model
{
    use HasFactory;
    use OwnerTrait;
    use UuidTrait;
    use SoftDeletes;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'bitpay_environment',
        'api_token_merchant',
        'api_token_payroll',
        'bitpay_key_path',
        'private_key_password',
        'payment_webhook_url',
        'redirect_url',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}
