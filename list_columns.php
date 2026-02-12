<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

$columns = Schema::getColumnListing('empleados');
foreach ($columns as $col) {
    echo $col . PHP_EOL;
}
