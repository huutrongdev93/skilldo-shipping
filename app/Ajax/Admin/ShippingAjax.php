<?php
namespace Shipping\Ajax\Admin;

use SkillDo\Cms\Location\Location;
use SkillDo\Cms\Location\Location2;

class ShippingAjax
{
    static function locations(): void
    {
        $data = [
            'cities'            =>  Location2::provinces(),
            'wards'             =>  Location2::wards(),
        ];

        response()->success(trans('ajax.load.success'), $data);
    }
}