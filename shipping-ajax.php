<?php

use JetBrains\PhpStorm\NoReturn;
use SkillDo\Http\Request;
use SkillDo\Validate\Rule;

class ShippingAdminAjax {
    #[NoReturn]
    static function locations(): void
    {
        $data = [
            'cities'            =>  Skilldo\Location::provinces(),
            'districts'         =>  Skilldo\Location::districts(),
            'wards'             =>  Skilldo\Location::wards(),
        ];

        response()->success(trans('ajax.load.success'), $data);
    }
}
Ajax::admin('ShippingAdminAjax::locations');

class ShippingFeeAdminAjax {
    #[NoReturn]
    static function load(Request $request): void
    {
        if($request->isMethod('post')) {

            $fees = ShippingFee::gets(Qr::set()->select('id', 'name', 'type', 'range', 'default'));

            response()->success(trans('ajax.load.success'), $fees);
        }

        response()->error(trans('ajax.load.error'));
    }

    #[NoReturn]
    static function add(Request $request): void
    {
        if($request->isMethod('post')) {

            $validate = $request->validate([
                'feeName'     => Rule::make(trans('Tên phí vận chuyển'))->notEmpty(),
                'feeType'     => Rule::make(trans('Tiêu chuẩn tính phí vận chuyển'))->notEmpty(),
                'range'       => Rule::make(trans('Danh sách hạn mức vận chuyển'))->notEmpty(),
                'range.*.min' => Rule::make(trans('Giá trị hạn mức (từ)'))->notEmpty()->integer(),
                'range.*.max' => Rule::make(trans('Giá trị hạn mức (đến)'))->notEmpty()->integer(),
            ]);

            if ($validate->fails()) {
                response()->error($validate->errors());
            }

            $fee = array();

            $fee['name'] = trim($request->input('feeName'));

            $fee['type'] = trim($request->input('feeType'));

            $range = $request->input('range');

            if(!have_posts($range)) {
                response()->error(trans('error.shipping.range'));
            }

            foreach ($range as $key => $item) {

                $item['id'] = $key;

                $item['min'] = Str::price($item['min']);

                $item['max'] = Str::price($item['max']);

                $item['unit'] = ($fee['type'] == 'weight') ? Prd::weightUnit() : Prd::priceUnit();

                $item['fee'] = Str::price($item['fee']);

                if(empty($item['fee'])) {
                    response()->error(trans('error.shipping.range.price', [
                        'min' => $item['min'],
                        'max' => $item['max']
                    ]));
                }

                $range[$key] = $item;
            }

            $fee['range'] = $range;

            $id = ShippingFee::insert($fee);

            if(!is_skd_error($id)) {

                $fee['id'] = $id;

                $default = (int)trim((string)$request->input('feeDefault'));

                if($default == 1) {
                    $fee['default'] = 1;
                    ShippingFee::where('default', 1)->update(['default' => 0]);
                    ShippingFee::where('id', $id)->update(['default' => 1]);
                }

                response()->success(trans('ajax.add.success'), $fee);
            }
        }

        response()->error(trans('ajax.add.error'));
    }

    #[NoReturn]
    static function save(Request $request): void
    {
        if($request->isMethod('post')) {

            $id = (int)$request->input('id');

            $feeOld = ShippingFee::get($id);

            if(!have_posts($feeOld)) {
                response()->error(trans('error.shipping.notfound'));
            }

            $validate = $request->validate([
                'feeName'     => Rule::make(trans('Tên phí vận chuyển'))->notEmpty(),
                'feeType'     => Rule::make(trans('Tiêu chuẩn tính phí vận chuyển'))->notEmpty(),
                'range'       => Rule::make(trans('Danh sách hạn mức vận chuyển'))->notEmpty(),
                'range.*.min' => Rule::make(trans('Giá trị hạn mức (từ)'))->notEmpty()->integer(),
                'range.*.max' => Rule::make(trans('Giá trị hạn mức (đến)'))->notEmpty()->integer(),
            ]);

            if ($validate->fails()) {
                response()->error($validate->errors());
            }

            $fee['id'] = $id;

            $fee['name'] = trim($request->input('feeName'));

            $fee['type'] = trim($request->input('feeType'));

            $range = $request->input('range');

            if(!have_posts($range)) {
                response()->error(trans('error.shipping.range'));
            }

            foreach ($range as $key => $item) {

                $item['id'] = $key;

                $item['min'] = Str::price($item['min']);

                $item['max'] = Str::price($item['max']);

                $item['unit'] = ($fee['type'] == 'weight') ? Prd::weightUnit() : Prd::priceUnit();

                $item['fee'] = Str::price($item['fee']);

                if(empty($item['fee'])) {
                    response()->error(trans('error.shipping.range.price', [
                        'min' => $item['min'],
                        'max' => $item['max']
                    ]));
                }

                $range[$key] = $item;
            }

            $fee['range'] = $range;

            $id = ShippingFee::insert($fee);

            if(!is_skd_error($id)) {

                $fee['id'] = $id;

                $default = (int)trim((string)$request->input('feeDefault'));

                if($default == 1) {

                    $fee['default'] = 1;

                    ShippingFee::where('default', 1)->update(['default' => 0]);

                    ShippingFee::where('id', $id)->update(['default' => 1]);
                }

                response()->success(trans('ajax.save.success'), $fee);
            }
        }

        response()->error(trans('ajax.save.error'));
    }

