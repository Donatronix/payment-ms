<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sumra\SDK\Traits\UuidTrait;

class Transaction extends Model
{
    use UuidTrait;
    use HasFactory;
    use SoftDeletes;

    public function paymentOrders(){
        return $this->belongsTo(PaymentOrder::class, 'payment_order_id', 'id');
    }
}
