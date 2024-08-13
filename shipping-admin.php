<?php
Class ShippingAdmin {
    static function setting($keyShipping, $shipping): void
    {
        $cities = Skilldo\Location::provincesOptions();

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
}