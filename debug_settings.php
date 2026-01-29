<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $count = \App\Models\FirefighterSetting::count();
    echo "Total settings found: $count" . PHP_EOL;

    foreach (\App\Models\FirefighterSetting::all() as $s) {
        echo "KEY: [{$s->key}] VALUE: [{$s->value}]" . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
