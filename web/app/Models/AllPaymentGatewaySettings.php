<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sumra\SDK\Traits\OwnerTrait;
use Sumra\SDK\Traits\UuidTrait;

class AllPaymentGatewaySettings extends Model
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
        'payment_gateway_name',

        'bitpay_environment',
        'bitpay_api_token_merchant',
        'bitpay_api_token_payroll',
        'bitpay_key_path',
        'bitpay_private_key_password',
        'bitpay_payment_webhook_url',
        'bitpay_redirect_url',

        'coinbase_api_key',
        'coinbase_webhook_key',
        'coinbase_redirect_url',
        'coinbase_cancel_url',

        'openpayd_username',
        'openpayd_password',
        'openpayd_url',
        'openpayd_public_key_path',

        'paypal_mode',
        'paypal_notify_url',
        'paypal_currency',
        'paypal_sandbox_client_id',
        'paypal_sandbox_client_secret',
        'paypal_live_client_id',
        'paypal_live_client_secret',
        'paypal_sandbox_api_url',
        'paypal_live_api_url',
        'paypal_payment_action',
        'paypal_locale',
        'paypal_validate_ssl',
        'paypal_app_id',

        'stripe_webhook_secret',
        'stripe_public_key',
        'stripe_secret_key',

        'status',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}
