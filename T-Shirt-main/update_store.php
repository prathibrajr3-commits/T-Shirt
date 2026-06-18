<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
App\Models\SiteSetting::first()->update(['store_name'=>'gold','admin_header_title'=>'goldADMIN']);
echo "Store settings updated\n";
?>
