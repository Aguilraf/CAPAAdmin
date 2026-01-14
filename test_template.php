<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/empleados/plantilla', 'GET')
);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Headers: " . json_encode($response->headers->all(), JSON_PRETTY_PRINT) . "\n";

if ($response->getStatusCode() !== 200) {
    echo "Content: " . $response->getContent() . "\n";
}

$kernel->terminate($request, $response);