    #[NoReturn]
    static function delete(Request $request): void
    {
        if($request->isMethod('post')) {

            $id       = (int)$request->input('data');

            $zones = ShippingZone::gets();

            if(have_posts($zones)) {
                foreach ($zones as $zone) {
                    if($zone->feeId == $id) {
                        response()->error(trans('error.shipping.zone.use', ['name' => $zone->name]));
                    }
                    if($zone->districtOption == 0) {
                        foreach ($zone->districts as $district) {
                            if($district['fee'] == $id) {
                                response()->error(trans('error.shipping.zone.use.price', ['name' => $zone->name]));
                            }
                        }
                    }
                }
            }

            $fee = ShippingFee::get($id);

            if(!have_posts($fee)) {
                response()->error(trans('error.shipping.notfound'));
            }

            if($fee->default == 1) {
                response()->error(trans('error.shipping.default'));
            }

            ShippingFee::delete($id);

            response()->success(trans('ajax.delete.success'));
        }

        response()->error(trans('ajax.delete.error'));
    }
}
Ajax::admin('ShippingFeeAdminAjax::load');
Ajax::admin('ShippingFeeAdminAjax::add');
Ajax::admin('ShippingFeeAdminAjax::save');
Ajax::admin('ShippingFeeAdminAjax::delete');

