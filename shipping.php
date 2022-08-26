<?php
/**
 * Plugin name     : Shipping
 * Plugin class    : shipping
 * Plugin uri      : http://sikido.vn
 * Description     : Plugin tính phí vận chuyển theo quận huyện
 * Author          : SKDSoftware Dev Team
 * Version         : 2.1.1
 */
const SHIP_NAME = 'shipping';

const SHIP_FOLDER = 'shipping';

const SHIP_KEY = 'zone';

const SHIP_VERSION = '2.1.1';

define('SHIP_PATH', Path::plugin(SHIP_FOLDER));

class shipping {

    private string $name = 'shipping';

    function __construct() {}

    static public function config($result) {

        $shipping_key = Request::post('shipping_key');

        $zone_id = (int)Request::post('zone_id');

        if (empty($zone_id)) {

            $data = Request::post();

            $shipping = option::get('cart_shipping', []);

            if (!have_posts($shipping)) $shipping = [];

            $shipping[$shipping_key]['enabled'] = (!empty($data['enabled'])) ? Str::clear($data['enabled']) : false;

            $shipping[$shipping_key]['range_price'] = (!empty($data['range_price'])) ? Str::clear($data['range_price']) : false;

            $shipping[$shipping_key]['title'] = Request::post('title');

            $shipping[$shipping_key]['img'] = FileHandler::handlingUrl(Request::post('img'));

            $shipping[$shipping_key]['price_default'] = Str::clear($data['price_default']);

            if (have_posts($shipping)) {

                Option::update('cart_shipping', $shipping);
            }
        } else {

            $zone = ShippingZones::get($zone_id);

            if (have_posts($zone)) {

                $model = model('shipping_zones');

                $data = [];

                $data['zone_name'] = Request::post('zone_name');

                $data['zone_type'] = Request::post('zone_type');

                $data['zone_price'] = (int)Request::post('zone_price');

                $data['range_zone_price'] = (!empty(Request::post('range_zone_price'))) ? serialize(Request::post('range_zone_price')) : false;

                if ($model->update($data, Qr::set($zone_id))) {

                    $locations = Request::post('zone_locations');

                    $model->settable('shipping_zone_locations');

                    foreach ($zone->locations as $location) {

                        if (in_array($location->location_type, $locations) === false) {

                            $model->delete(Qr::set('zone_id', $zone_id)->where('location_type', $location->location_type));

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

    static public function listService($package, $order): array {

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

        $citi_location = ShippingZonesLocation::get(Qr::set('location_type', $city));

        if (have_posts($citi_location)) {

            $zone = ShippingZones::get($citi_location->zone_id);
        } else {
            $zones = ShippingZones::gets();

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

                    $district_location = ShippingDistrictsLocation::gets(Qr::set('location_type', $city)->where('location_code', $districts));

                    if (have_posts($district_location)) {
                        if (!empty($shipping['range_price']) && $shipping['range_price'] == 'range_price') {
                            foreach ($district_location as $item) {
                                $district = ShippingDistricts::get($item->districts_id);
                                if ($district->districts_price_min <= $total && ($district->districts_price_max >= $total || $district->districts_price_max == 0)) {
                                    return $district->districts_price;
                                }
                            }

                            $range = unserialize($zone->range_zone_price);

                            foreach ($range as $item) {
                                if ($item['min'] <= $total && $item['max'] >= $total) return $item['value'];
                            }

                        } else {

                            $district = ShippingDistricts::get($district_location[0]->districts_id);

                            if(have_posts($district)) return $district->districts_price;
                        }
                    }
                }

                return $zone->zone_price;
            }
        }

        return false;
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

    public function active() {
        $model = model();
        if(!$model::schema()->hasTable('shipping_zones')) {
            $model::schema()->create('shipping_zones', function ($table) {
                $table->increments('id');
                $table->string('zone_name', 255)->collate('utf8mb4_unicode_ci')->nullable();
                $table->string('zone_type', 200)->collate('utf8mb4_unicode_ci');
                $table->integer('zone_price')->default(0);
                $table->string('range_zone_price', 200)->collate('utf8mb4_unicode_ci')->nullable();
                $table->tinyInteger('public')->default(1);
                $table->integer('order')->default(0);
                $table->dateTime('created');
                $table->dateTime('updated')->nullable();
            });
        }
        if(!$model::schema()->hasTable('shipping_zones_locations')) {
            $model::schema()->create('shipping_zones_locations', function ($table) {
                $table->increments('id');
                $table->integer('zone_id')->default(0);
                $table->string('location_code', 255)->collate('utf8mb4_unicode_ci')->nullable();
                $table->string('location_type', 200)->collate('utf8mb4_unicode_ci');
                $table->integer('order')->default(0);
                $table->dateTime('created');
                $table->dateTime('updated')->nullable();
            });
        }
        if(!$model::schema()->hasTable('shipping_districts')) {
            $model::schema()->create('shipping_districts', function ($table) {
                $table->increments('id');
                $table->integer('zone_id')->default(0);
                $table->integer('districts_price_min')->default(0);
                $table->integer('districts_price_max')->default(0);
                $table->integer('districts_price')->default(0);
                $table->integer('order')->default(0);
                $table->dateTime('created');
                $table->dateTime('updated')->nullable();
            });
        }
        if(!$model::schema()->hasTable('shipping_districts_locations')) {
            $model::schema()->create('shipping_districts_locations', function ($table) {
                $table->increments('id');
                $table->integer('districts_id')->default(0);
                $table->string('location_code', 255)->collate('utf8mb4_unicode_ci')->nullable();
                $table->string('location_type', 200)->collate('utf8mb4_unicode_ci');
                $table->integer('order')->default(0);
                $table->dateTime('created');
                $table->dateTime('updated')->nullable();
            });
        }
        Option::update('shipping_version', SHIP_VERSION);
    }

    public function uninstall(): void {
        $model = model();
        $model::schema()->drop('shipping_zones');
        $model::schema()->drop('shipping_zones_locations');
        $model::schema()->drop('shipping_districts');
        $model::schema()->drop('shipping_districts_locations');
    }
}


include_once 'shipping-admin.php';
include_once 'shipping-ajax.php';
include_once 'shipping-function.php';
include_once 'shipping-update.php';