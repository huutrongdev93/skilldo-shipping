<?php
class ShippingFee extends \SkillDo\Model\Model {

    static string $table = 'shipping_fee';

    static array $columns = [
        'name'      => ['string'],
        'type'      => ['string'],
        'range'     => ['array'],
        'fee'       => ['int', 0],
        'default'   => ['int', 0],
    ];

    static array $rules = [
        'created' => true,
        'updated' => true,
    ];

    static function get($args = []) {
        $zone = parent::get($args);
        if (have_posts($zone)) {
            if(isset($zone->range)) $zone->range = unserialize($zone->range);
        }
        return $zone;
    }

    static function gets($args = []) {
        $zone = parent::gets($args);
        if (have_posts($zone)) {
            foreach ($zone as $value) {
                if(isset($value->range)) $value->range = unserialize($value->range);
            }
        }
        return $zone;
    }

    static function deleteById($objectId = 0): array|bool
    {
        $objectId = (int)Str::clear($objectId);

        if($objectId == 0 ) return false;

        $model = model(self::$table);

        if($model->delete(Qr::set($objectId))) {
            return [$objectId];
        }

        return false;
    }
}

class ShippingZone extends \SkillDo\Model\Model {

    static string $table = 'shipping_zones';

    static array $columns = [
        'name'              => ['string'],
        'feeId'             => ['int', 0],
        'city'              => ['string'],
        'districts'         => ['array'],
        'districtOption'    => ['int', 0],
    ];

    static array $rules = [
        'created' => true,
        'updated' => true,
    ];

    static function get($args = []) {
        $zone = parent::get($args);
        if (have_posts($zone)) {
            if(isset($zone->districts)) $zone->districts = unserialize($zone->districts);
        }
        return $zone;
    }

    static function gets($args = []) {
        $zone = parent::gets($args);
        if (have_posts($zone)) {
            foreach ($zone as $value) {
                if(isset($value->districts)) $value->districts = unserialize($value->districts);
            }
        }
        return $zone;
    }

    static function deleteById($objectId = 0): array|bool
    {
        $objectId = (int)Str::clear($objectId);
        if($objectId == 0 ) return false;
        $model = model(self::$table);
        if($model->delete(Qr::set($objectId))) {
            return [$objectId];
        }
        return false;
    }
}