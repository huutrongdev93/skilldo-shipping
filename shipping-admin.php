<?php
Class ShippingAdmin {
    static function setting($keyShipping, $shipping): void
    {
        $cities = Cart_Location::cities();

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