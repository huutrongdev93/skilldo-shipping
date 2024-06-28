<?php
class ShippingZoneHandler {

    static function register($tabs) {

        $tabs[SHIP_KEY] 	= [
            'label'         => 'Giao hàng tận nơi',
            'description'   => 'Tự động thêm phí giao hàng theo từng loại khu vực để khách hàng lựa chọn và tự động tính nó vào hóa đơn.',
            'callback'      => 'ShippingAdmin::setting',
            'class'         => 'ShippingZoneHandler'
        ];

        return $tabs;
    }

    static function form($keyShipping, $shipping): void
    {
        $languages = Language::list();

        $languageDefault = Language::default();

        $tabs = [];

        $form = form();

        $form->none('<div class="row">');

        $form->radio('enabled', ['Tắt','Bật'], [
            'label' => 'Bật /Tắt hình thức vận chuyển này',
        ], (!empty($shipping['enabled'])) ? 1 : 0);

        $form->checkbox('default', SHIP_KEY, [
            'label' => 'Đặt làm phí ship mặc định',
        ], (!empty($shipping['default'])) ? SHIP_KEY : '');

        if(count($languages) === 1) {

            $form->image('img', [
                'label' => 'Icon',
                'start' => 4,
            ], $shipping['img']);

            $form->text('title', [
                'label' => 'Tiêu đề',
                'start' => 8,
            ], $shipping['title']);

            $form->textarea('description', [
                'label' => 'Mô tả',
            ], $shipping['description']);
        }
        else {

            $form->image('img', [
                'label' => 'Icon',
                'start' => 12,
            ], $shipping['img']);

            foreach ($languages as $languageKey => $language) {
                $tabs['shipping_'.$languageKey] = [
                    'label'   => $language['label'],
                    'content' => function () use ($languageKey, $languageDefault, $shipping) {

                        $name = ($languageDefault == $languageKey) ? '' : '_'.$languageKey;

                        $form = form();

                        $form->none('<div class="row">');

                        $form->text('title'.$name, [
                            'label' => 'Tiêu đề',
                            'start' => 8,
                        ], $shipping['title'.$name] ?? '');

                        $form->textarea('description'.$name, ['label' => 'Mô tả',], $shipping['description'.$name] ?? '');

                        $form->none('</div>');

                        $form->html(false);
                    }
                ];
            }
        }

        $form->none('</div>');

        $form = apply_filters('admin_payment_'.$keyShipping.'_input_fields', $form, $shipping);

        $form->html(false);

        if(!empty($tabs)) {
            echo Admin::tabs($tabs, 'shipping_'.Language::default());
        }
    }

    static function config(\SkillDo\Http\Request $request): void
    {
        $shipping_key = trim((string)$request->input('shipping_key'));

        $enabled = trim((string)$request->input('enabled'));

        $default = trim((string)$request->input('default'));

        $img     = trim((string)$request->input('img'));

        $title   = trim((string)$request->input('title'));

        $description   = trim((string)$request->input('description'));

        if(empty($title)) {
            response()->error('Không được để trống tên hình thức vận chuyển');
        }

        $shipping = Option::get('cart_shipping', []);

        if (!have_posts($shipping)) $shipping = [];

        $shipping[$shipping_key]['enabled'] = (!empty($enabled)) ? $enabled : false;

        $shipping[$shipping_key]['title'] = $title;

        $shipping[$shipping_key]['description'] = $description;

        $shipping[$shipping_key]['img'] = FileHandler::handlingUrl($img);


        if(Language::isMulti()) {

            $languages = Language::list();

            $languageDefault = Language::default();

            foreach ($languages as $languageKey => $language) {

                if($languageKey == $languageDefault) continue;

                $title   = trim((string)$request->input('title_'.$languageKey));

                $description   = trim((string)$request->input('description_'.$languageKey));

                $shipping[$shipping_key]['title_'.$languageKey] = $title;

                $shipping[$shipping_key]['description_'.$languageKey] = $description;
            }
        }

        Option::update('cart_shipping', $shipping);

        if(!empty($default) && $default == $shipping_key) {
            Option::update('cart_shipping_default', $default);
        }
    }

    static function listService($package, $order) {
    }

    static function calculate($package): bool|int
    {
        if (empty($package['billing_city'])) return false;

        $fee = false;

        $feeId = 0;

        $zone = ShippingZone::get(Qr::set('city', $package['billing_city']));

        if(have_posts($zone)) {
            if($zone->districtOption == 1) {
                $feeId = $zone->feeId;
            }
            else {
                if (!empty($package['billing_districts'])) {
                    foreach ($zone->districts as $item) {
                        if(in_array($package['billing_districts'], $item['districts']) !== false) {
                            $feeId = $item['fee'];
                            break;
                        }
                    }
                }

                if(empty($feeId)) {
                    $feeId = $zone->feeId;
                }
            }
        }

        if(empty($feeId)) {
            $zone = ShippingZone::get(Qr::set('city', 'all'));
            if(have_posts($zone)) {
                $feeId = $zone->feeId;
            }
        }

        if(!empty($feeId)) {
            $shipFee = ShippingFee::get($feeId);
        }
        else {
            $shipFee = ShippingFee::get(Qr::set('default', 1));
        }

        if(have_posts($shipFee)) {
            if($shipFee->type == 'price') {
                $role = (isset($package['total'])) ? $package['total'] :Scart::total();
            }
            else {
                $role = (isset($package['weight'])) ? $package['weight'] :Scart::totalWeight();
            }

            if(have_posts($shipFee->range)) {
                foreach ($shipFee->range as $item) {
                    if(!isset($item['min']) || !isset($item['max']) || !isset($item['fee'])) continue;
                    if($item['min'] == 0 && $item['max'] == 0) {
                        $fee = $item['fee'];
                        break;
                    }
                    if($item['min'] == 0 && $item['max'] >= $role) {
                        $fee = $item['fee'];
                        break;
                    }
                    if($item['max'] == 0 && $item['min'] <= $role) {
                        $fee = $item['fee'];
                        break;
                    }
                    if($item['min'] <= $role && $item['max'] >= $role) {
                        $fee = $item['fee'];
                        break;
                    }
                }
            }
        }

        return $fee;
    }

    static function change($shipping, $order): array
    {
        $citi      = $order->billing_city;

        $districts = $order->billing_districts;

        $total     = $order->total;

        if(isset($order->_shipping_price)) {
            $total = $order->total - $order->_shipping_price;
        }

        $weight = 0;

        foreach ($order->items as $item) {
            $weight += (int)OrderItem::getMeta($item->id, 'weight', true);
        }

        $weight = $weight/1000;

        $fee = ShippingZoneHandler::calculate([
            'billing_city'      => $citi,
            'billing_districts' => $districts,
            'weight'            => $weight,
            'total'             => $total
        ]);

        $orderMeta = [
            '_shipping_type'    => SHIP_KEY,
            '_shipping_price'   => $fee,
            '_shipping_label'   => $shipping['label'],
            '_shipping_info'    => [
                'pickId'    => '',
                'transport' => '',
                'weight'    => $weight,
                'isFreeShip'=> 0,
                'note'      => '',
            ],
        ];

        return [
            'order' => $order,
            'orderMeta' => $orderMeta
        ];
    }
}