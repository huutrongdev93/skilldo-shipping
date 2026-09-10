<?php
namespace Shipping\Ajax\Admin;

use Ecommerce\Supports\Prd;
use Shipping\Models\ShippingFee;
use Shipping\Models\ShippingZone;
use SkillDo\Http\Request;
use Illuminate\Support\Str;
use SkillDo\Validate\Rule;

class ShippingFeeAjax
{
    static function load(Request $request): void
    {
        $fees = ShippingFee::select('id', 'name', 'type', 'range', 'default')->get();

        response()->success(trans('ajax.load.success'), $fees);
    }

    static function add(Request $request): void
    {
        $validate = $request->validate([
            'feeName'     => Rule::make(trans('Tên phí vận chuyển'))->notEmpty(),
            'feeType'     => Rule::make(trans('Tiêu chuẩn tính phí vận chuyển'))->notEmpty(),
            'range'       => Rule::make(trans('Danh sách hạn mức vận chuyển'))->notEmpty(),
            'range.*.min' => Rule::make(trans('Giá trị hạn mức (từ)'))->notEmpty()->integer(),
            'range.*.max' => Rule::make(trans('Giá trị hạn mức (đến)'))->notEmpty()->integer(),
        ]);

        if ($validate->fails())
        {
            response()->error($validate->errors());
        }

        $fee = array();

        $fee['name'] = trim($request->input('feeName'));

        $fee['type'] = trim($request->input('feeType'));

        $range = $request->input('range');

        if(!hasItems($range))
        {
            response()->error(trans('error.shipping.range'));
        }

        foreach ($range as $key => $item) {

            $item['id'] = $key;

            $item['min'] = Str::price($item['min']);

            $item['max'] = Str::price($item['max']);

            $item['unit'] = ($fee['type'] == 'weight') ? Prd::weightUnit() : Prd::priceUnit();

            $item['fee'] = Str::price($item['fee']);

            if(empty($item['fee']))
            {
                response()->error(trans('error.shipping.range.price', [
                    'min' => $item['min'],
                    'max' => $item['max']
                ]));
            }

            $range[$key] = $item;
        }

        $fee['range'] = $range;

        $id = ShippingFee::insert($fee);

        if(!is_skd_error($id))
        {
            $fee['id'] = $id;

            $default = (int)trim((string)$request->input('feeDefault'));

            if($default == 1)
            {
                $fee['default'] = 1;
                ShippingFee::where('default', 1)->update(['default' => 0]);
                ShippingFee::where('id', $id)->update(['default' => 1]);
            }

            response()->success(trans('ajax.add.success'), $fee);
        }
    }

    static function save(Request $request): void
    {
        $id = (int)$request->input('id');

        $feeOld = ShippingFee::get($id);

        if(!hasItems($feeOld)) {
            response()->error(trans('error.shipping.notfound'));
        }

        $validate = $request->validate([
            'feeName'     => Rule::make(trans('Tên phí vận chuyển'))->notEmpty(),
            'feeType'     => Rule::make(trans('Tiêu chuẩn tính phí vận chuyển'))->notEmpty(),
            'range'       => Rule::make(trans('Danh sách hạn mức vận chuyển'))->notEmpty(),
            'range.*.min' => Rule::make(trans('Giá trị hạn mức (từ)'))->notEmpty()->integer(),
            'range.*.max' => Rule::make(trans('Giá trị hạn mức (đến)'))->notEmpty()->integer(),
        ]);

        if ($validate->fails())
        {
            response()->error($validate->errors());
        }

        $fee['id'] = $id;

        $fee['name'] = trim($request->input('feeName'));

        $fee['type'] = trim($request->input('feeType'));

        $range = $request->input('range');

        if(!hasItems($range)) {
            response()->error(trans('error.shipping.range'));
        }

        foreach ($range as $key => $item)
        {
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

        response()->error(trans('ajax.save.error'));
    }

    static function delete(Request $request): void
    {
        $id       = (int)$request->input('data');

        $zones = ShippingZone::all();

        if(hasItems($zones))
        {
            foreach ($zones as $zone)
            {
                if($zone->feeId == $id)
                {
                    response()->error(trans('error.shipping.zone.use', ['name' => $zone->name]));
                }
                if($zone->wardOption == 0)
                {
                    foreach ($zone->wards as $ward)
                    {
                        if($ward['fee'] == $id)
                        {
                            response()->error(trans('error.shipping.zone.use.price', ['name' => $zone->name]));
                        }
                    }
                }
            }
        }

        $fee = ShippingFee::find($id);

        if(!hasItems($fee))
        {
            response()->error(trans('error.shipping.notfound'));
        }

        if($fee->default == 1)
        {
            response()->error(trans('error.shipping.default'));
        }

        ShippingFee::whereKey($id)->delete();

        response()->success(trans('ajax.delete.success'));
    }
}