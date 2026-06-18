<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$setting = App\Models\SiteSetting::first();
if (!$setting) {
    $setting = new App\Models\SiteSetting();
}
$setting->store_name = 'gold';
$setting->admin_header_title = 'goldADMIN';
$setting->save();
echo "Saved: " . $setting->store_name . "\n";
?>
