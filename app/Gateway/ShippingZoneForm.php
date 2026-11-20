<?php
namespace Shipping\Gateway;

use Admin\Supports\Component;
use Admin\Supports\Components\BlockSystem;
use SkillDo\Cms\Location\Location;
use SkillDo\Cms\Plugin\Plugin;
use SkillDo\Http\Request;

trait ShippingZoneForm
{
    public function form()
    {
        $form = parent::form();

        $form->number($this->getName().'[freeShipAmount]', [
            'label' => trans('shipping::checkout.freeShip.label'),
            'note'  => trans('shipping::checkout.freeShip.note'),
            'min'   => 0,
            'step'  => 1000,
        ], $this->getGateway()->getFreeShipAmount() ?: '');

        echo Component::blockSystem(function (BlockSystem $block) use ($form)
        {
            $block->header('Thông tin')->description('Thông tin hiển thị ở trang thanh toán đơn hàng <br /><button class="btn btn-blue mt-2 js_shipping_btn__save" type="button"><i class="fa-duotone fa-floppy-disk-pen"></i> Lưu cấu hình</button>');
            $block->content($form);
        });

        $cities = Location::provincesOptions();

        Plugin::view('shipping', 'admin/shipping', [
            'cities' => $cities
        ]);

        Plugin::view('shipping', 'admin/shipping-zone', [
            'cities' => $cities
        ]);

        Plugin::view('shipping', 'admin/shipping-script', [
            'cities' => $cities
        ]);
    }

    public function saveConfig(Request $request)
    {
        $config = parent::saveConfig($request);

        $value = $request->input($this->getName().'.freeShipAmount');

        $config['freeShipAmount'] = (is_numeric($value) && $value > 0) ? (float)$value : 0;

        return $config;
    }
}