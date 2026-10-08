<?php

use Shipping\Services\AdminService;

add_action('admin_assets', [AdminService::class, 'assets']);
/*
| Plugin không có bước migration khi cập nhật: bản 4.0.1 thêm cột shipping_zones.wards,
| site đang chạy bản cũ chỉ có cột khi up() chạy lại. up() chỉ thêm phần còn thiếu nên chạy lại an toàn.
*/
add_action('admin_init', function () {
    if (\Option::get('shipping_db_version') === '4.0.1') return;
    $db = include \SkillDo\Support\Path::plugin('shipping/database/database.php');
    $db->up();
    \Option::update('shipping_db_version', '4.0.1');
});
