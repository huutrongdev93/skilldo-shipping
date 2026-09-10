<?php
namespace Shipping\Models;

use SkillDo\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ShippingZone extends Model {

    protected string $table = 'shipping_zones';

    protected array $columns = [
        'name'              => ['string'],
        'feeId'             => ['int', 0],
        'city'              => ['string'],
        'wards'         => ['array'],
        'wardOption'    => ['int', 0],
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::retrieved(function (ShippingZone $shippingZone)
        {
            if(Str::isSerialized($shippingZone->wards))
            {
                $shippingZone->wards = unserialize($shippingZone->wards);
            }
        });
    }
}