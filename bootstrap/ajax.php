<?php

use SkillDo\Cms\Support\Ajax;

Ajax::admin('Shipping\Ajax\Admin\ShippingAjax::locations');

Ajax::admin('Shipping\Ajax\Admin\ShippingFeeAjax::load');
Ajax::admin('Shipping\Ajax\Admin\ShippingFeeAjax::add');
Ajax::admin('Shipping\Ajax\Admin\ShippingFeeAjax::save');
Ajax::admin('Shipping\Ajax\Admin\ShippingFeeAjax::delete');

Ajax::admin('Shipping\Ajax\Admin\ShippingZoneAjax::load');
Ajax::admin('Shipping\Ajax\Admin\ShippingZoneAjax::add');
Ajax::admin('Shipping\Ajax\Admin\ShippingZoneAjax::save');
Ajax::admin('Shipping\Ajax\Admin\ShippingZoneAjax::delete');