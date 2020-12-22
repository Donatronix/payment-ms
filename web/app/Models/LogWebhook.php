<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Balance
 *
 * @package App\Models
 */
class LogWebhook extends Model
{
    protected $table = 'log_invoices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'gateway',
        'request'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}