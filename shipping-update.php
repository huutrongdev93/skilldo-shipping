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
        $listVersion    = ['2.0.0', '2.1.0'];
        $model          = model();
        foreach ($listVersion as $version ) {
            if(version_compare( $version, $shippingVersion ) == 1) {
                $function = 'update_Version_'.str_replace('.','_',$version);
                if(method_exists($this, $function)) $this->$function($model);
            }
        }
        Option::update('shipping_version', SHIP_VERSION );
    }
    public function update_Version_2_0_0($model) {
        //Shipping_Update_Database::Version_2_0_0($model);
    }
    public function update_Version_2_1_0($model) {
        Shipping_Update_Database::Version_2_1_0($model);
    }
}
Class Shipping_Update_Database {
    public static function Version_2_0_0($model) {
        if(!$model::schema()->hasColumn('wcmc_shipping_zones', 'range_zone_price')) {
            get_model()->query("ALTER TABLE `".CLE_PREFIX."wcmc_shipping_zones` ADD `range_zone_price` VARCHAR(255) NULL AFTER `zone_price`");
        }
        if(!$model::schema()->hasColumn('wcmc_shipping_districts', 'districts_price_min')) {
            get_model()->query("ALTER TABLE `".CLE_PREFIX."wcmc_shipping_districts` ADD `districts_price_min` int(11) NOT NULL DEFAULT '0' AFTER `zone_id`");
            get_model()->query("ALTER TABLE `".CLE_PREFIX."wcmc_shipping_districts` ADD `districts_price_max` int(11) NOT NULL DEFAULT '0' AFTER `zone_id`");
        }
    }
    public static function Version_2_1_0($model) {
        if($model::schema()->hasTable('wcmc_shipping_zones')) {
            $model::schema()->rename('wcmc_shipping_zones', 'shipping_zones');
        }
        if($model::schema()->hasTable('wcmc_shipping_zone_locations')) {
            $model::schema()->rename('wcmc_shipping_zone_locations', 'shipping_zone_locations');
        }
        if($model::schema()->hasTable('wcmc_shipping_districts')) {
            $model::schema()->rename('wcmc_shipping_districts', 'shipping_districts');
        }
        if($model::schema()->hasTable('wcmc_shipping_districts_locations')) {
            $model::schema()->rename('wcmc_shipping_districts_locations', 'shipping_districts_locations');
        }
    }
}