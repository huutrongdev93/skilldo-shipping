<?php
namespace Shipping\Gateway;

use Ecommerce\Gateway\Shipping\Common\AbstractShippingBase;

class ShippingZone extends AbstractShippingBase
{
    use ShippingZoneForm;

    public function getName(): string
    {
        return 'zone';
    }

    public function getAdminName(): string
    {
        return 'Giao hàng tận nơi';
    }

    public function getAdminDescription(): string
    {
        return 'Tự động thêm phí giao hàng theo từng loại khu vực để khách hàng lựa chọn và tự động tính nó vào hóa đơn.';
    }
}