class ShippingZoneAdminAjax {
    #[NoReturn]
    static function load(Request $request): void
    {
        if($request->isMethod('post')) {

            $zones = ShippingZone::gets(Qr::set());

            $fees = ShippingFee::gets(Qr::set()->select('id', 'name'));

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

        response()->error(trans('ajax.load.error'));
    }

    #[NoReturn]
    static function add(Request $request): void
    {
        if($request->isMethod('post')) {

            $validate = $request->validate([
                'zoneCity'     => Rule::make(trans('Tỉnh thành'))->notEmpty(),
                'zoneFee'     => Rule::make(trans('Phí vận chuyển'))->notEmpty(),
            ]);

            if ($validate->fails()) {
                response()->error($validate->errors());
            }

            $zone = [];

            $zone['city'] = trim((string)$request->input('zoneCity'));

            if($zone['city'] != 'all') {

                $name = \Skilldo\Location::provinces($zone['city']);

                if(empty($name) || empty($name->fullname)) {
                    response()->error(trans('Tỉnh thành bạn chọn không tồn tại'));
                }

                $name = $name->fullname;
            }
            else {
                $name = 'Tất cả Tỉnh/Thành phố';
            }


            $zone['name'] = $name;

            if(ShippingZone::count(Qr::set('city', $zone['city'])) != 0) {
                response()->error(trans('Khu vực vận chuyển đã tồn tại'));
            }

            $zone['feeId'] = (int)trim($request->input('zoneFee'));

            $fee = ShippingFee::get($zone['feeId']);

            if(!have_posts($fee)) {
                response()->error(trans('Phí vận chuyển bạn chọn không tồn tại'));
            }

            $zone['districtOption']  = (int)$request->input('zoneDistrictOption');

            if($zone['districtOption'] == 0) {

                $zoneDistricts = $request->input('zoneDistricts');

                if(!have_posts($zoneDistricts)) {
                    response()->error(trans('Bạn chưa chọn quận huyện cho khu vực'));
                }

                $districts = [];

                foreach ($zoneDistricts as $key => $item) {

                    $item['id'] = $key;

                    if(!isset($item['districts']) || !have_posts($item['districts'])) {
                        response()->error(trans('Không được để trống giá trị quận huyện'));
                    }

                    foreach ($item['districts'] as $district) {

                        if(in_array($district, $districts) !== false) {

                            $district = \Skilldo\Location::districts($zone['city'], $district);

                            $district = (!empty($district->fullname)) ? $district->fullname : '';

                            response()->error(trans($district.' đang bị trùng lập'));
                        }
                    }

                    $districts = array_merge($districts, $item['districts']);

                    if(!isset($item['fee'])) {
                        response()->error(trans('Bạn chưa chọn phí vận chuyển cho quận huyện'));
                    }

                    $zoneDistricts[$key] = $item;
                }

                $zone['districts'] = $zoneDistricts;
            }
            else {
                $zone['districts'] = [];
            }

            $id = ShippingZone::insert($zone);

            if(!is_skd_error($id)) {

                $zone['id'] = $id;

                $zone['feeName'] = $fee->name;

                response()->success(trans('ajax.add.success'), $zone);
            }
        }

        response()->error(trans('ajax.add.error'));
    }

    #[NoReturn]
    static function save(Request $request): void
    {
        if($request->isMethod('post')) {

            $validate = $request->validate([
                'zoneFee'     => Rule::make(trans('Phí vận chuyển'))->notEmpty(),
            ]);

            if ($validate->fails()) {
                response()->error($validate->errors());
            }

            $id = (int)$request->input('id');

            $zoneOld = ShippingZone::get($id);

            if(!have_posts($zoneOld)) {
                response()->error(trans('Khu vực vận chuyển này không tồn tại hoặc đã bị xóa'));
            }

            $zone['id'] = $id;

            $zone['feeId'] = (int)trim($request->input('zoneFee'));

            $fee = ShippingFee::get($zone['feeId']);

            if(!have_posts($fee)) {
                response()->error(trans('Phí vận chuyển bạn chọn không tồn tại'));
            }

            $zone['districtOption']  = (int)$request->input('zoneDistrictOption');

            if($zone['districtOption'] == 0) {

                $zoneDistricts = $request->input('zoneDistricts');

                if(!have_posts($zoneDistricts)) {
                    response()->error(trans('Bạn chưa chọn quận huyện cho khu vực'));
                }

                $districts = [];

                foreach ($zoneDistricts as $key => $item) {

                    $item['id'] = $key;

                    if(!isset($item['districts']) || !have_posts($item['districts'])) {
                        response()->error(trans('Không được để trống giá trị quận huyện'));
                    }

                    foreach ($item['districts'] as $district) {

                        $districtName = \Skilldo\Location::districts($zoneOld->city, $district);

                        if(empty($districtName) || empty($districtName->fullname)) {
                            response()->error(trans('Quận huyện bạn chọn không đúng'));
                        }
                        if(in_array($district, $districts) !== false) {
                            response()->error(trans($districtName->fullname.' đang bị trùng lập'));
                        }
                    }

                    $districts = array_merge($districts, $item['districts']);

                    if(!isset($item['fee'])) {
                        response()->error(trans('Bạn chưa chọn phí vận chuyển cho quận huyện'));
                    }

                    $zoneDistricts[$key] = $item;
                }

                $zone['districts'] = $zoneDistricts;
            }
            else {
                $zone['districts'] = [];
            }

            $id = ShippingZone::insert($zone);

            if(!is_skd_error($id)) {

                $zone['name'] = $zoneOld->name;

                $zone['feeName'] = $fee->name;

                response()->success(trans('ajax.save.success'), $zone);
            }
        }

        response()->error(trans('ajax.save.error'));
    }

    #[NoReturn]
    static function delete(Request $request): void
    {
        if($request->isMethod('post')) {

            $id   = (int)$request->input('data');

            $zone = ShippingZone::get($id);

            if(!have_posts($zone)) {
                response()->error(trans('Khu vực vận chuyển không tồn tại'));
            }

            ShippingZone::delete($id);

            response()->error(trans('Xóa dữ liệu thành công'));
        }

        response()->error(trans('ajax.delete.error'));
    }
}
Ajax::admin('ShippingZoneAdminAjax::load');
Ajax::admin('ShippingZoneAdminAjax::add');
Ajax::admin('ShippingZoneAdminAjax::save');
Ajax::admin('ShippingZoneAdminAjax::delete');