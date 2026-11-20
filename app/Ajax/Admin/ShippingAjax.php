<?php
namespace Shipping\Ajax\Admin;

use SkillDo\Cms\Location\Location;
use SkillDo\Cms\Location\Location2;

class ShippingAjax
{
    static function locations(): void
    {
        // Ajax::admin() chỉ chặn ở mức loggin_admin: kiểm quyền theo chức năng.
        if(!\SkillDo\Support\Auth::hasCap('edit_setting'))
        {
            response()->error(trans('admin::app.categoryAccessForbidden'));
        }

        $data = [
            'cities'            =>  Location2::provinces(),
            'wards'             =>  Location2::wards(),
        ];

        response()->success(trans('ajax.load.success'), $data);
    }
}