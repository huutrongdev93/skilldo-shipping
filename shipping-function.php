<?php
class ShippingFee extends \SkillDo\Model\Model {

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

        static::retrieved(function (ShippingFee $shippingFee) {
            if(Str::isSerialized($shippingFee->range)) {
                $shippingFee->range = unserialize($shippingFee->range);
            }
        });
    }

    static function deleteById($objectId): array|bool
    {
        return static::whereKey($objectId)->remove();
    }
}

class ShippingZone extends \SkillDo\Model\Model {

    protected string $table = 'shipping_zones';

    protected array $columns = [
        'name'              => ['string'],
        'feeId'             => ['int', 0],
        'city'              => ['string'],
        'districts'         => ['array'],
        'districtOption'    => ['int', 0],
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::retrieved(function (ShippingZone $shippingZone) {
            if(Str::isSerialized($shippingZone->districts)) {
                $shippingZone->districts = unserialize($shippingZone->districts);
            }
        });
    }

    static function deleteById($objectId): array|bool
    {
        return static::whereKey($objectId)->remove();
    }
}