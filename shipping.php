<?php

use SkillDo\Support\Path;

const SHIP_NAME = 'shipping';

const SHIP_FOLDER = 'shipping';

const SHIP_KEY = 'zone';

const SHIP_VERSION = '3.2.0';

class Shipping {

    private string $name = 'shipping';

    function __construct() {}

    public function active(): void
    {
        include_once Path::plugin('shipping/app/Services/ActivatorService.php');

        \Shipping\Services\ActivatorService::active();
    }

    public function uninstall(): void
    {
        include_once Path::plugin('shipping/app/Services/DeactivatorService.php');

        \Shipping\Services\DeactivatorService::uninstall();
    }
}
