<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$products = App\Models\Product::all();
foreach ($products as $p) {
    echo "ID: " . $p->id . " | Name: " . $p->name . " | Price: " . $p->price . " | Discount: " . $p->discount_price . " | Slug: " . $p->slug . "\n";
}
