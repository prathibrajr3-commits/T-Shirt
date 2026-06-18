<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$setting = App\Models\SiteSetting::first();

if ($setting) {
    echo "Current store_name: " . $setting->store_name . PHP_EOL;
} else {
    echo "No SiteSetting record found." . PHP_EOL;
}