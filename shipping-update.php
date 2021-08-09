<?php
if(!Admin::is()) return;
function Shipping_update_core() {
    if(Admin::is() && Auth::check() ) {
        $version = Option::get('shipping_version');
        $version = (empty($version)) ? '1.2.0' : $version;
        if (version_compare( SHIP_VERSION, $version ) === 1 ) {
            $update = new Shipping_Update_Version();
            $update->runUpdate($version);
        }
    }
}
add_action('admin_init', 'Shipping_update_core');
Class Shipping_Update_Version {
    public function runUpdate($shippingVersion) {
        $listVersion    = ['2.0.0'];
        $model          = get_model();
        foreach ($listVersion as $version ) {
            if(version_compare( $version, $shippingVersion ) == 1) {
                $function = 'update_Version_'.str_replace('.','_',$version);
                if(method_exists($this, $function)) $this->$function($model);
            }
        }
        Option::update('shipping_version', SHIP_VERSION );
    }
    public function update_Version_2_0_0($model) {
        Shipping_Update_Database::Version_2_0_0($model);
    }
}
Class Shipping_Update_Database {
    public static function Version_2_0_0($model) {
        if(!$model->db_field_exists('range_zone_price','wcmc_shipping_zones')) {
            $model->query("ALTER TABLE `".CLE_PREFIX."wcmc_shipping_zones` ADD `range_zone_price` VARCHAR(255) NULL AFTER `zone_price`");
        }
        if(!$model->db_field_exists('districts_price_min','wcmc_shipping_districts')) {
            $model->query("ALTER TABLE `".CLE_PREFIX."wcmc_shipping_districts` ADD `districts_price_min` int(11) NOT NULL DEFAULT '0' AFTER `zone_id`");
            $model->query("ALTER TABLE `".CLE_PREFIX."wcmc_shipping_districts` ADD `districts_price_max` int(11) NOT NULL DEFAULT '0' AFTER `zone_id`");
        }
    }
}