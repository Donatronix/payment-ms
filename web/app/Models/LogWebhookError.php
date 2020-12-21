<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Balance
 *
 * @package App\Models
 */
class LogWebhookError extends Model
{
    protected $table = 'log_webhooks_errors';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'error'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}
