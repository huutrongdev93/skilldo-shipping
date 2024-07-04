<?php
if(!Admin::is()) return;
function Shipping_update_core(): void
{
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
    public function runUpdate($shippingVersion): void
    {
        $listVersion    = ['2.0.0', '2.1.0', '3.0.0', '3.1.0'];
        $model          = model();
        foreach ($listVersion as $version ) {
            if(version_compare( $version, $shippingVersion ) == 1) {
                $function = 'update_Version_'.str_replace('.','_',$version);
                if(method_exists($this, $function)) $this->$function($model);
            }
        }
        Option::update('shipping_version', SHIP_VERSION );
    }
    public function update_Version_2_0_0($model): void
    {
        Shipping_Update_Database::Version_2_0_0($model);
    }
    public function update_Version_2_1_0($model): void
    {
        Shipping_Update_Database::Version_2_1_0($model);
    }
    public function update_Version_3_0_0($model): void
    {
        Shipping_Update_Database::Version_3_0_0($model);
    }
    public function update_Version_3_1_0($model): void
    {
        Shipping_Update_Database::Version_3_1_0($model);
    }
}
Class Shipping_Update_Database {
    public static function Version_2_0_0($model): void
    {
        if(!schema()->hasColumn('wcmc_shipping_zones', 'range_zone_price')) {
            schema()->table('wcmc_shipping_zones', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('range_zone_price', 255)->nullable()->after('zone_price');
            });
        }
        if(!schema()->hasColumn('wcmc_shipping_districts', 'districts_price_min')) {
             schema()->table('wcmc_shipping_districts', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->integer('districts_price_min')->default(0)->after('zone_id');
                $table->integer('districts_price_max')->default(0)->after('zone_id');
            });
        }
    }
    public static function Version_2_1_0($model): void
    {
        if(schema()->hasTable('wcmc_shipping_zones')) {
            schema()->rename('wcmc_shipping_zones', 'shipping_zones');
        }
        if(schema()->hasTable('wcmc_shipping_zone_locations')) {
            schema()->rename('wcmc_shipping_zone_locations', 'shipping_zones_locations');
        }
        if(schema()->hasTable('wcmc_shipping_districts')) {
            schema()->rename('wcmc_shipping_districts', 'shipping_districts');
        }
        if(schema()->hasTable('wcmc_shipping_districts_locations')) {
            schema()->rename('wcmc_shipping_districts_locations', 'shipping_districts_locations');
        }
    }
    public static function Version_3_0_0($model): void
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

        schema()->table('shipping_zones', function ($table) {
            $table->renameColumn('zone_name', 'name');
            $table->renameColumn('zone_price', 'feeId');
            $table->renameColumn('zone_type', 'city');
            $table->renameColumn('range_zone_price', 'districts');
            $table->renameColumn('public', 'districtOption');
            $table->text('districts')->collate('utf8mb4_unicode_ci')->change();
        });

        schema()->drop('shipping_zones_locations');
        schema()->drop('shipping_districts');
        schema()->drop('shipping_districts_locations');
    }
    public static function Version_3_1_0($model): void
    {
        if(!schema()->hasTable('shipping_fee')) {
            schema()->create('shipping_fee', function ($table) {
                $table->dateTime('created')->default('CURRENT_TIMESTAMP')->change();
            });
        }
        if(!schema()->hasTable('shipping_zones')) {
            schema()->create('shipping_zones', function ($table) {
                $table->dateTime('created')->default('CURRENT_TIMESTAMP')->change();
            });
        }
    }
}