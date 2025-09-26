<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WasteItem;

$items = WasteItem::orderByDesc('id')->take(5)->get(['id', 'images']);
foreach ($items as $it) {
    echo 'ID '.$it->id.' images: '.json_encode($it->images).PHP_EOL;
}
