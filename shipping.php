<?php
/**
 * Plugin name     : Shipping
 * Plugin class    : shipping
 * Plugin uri      : http://sikido.vn
 * Description     : Plugin tính phí vận chuyển theo quận huyện
 * Author          : SKDSoftware Dev Team
 * Version         : 2.0.1
 */
define('SHIP_NAME', 'shipping');

define('SHIP_FOLDER', 'shipping');

define('SHIP_PATH', Path::plugin(SHIP_FOLDER));

define('SHIP_KEY', 'zone');

define('SHIP_VERSION', '2.0.1');

class shipping {

    private $name = 'shipping';

    function __construct() {}

    static public function config($result) {

        $shipping_key = InputBuilder::post('shipping_key');

        $zone_id = (int)InputBuilder::post('zone_id');

        if (empty($zone_id)) {

            $data = InputBuilder::post();

            $shipping = option::get('cart_shipping', []);

            if (!have_posts($shipping)) $shipping = [];

            $shipping[$shipping_key]['enabled'] = (!empty($data['enabled'])) ? Str::clear($data['enabled']) : false;

            $shipping[$shipping_key]['range_price'] = (!empty($data['range_price'])) ? Str::clear($data['range_price']) : false;

            $shipping[$shipping_key]['title'] = InputBuilder::post('title');

            $shipping[$shipping_key]['img'] = FileHandler::handlingUrl(InputBuilder::post('img'));

            $shipping[$shipping_key]['price_default'] = Str::clear($data['price_default']);

            if (have_posts($shipping)) {

                Option::update('cart_shipping', $shipping);
            }
        } else {

            $zone = shipping::getZone($zone_id);

            if (have_posts($zone)) {

                $model = get_model()->settable('wcmc_shipping_zones');

                $data = [];

                $data['zone_name'] = InputBuilder::post('zone_name');

                $data['zone_type'] = InputBuilder::post('zone_type');

                $data['zone_price'] = (int)InputBuilder::post('zone_price');

                $data['range_zone_price'] = (!empty(InputBuilder::post('range_zone_price'))) ? serialize(InputBuilder::post('range_zone_price')) : false;


                if ($model->update_where($data, ['id' => $zone_id])) {

                    $locations = InputBuilder::post('zone_locations');

                    $model->settable('wcmc_shipping_zone_locations');

                    foreach ($zone->locations as $location) {

                        if (in_array($location->location_type, $locations) === false) {

                            $model->delete_where(array('zone_id' => $zone_id, 'location_type' => $location->location_type));

                        } else unset($locations[array_search($location->location_type, $locations)]);

                    }

                    if (have_posts($locations)) {

                        $zone_location['zone_id'] = $zone_id;

                        $zone_location['location_code'] = 'VN';

                        foreach ($locations as $value) {

                            $zone_location['location_type'] = $value;

                            $model->add($zone_location);

                        }

                    }

                }

            }
        }

        return $result;
    }

    static public function getZone($args = []) {

        $model = get_model()->settable('wcmc_shipping_zones');

        if (is_numeric($args)) $args = ['where' => ['id' => (int)$args]];

        if (!have_posts($args)) $args = [];

        $args = array_merge(['where' => [], 'params' => []], $args);

        $where = $args['where'];

        $params = $args['params'];

        $zone = $model->get_where($where, $params);

        if (have_posts($zone)) {

            $zone->locations = static::getsZoneLocations(['where' => array('zone_id' => $zone->id)]);
        }

        return $zone;
    }

    static public function getsZoneLocations($args = []) {

        $model = get_model()->settable('wcmc_shipping_zone_locations');

        if (is_numeric($args)) $args = ['where' => ['id' => (int)$args]];

        if (!have_posts($args)) $args = [];

        $args = array_merge(['where' => [], 'params' => []], $args);

        $where = $args['where'];

        $params = $args['params'];

        return $model->gets_where($where, $params);

    }

    static public function listService($package, $order) {

        if (!empty($order->other_delivery_address)) {

            $citi = $order->shipping_city;

            $districts = $order->shipping_districts;

        } else {

            $citi = $order->billing_city;

            $districts = $order->billing_districts;

        }


        $item[0] = ['label' => $package['label']];

        $item[0]['expected_delivery_time'] = 'N/A';

        $item[0]['fee'] = Shipping::calculate([

            'billing_city' => $citi,

            'billing_districts' => $districts,

        ]);

        return $item;

    }

