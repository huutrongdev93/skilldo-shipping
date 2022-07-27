<?php
class ShippingZones extends Model {
    static string $table = 'shipping_zones';
    static public function get($args = []) {
        $zone = parent::get($args);
        if (have_posts($zone)) {
            $zone->locations = ShippingZonesLocation::gets(Qr::set('zone_id', $zone->id));
        }
        return $zone;
    }
    static public function gets($args = []) {
        $zone = parent::gets($args);
        if (have_posts($zone)) {
            foreach ($zone as $value) {
                $value->locations = ShippingZonesLocation::gets(Qr::set('zone_id', $value->id));
            }
        }
        return $zone;
    }
    static public function type($id = '') {

        $zone_type = [
            'zone_type_free' => 'Giao hàng miễn phí',
            'zone_type_flat' => 'Đồng giá',
            'zone_type_district' => 'Tính phí theo quận huyện'
        ];

        if (!empty($id) && !empty($zone_type[$id])) return apply_filters('shipping_zone_type_label', $zone_type[$id], $id);

        return $zone_type;
    }
    static public function insert($insertData = []) {

        $columnsTable = [
            'zone_name'         => ['string'],
            'zone_type'         => ['string'],
            'zone_price'        => ['price', 0],
            'range_zone_price'  => ['string'],
            'order'             => ['int', 0],
        ];

        $columnsTable = apply_filters('columns_db_'.self::$table, $columnsTable);

        $update = false;

        if (!empty($insertData['id'])) {

            $id             = (int) $insertData['id'];

            $update        = true;

            $oldObject = static::get($id);

            if (!$oldObject) return new SKD_Error('invalid_id', __('ID zones không chính xác.'));
        }

        $insertData = createdDataInsert($columnsTable, $insertData, (isset($oldObject)) ? $oldObject : null);

        foreach ($columnsTable as $columnsKey => $columnsValue ) {
            ${$columnsKey}  = $insertData[$columnsKey];
        }

        $data = compact(array_keys($columnsTable));

        $data = apply_filters('pre_insert_'.static::$table.'_data', $data, $insertData, $update ? $oldObject : null);

        $model = model(self::$table);

        if ($update) {
            $data['updated'] = gmdate('Y-m-d H:i:s', time() + 7*3600);
            $model->update( $data, Qr::set($id));
        }
        else {
            $data['created'] = gmdate('Y-m-d H:i:s', time() + 7*3600);
            $id = $model->add( $data );
        }

        return apply_filters('after_insert_'.self::$table, $id, $insertData, $data, $update ? $oldObject : null);
    }
    static public function delete($id) {

        $zone    = ShippingZones::get($id);

        if(have_posts($zone)) {

            $model = model(static::$table);

            if($model->delete(Qr::set($id))) {

                $args = Qr::set('zone_id', $id);
                //delete zone_locations
                $model->settable('shipping_zones_locations')->delete(clone $args);

                //delete districts
                $model->settable('shipping_districts');

                $districts = $model->gets(clone $args);

                $model->settable('shipping_districts_locations');

                foreach ($districts as $district) {
                    $model->delete(Qr::set('districts_id', $district->id));
                }

                $model->settable('shipping_districts')->delete(clone $args);

                return [$id];
            }
        }

        return false;
    }
}

class ShippingZonesLocation extends Model {
    static string $table = 'shipping_zones_locations';
    static public function insert($insertData = []) {

        $columnsTable = [
            'zone_id'         => ['int', 0],
            'location_code'   => ['string'],
            'location_type'   => ['string'],
        ];

        $columnsTable = apply_filters('columns_db_'.self::$table, $columnsTable);

        $update = false;

        if (!empty($insertData['id'])) {

            $id             = (int) $insertData['id'];

            $update        = true;

            $oldObject = static::get($id);

            if (!$oldObject) return new SKD_Error('invalid_id', __('ID zones không chính xác.'));
        }

        $insertData = createdDataInsert($columnsTable, $insertData, (isset($oldObject)) ? $oldObject : null);

        foreach ($columnsTable as $columnsKey => $columnsValue ) {
            ${$columnsKey}  = $insertData[$columnsKey];
        }

        $data = compact(array_keys($columnsTable));

        $data = apply_filters('pre_insert_'.static::$table.'_data', $data, $insertData, $update ? $oldObject : null);

        $model = model(self::$table);

        if ($update) {
            $data['updated'] = gmdate('Y-m-d H:i:s', time() + 7*3600);
            $model->update( $data, Qr::set($id));
        }
        else {
            $data['created'] = gmdate('Y-m-d H:i:s', time() + 7*3600);
            $id = $model->add($data);
        }

        return apply_filters('after_insert_'.self::$table, $id, $insertData, $data, $update ? $oldObject : null);
    }
}

