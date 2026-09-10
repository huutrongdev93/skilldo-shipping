<?php

namespace Shipping\Models;

use SkillDo\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ShippingFee extends Model {

    protected string $table = 'shipping_fee';

    protected array $columns = [
        'name'      => ['string'],
        'type'      => ['string'],
        'range'     => ['array'],
        'fee'       => ['int', 0],
        'default'   => ['int', 0],
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::retrieved(function (ShippingFee $shippingFee)
        {
            if(Str::isSerialized($shippingFee->range))
            {
                $shippingFee->range = unserialize($shippingFee->range);
            }
        });
    }
}