    static public function calculate($package) {

        if (empty($package['billing_city'])) return false;

        $city = $package['billing_city'];

        $districts = !empty($package['billing_districts']) ? $package['billing_districts'] : '';

        $show_form_shipping = !empty($package['show-form-shipping']) ? $package['show-form-shipping'] : '';

        if ($show_form_shipping == 'on') {

            if (empty($package['shipping_city'])) return false;

            $city = $package['shipping_city'];

            $districts = !empty($package['shipping_districts']) ? $package['shipping_districts'] : '';
        }

        $zone = [];

        $citi_location = static::getZoneLocations(['where' => array('location_type' => $city)]);

        if (have_posts($citi_location)) {

            $zone = static::getZone($citi_location->zone_id);
        } else {
            $zones = static::getsZone();

            foreach ($zones as $value) {

                if (!have_posts($value->locations)) {
                    $zone = $value;
                    break;
                }
            }
        }

        if (have_posts($zone)) {

            $total = Scart::total();

            $shipping = option::get('cart_shipping', [])[SHIP_KEY];

            if ($zone->zone_type == 'zone_type_free') return 0;

            if ($zone->zone_type == 'zone_type_flat') {

                if (!empty($shipping['range_price']) && $shipping['range_price'] == 'range_price' && !empty($zone->range_zone_price)) {

                    $range = unserialize($zone->range_zone_price);

                    foreach ($range as $item) {

                        if ($item['min'] <= $total && $item['max'] >= $total) return $item['value'];
                    }
                }

                return $zone->zone_price;
            }

            if ($zone->zone_type == 'zone_type_district') {

                if (!empty($districts)) {

                    $district = [];

                    $district_location = static::getsDistrictsLocations(['where' => ['locations_type' => $city, 'locations_code' => $districts]]);

                    if (have_posts($district_location)) {
                        if (!empty($shipping['range_price']) && $shipping['range_price'] == 'range_price') {
                            foreach ($district_location as $item) {
                                $district = static::getDistricts($item->districts_id);
                                if ($district->districts_price_min <= $total && ($district->districts_price_max >= $total || $district->districts_price_max == 0)) {
                                    return $district->districts_price;
                                }
                            }

                            $range = unserialize($zone->range_zone_price);

                            foreach ($range as $item) {
                                if ($item['min'] <= $total && $item['max'] >= $total) return $item['value'];
                            }

                        } else {

                            $district = static::getDistricts($district_location[0]->districts_id);

                            if(have_posts($district)) return $district->districts_price;
                        }
                    }
                }

                return $zone->zone_price;
            }
        }

        return false;
    }

    static public function getZoneLocations($args = []) {

        $model = get_model()->settable('wcmc_shipping_zone_locations');

        if (is_numeric($args)) $args = ['where' => ['id' => (int)$args]];

        if (!have_posts($args)) $args = [];

        $args = array_merge(['where' => [], 'params' => []], $args);

        $where = $args['where'];

        $params = $args['params'];

        return $model->get_where($where, $params);
    }

    static public function getsZone($args = []) {

        $model = get_model()->settable('wcmc_shipping_zones');

        if (is_numeric($args)) $args = ['where' => ['id' => (int)$args]];

        if (!have_posts($args)) $args = [];

        $args = array_merge(['where' => [], 'params' => []], $args);

        $where = $args['where'];

        $params = $args['params'];

        $zone = $model->gets_where($where, $params);

        if (have_posts($zone)) {

            foreach ($zone as $key => $value) {

                $zone[$key]->locations = static::getsZoneLocations(['where' => array('zone_id' => $value->id)]);
            }
        }

        return $zone;
    }

    static public function getsDistrictsLocations($args = []) {

        $model = get_model()->settable('wcmc_shipping_districts_locations');

        if (is_numeric($args)) $args = ['where' => ['id' => (int)$args]];

        if (!have_posts($args)) $args = [];

        $args = array_merge(['where' => [], 'params' => []], $args);

        $where = $args['where'];

        $params = $args['params'];

        return $model->gets_where($where, $params);
    }

    static public function getDistricts($args = []) {

        $model = get_model()->settable('wcmc_shipping_districts');

        if (is_numeric($args)) $args = ['where' => ['id' => (int)$args]];

        if (!have_posts($args)) $args = [];

        $args = array_merge(['where' => [], 'params' => []], $args);

        $where = $args['where'];

        $params = $args['params'];

        $zone = $model->get_where($where, $params);

        if (have_posts($zone)) {

            $zone->locations = static::getsDistrictsLocations(array('where' => array('districts_id' => $zone->id)));
        }

        return $zone;
    }

