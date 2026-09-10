<?php
namespace Shipping\Ajax\Admin;

use Shipping\Models\ShippingFee;
use Shipping\Models\ShippingZone;
use SkillDo\Cms\Location\Location;
use SkillDo\Cms\Location\Location2;
use SkillDo\Http\Request;
use SkillDo\Validate\Rule;

class ShippingZoneAjax
{
    static function load(Request $request): void
    {
        $zones = ShippingZone::all();

        $fees = ShippingFee::select('id', 'name')->get();

        foreach ($zones as $zone) {
            $zone->feeName = '';
            foreach ($fees as $fee) {
                if($fee->id == $zone->feeId) {
                    $zone->feeName = $fee->name;
                    break;
                }
            }
        }

        response()->success(trans('ajax.load.success'), $zones);
    }

    static function add(Request $request): void
    {
        $validate = $request->validate([
            'zoneCity' => Rule::make(trans('Tỉnh thành'))->notEmpty(),
            'zoneFee'  => Rule::make(trans('Phí vận chuyển'))->notEmpty(),
        ]);

        if ($validate->fails())
        {
            response()->error($validate->errors());
        }

        $zone = [];

        $zone['city'] = trim((string)$request->input('zoneCity'));

        if($zone['city'] != 'all')
        {
            $name = Location2::provinces($zone['city']);

            if(empty($name) || empty($name->fullname))
            {
                response()->error(trans('Tỉnh thành bạn chọn không tồn tại'));
            }

            $name = $name->fullname;
        }
        else
        {
            $name = 'Tất cả Tỉnh/Thành phố';
        }

        $zone['name'] = $name;

        if(ShippingZone::where('city', $zone['city'])->count() != 0)
        {
            response()->error(trans('Khu vực vận chuyển đã tồn tại'));
        }

        $zone['feeId'] = (int)trim($request->input('zoneFee'));

        $fee = ShippingFee::find($zone['feeId']);

        if(!hasItems($fee))
        {
            response()->error(trans('Phí vận chuyển bạn chọn không tồn tại'));
        }

        $zone['wardOption']  = (int)$request->input('zoneWardOption');

        if($zone['wardOption'] == 0)
        {
            $zoneWards = $request->input('zoneWards');

            if(!hasItems($zoneWards))
            {
                response()->error(trans('Bạn chưa chọn phường xã cho khu vực'));
            }

            $wards = [];

            foreach ($zoneWards as $key => $item)
            {
                $item['id'] = $key;

                if(!isset($item['wards']) || !hasItems($item['wards']))
                {
                    response()->error(trans('Không được để trống giá trị quận huyện'));
                }

                foreach ($item['wards'] as $ward)
                {
                    if(in_array($ward, $wards) !== false)
                    {
                        $ward = Location2::wards($zone['city'], $ward);

                        $ward = (!empty($ward->fullname)) ? $ward->fullname : '';

                        response()->error(trans($ward.' đang bị trùng lập'));
                    }
                }

                $wards = array_merge($wards, $item['wards']);

                if(!isset($item['fee']))
                {
                    response()->error(trans('Bạn chưa chọn phí vận chuyển cho phường xã'));
                }

                $zoneWards[$key] = $item;
            }

            $zone['wards'] = $zoneWards;
        }
        else
        {
            $zone['wards'] = [];
        }

        $id = ShippingZone::insert($zone);

        if(!is_skd_error($id))
        {
            $zone['id'] = $id;

            $zone['feeName'] = $fee->name;

            response()->success(trans('ajax.add.success'), $zone);
        }

        response()->error(trans('ajax.add.error'));
    }

    static function save(Request $request): void
    {
        $validate = $request->validate([
            'zoneFee'     => Rule::make(trans('Phí vận chuyển'))->notEmpty(),
        ]);

        if ($validate->fails()) {
            response()->error($validate->errors());
        }

        $id = (int)$request->input('id');

        $zoneOld = ShippingZone::find($id);

        if(!hasItems($zoneOld))
        {
            response()->error(trans('Khu vực vận chuyển này không tồn tại hoặc đã bị xóa'));
        }

        $zone['id'] = $id;

        $zone['feeId'] = (int)trim($request->input('zoneFee'));

        $fee = ShippingFee::find($zone['feeId']);

        if(!hasItems($fee))
        {
            response()->error(trans('Phí vận chuyển bạn chọn không tồn tại'));
        }

        $zone['wardOption']  = (int)$request->input('zoneWardOption');

        if($zone['wardOption'] == 0)
        {
            $zoneWards = $request->input('zoneWards');

            if(!hasItems($zoneWards))
            {
                response()->error(trans('Bạn chưa chọn quận huyện cho khu vực'));
            }

            $wards = [];

            foreach ($zoneWards as $key => $item)
            {
                $item['id'] = $key;

                if(!isset($item['wards']) || !hasItems($item['wards']))
                {
                    response()->error(trans('Không được để trống giá trị quận huyện'));
                }

                foreach ($item['wards'] as $ward) {

                    $wardName = Location2::wards($zoneOld->city, $ward);

                    if(empty($wardName) || empty($wardName->fullname))
                    {
                        response()->error(trans('Quận huyện bạn chọn không đúng'));
                    }

                    if(in_array($ward, $wards) !== false)
                    {
                        response()->error(trans($wardName->fullname.' đang bị trùng lập'));
                    }
                }

                $wards = array_merge($wards, $item['wards']);

                if(!isset($item['fee']))
                {
                    response()->error(trans('Bạn chưa chọn phí vận chuyển cho quận huyện'));
                }

                $zoneWards[$key] = $item;
            }

            $zone['wards'] = $zoneWards;
        }
        else
        {
            $zone['wards'] = [];
        }

        $id = ShippingZone::insert($zone);

        if(!is_skd_error($id))
        {
            $zone['name'] = $zoneOld->name;

            $zone['feeName'] = $fee->name;

            response()->success(trans('ajax.save.success'), $zone);
        }

        response()->error(trans('ajax.save.error'));
    }

    static function delete(Request $request): void
    {
        $id   = (int)$request->input('data');

        $zone = ShippingZone::find($id);

        if(!hasItems($zone))
        {
            response()->error(trans('Khu vực vận chuyển không tồn tại'));
        }

        ShippingZone::whereKey($id)->delete();

        response()->success(trans('ajax.delete.success'));
    }
}