<?php

namespace Shipping\Providers;

use Ecommerce\Gateway\Shipping\Common\ShippingManager;
use Shipping\Gateway\ShippingZone;
use Shipping\Gateway\ShippingZoneGateway;
use SkillDo\ServiceProvider;

class ShippingServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
    {
        ShippingManager::addGateway('zone', [
            'shipping' => ShippingZone::class,
            'gateway' => ShippingZoneGateway::class,
        ]);
    }
}
