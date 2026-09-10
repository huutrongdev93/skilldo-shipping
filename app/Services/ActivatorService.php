<?php
namespace Shipping\Services;

use SkillDo\Support\Path;

class ActivatorService
{
    static function active(): void
    {
        $db = include Path::plugin('shipping/database/database.php');
        $db->up();
    }
}
