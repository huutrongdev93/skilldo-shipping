<?php
namespace Shipping\Services;

use SkillDo\Cms\Support\Admin;

class AdminService
{
    static function assets(): void
    {
        Admin::asset()->location('header')->add('shipping', 'plugins/shipping/assets/css/style.admin.css');
        Admin::asset()->location('footer')->add('shipping', 'plugins/shipping/assets/script/script.admin.js');
    }
}