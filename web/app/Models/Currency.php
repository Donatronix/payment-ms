<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    const CURRENCY_USD = 1;
    const CURRENCY_EUR = 2;
    const CURRENCY_GBP = 3;

    /**
     * @var int[]
     */
    public static $currencies = [
        'USD' => self::CURRENCY_USD,
        'EUR' => self::CURRENCY_EUR,
        'GBP' => self::CURRENCY_GBP
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}
