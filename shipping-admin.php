<?php
if(!function_exists('register_shipping')) {
    function register_shipping( $tabs ) {
        $tabs[SHIP_KEY] 	= [
            'label'         => 'Phí ship theo quận huyện',
            'description'   => 'Tự động thêm phí giao hàng theo từng loại khu vực để khách hàng lựa chọn và tự động tính nó vào hóa đơn.',
            'callback'      => 'admin_shipping_setting',
            'class'         => 'shipping'
        ];

        return $tabs;
    }
    add_filter('shipping_gateways', 'register_shipping', 1 );
}

if(!function_exists('admin_shipping_setting')) {
	function admin_shipping_setting($key_shipping, $shipping) {
		$zone_id = (int)InputBuilder::get('zone_id');
		if(!empty($zone_id)) {
		    include ('admin/views/html-shipping-detail.php');
		}
		else {
		    include('admin/views/html-shipping.php');
		}
	}
}
