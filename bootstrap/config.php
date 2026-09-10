<?php

use Shipping\Services\AdminService;

add_action('admin_assets', [AdminService::class, 'assets']);