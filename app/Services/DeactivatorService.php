<?php
namespace Shipping\Services;

use SkillDo\Cms\Support\Option;
use SkillDo\Support\Path;

Class DeactivatorService
{
    public static function uninstall(): void
    {
        $db = include Path::plugin('shipping/database/database.php');
        $db->down();
    }
}