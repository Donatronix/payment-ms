<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sumra\SDK\Traits\UuidTrait;

class PaymentService extends Model
{
    use HasFactory;
    use UuidTrait;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'gateway',
        'description',
        'new_order_status'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Get the payment settings for the payment service
     */
    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }

    /**
     * @return array
     */
    public static function catalog(): array
    {
        $systems = [];

        $dir = base_path('app/Services/PaymentServiceProviders');

        if ($handle = opendir($dir)) {
            /* Именно такой способ чтения элементов каталога является правильным. */
            while (false !== ($entry = readdir($handle))) {
                if (($entry == '.') || ($entry == '..'))
                    continue;

                $class = '\App\Services\PaymentServiceProviders\\' . preg_replace('/\.php/', '', $entry);

                if (!class_exists($class))
                    continue;

                try {
                    $systems[$class::key()] = [
                        'title' => $class::title(),
                        'key' => $class::key(),
                        'description' => $class::description(),
                        'icon' => null,
                        'new_order_status' => $class::newOrderStatus()
                    ];
                } catch (\Exception $e) {
                    throw new \Exception($entry . ' ' . $e->getMessage());
                }
            }

            closedir($handle);
        }

        return $systems;
    }
}
