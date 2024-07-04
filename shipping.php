<?php
/**
 * Plugin name     : Shipping
 * Plugin class    : shipping
 * Plugin uri      : http://sikido.vn
 * Description     : Plugin tính phí vận chuyển theo quận huyện
 * Author          : SKDSoftware Dev Team
 * Version         : 3.1.0
 */
const SHIP_NAME = 'shipping';

const SHIP_FOLDER = 'shipping';

const SHIP_KEY = 'zone';

const SHIP_VERSION = '3.1.0';

define('SHIP_PATH', Path::plugin(SHIP_FOLDER));

class Shipping {

    private string $name = 'shipping';

    function __construct() {}

    public function active(): void
    {
        if(!schema()->hasTable('shipping_fee')) {
            schema()->create('shipping_fee', function ($table) {
                $table->increments('id');
                $table->string('name', 255)->collate('utf8mb4_unicode_ci')->nullable();
                $table->string('type', 200)->collate('utf8mb4_unicode_ci')->default('price');
                $table->text('range')->nullable();
                $table->integer('fee')->default(0);
                $table->tinyInteger('default')->default(0);
                $table->dateTime('created')->default('CURRENT_TIMESTAMP');
                $table->dateTime('updated')->nullable();
            });
        }

        if(!schema()->hasTable('shipping_zones')) {
            schema()->create('shipping_zones', function ($table) {
                $table->increments('id');
                $table->string('name', 255)->collate('utf8mb4_unicode_ci')->nullable();
                $table->integer('feeId')->default(0);
                $table->string('city', 200)->collate('utf8mb4_unicode_ci');
                $table->text('districts')->collate('utf8mb4_unicode_ci')->nullable();
                $table->tinyInteger('districtOption')->default(1);
                $table->dateTime('created')->default('CURRENT_TIMESTAMP');
                $table->dateTime('updated')->nullable();
            });
        }

        Option::update('shipping_version', SHIP_VERSION);
    }

    public function uninstall(): void {
        schema()->drop('shipping_fee');
        schema()->drop('shipping_zones');
    }

    static function adminAssets(): void
    {
        $asset = SHIP_PATH.'/assets/';

        if(Admin::is()) {

            Admin::asset()->location('header')->add('shipping', $asset.'css/style.admin.css');

            Admin::asset()->location('footer')->add('shipping', $asset.'script/script.admin.js');
        }
    }
}

include_once 'shipping-function.php';

include_once 'shipping-zone.php';

include_once 'shipping-admin.php';

include_once 'shipping-ajax.php';

include_once 'shipping-checkout.php';

add_filter('shipping_gateways', 'ShippingZoneHandler::register', 1);

add_action('admin_init','Shipping::adminAssets', 100);