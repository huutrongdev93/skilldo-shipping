<?php
Class ShippingCheckout {
    static function feeReview(): void
    {
        $checkout       = Cms::getData('checkout');
        $shippingPrice  = (isset($checkout['shipping_price_zone'])) ? $checkout['shipping_price_zone'] : false;
        if($shippingPrice !== false) {

            if($shippingPrice === 0) {
                $shippingPrice = 'Miễn phí';
            }

            if(!empty($shippingPrice) && is_numeric($shippingPrice)) {
                $shippingPrice = Prd::price($shippingPrice);
            }

            Plugin::view(SHIP_NAME, 'checkout/review', [
                'shippingPrice' => $shippingPrice,
            ]);
        }

    }
}

add_action('checkout_review_order', 'ShippingCheckout::feeReview', 50);
