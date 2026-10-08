<?php
namespace Shipping\Services;

use SkillDo\Support\Path;

class ActivatorService
{
    static function active(): void
    {
        $db = include Path::plugin('shipping/database/database.php');
        $db->up();

        //Mặc định phương thức tắt: chưa có bảng phí thì không được hiện ở trang thanh toán
        $shipping = \Option::get('cart_shipping');

        if(!is_array($shipping))
        {
            $shipping = [];
        }

        if(!isset($shipping['zone']))
        {
            $shipping['zone'] = ['enabled' => false];

            \Option::update('cart_shipping', $shipping);
        }
    }
}