class ShippingDistricts extends Model {
    static string $table = 'shipping_districts';
    static public function get($args = []) {
        $zone = parent::get($args);
        if (have_posts($zone)) {
            $zone->locations = ShippingDistrictsLocation::gets(Qr::set('districts_id', $zone->id));
        }
        return $zone;
    }
    static public function gets($args = []) {
        $zone = parent::gets($args);
        if (have_posts($zone)) {
            foreach ($zone as $value) {
                $value->locations = ShippingDistrictsLocation::gets(Qr::set('districts_id', $value->id));
            }
        }
        return $zone;
    }
    static public function insert($insertData = []) {

        $columnsTable = [
            'zone_id'               => ['int', 0],
            'districts_price_min'   => ['int', 0],
            'districts_price_max'   => ['int', 0],
            'districts_price'       => ['int', 0],
            'order'                 => ['int', 0],
        ];

        $columnsTable = apply_filters('columns_db_'.self::$table, $columnsTable);

        $update = false;

        if (!empty($insertData['id'])) {

            $id             = (int) $insertData['id'];

            $update        = true;

            $oldObject = static::get($id);

            if (!$oldObject) return new SKD_Error('invalid_id', __('ID zones không chính xác.'));
        }

        $insertData = createdDataInsert($columnsTable, $insertData, (isset($oldObject)) ? $oldObject : null);

        foreach ($columnsTable as $columnsKey => $columnsValue ) {
            ${$columnsKey}  = $insertData[$columnsKey];
        }

        $data = compact(array_keys($columnsTable));

        $data = apply_filters('pre_insert_'.static::$table.'_data', $data, $insertData, $update ? $oldObject : null);

        $model = model(self::$table);

        if ($update) {
            $data['updated'] = gmdate('Y-m-d H:i:s', time() + 7*3600);
            $model->update( $data, Qr::set($id));
        }
        else {
            $data['created'] = gmdate('Y-m-d H:i:s', time() + 7*3600);
            $id = $model->add( $data );
        }

        return apply_filters('after_insert_'.self::$table, $id, $insertData, $data, $update ? $oldObject : null);
    }
}

class ShippingDistrictsLocation extends Model {
    static string $table = 'shipping_districts_locations';
    static public function insert($insertData = []) {

        $columnsTable = [
            'districts_id'    => ['int', 0],
            'location_code'   => ['string'],
            'location_type'   => ['string'],
        ];

        $columnsTable = apply_filters('columns_db_'.self::$table, $columnsTable);

        $update = false;

        if (!empty($insertData['id'])) {

            $id             = (int) $insertData['id'];

            $update        = true;

            $oldObject = static::get($id);

            if (!$oldObject) return new SKD_Error('invalid_id', __('ID zones không chính xác.'));
        }

        $insertData = createdDataInsert($columnsTable, $insertData, (isset($oldObject)) ? $oldObject : null);

        foreach ($columnsTable as $columnsKey => $columnsValue ) {
            ${$columnsKey}  = $insertData[$columnsKey];
        }

        $data = compact(array_keys($columnsTable));

        $data = apply_filters('pre_insert_'.static::$table.'_data', $data, $insertData, $update ? $oldObject : null);

        $model = model(self::$table);

        if ($update) {
            $data['updated'] = gmdate('Y-m-d H:i:s', time() + 7*3600);
            $model->update( $data, Qr::set($id));
        }
        else {
            $data['created'] = gmdate('Y-m-d H:i:s', time() + 7*3600);
            $id = $model->add($data);
        }

        return apply_filters('after_insert_'.self::$table, $id, $insertData, $data, $update ? $oldObject : null);
    }
}