    static public function change($package, $order) {

        if (!empty($order->other_delivery_address)) {

            $citi = $order->shipping_city;

            $districts = $order->shipping_districts;

        } else {

            $citi = $order->billing_city;

            $districts = $order->billing_districts;
        }

        $fee = Shipping::calculate(['billing_city' => $citi, 'billing_districts' => $districts,]);

        Order::updateMeta($order->id, '_shipping_type', SHIP_KEY);

        Order::updateMeta($order->id, '_shipping_price', $fee);

        Order::updateMeta($order->id, '_shipping_label', $package['label']);

        return true;
    }

    static public function getZoneType($id = '') {

        $zone_type = [
            'zone_type_free' => 'Giao hàng miễn phí',
            'zone_type_flat' => 'Đồng giá',
            'zone_type_district' => 'Tính phí theo quận huyện'
        ];

        if (!empty($id) && !empty($zone_type[$id])) return apply_filters('shipping_zone_type_label', $zone_type[$id], $id);

        return $zone_type;
    }

    static public function getsDistricts($args = []) {

        $model = get_model()->settable('wcmc_shipping_districts');

        if (is_numeric($args)) $args = ['where' => ['id' => (int)$args]];

        if (!have_posts($args)) $args = [];

        $args = array_merge(['where' => [], 'params' => []], $args);

        $where = $args['where'];

        $params = $args['params'];

        $zone = $model->gets_where($where, $params);

        if (have_posts($zone)) {

            foreach ($zone as $key => $value) {

                $zone[$key]->locations = static::getsDistrictsLocations(array('where' => array('districts_id' => $value->id)));
            }
        }

        return $zone;
    }

    static public function getDistrictsLocations($args = []) {

        $model = get_model()->settable('wcmc_shipping_districts_locations');

        if (is_numeric($args)) $args = ['where' => ['id' => (int)$args]];

        if (!have_posts($args)) $args = [];

        $args = array_merge(['where' => [], 'params' => []], $args);

        $where = $args['where'];

        $params = $args['params'];

        return $model->get_where($where, $params);
    }

    public function active() {

        $model = get_model('plugins');
        /**
         * ADD OPTION CẤU HÌNH MẶC ĐỊNH
         */
        $model->query("CREATE TABLE IF NOT EXISTS `cle_wcmc_shipping_zones` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `zone_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `order` int(11) NOT NULL DEFAULT '0',
            `public` int(11) NOT NULL DEFAULT '1',
            `created` datetime DEFAULT NULL,
            `updated` datetime DEFAULT NULL,
            `zone_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `zone_price` int(11) NOT NULL DEFAULT '0',
            `range_zone_price` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
        ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $model->query("CREATE TABLE IF NOT EXISTS `cle_wcmc_shipping_zone_locations` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `zone_id` int(11) NOT NULL,
            `location_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `location_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `order` int(11) NOT NULL DEFAULT '0',
            `created` datetime DEFAULT NULL,
            `updated` datetime DEFAULT NULL
        ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $model->query("CREATE TABLE IF NOT EXISTS `cle_wcmc_shipping_districts` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `zone_id` int(11) NOT NULL DEFAULT '0',
            `districts_price_min` int(11) NOT NULL DEFAULT '0',
            `districts_price_max` int(11) NOT NULL DEFAULT '0',
            `districts_price` int(11) NOT NULL DEFAULT '0',
            `order` int(11) NOT NULL DEFAULT '0',
            `created` datetime DEFAULT NULL,
            `updated` datetime DEFAULT NULL
        ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $model->query("CREATE TABLE IF NOT EXISTS `cle_wcmc_shipping_districts_locations` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `districts_id` int(11) NOT NULL DEFAULT '0',
            `locations_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `locations_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `order` int(11) NOT NULL DEFAULT '0',
            `created` datetime DEFAULT NULL,
            `updated` datetime DEFAULT NULL
        ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function uninstall() {
        $model = get_model('plugins');
        $model->query("DROP TABLE IF EXISTS `cle_wcmc_shipping_zones`");
        $model->query("DROP TABLE IF EXISTS `cle_wcmc_shipping_zone_locations`");
        $model->query("DROP TABLE IF EXISTS `cle_wcmc_shipping_districts`");
        $model->query("DROP TABLE IF EXISTS `cle_wcmc_shipping_districts_locations`");
    }
}


include_once 'shipping-admin.php';
include_once 'shipping-ajax.php';
include_once 'shipping-update.php';