<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$routes = \Illuminate\Support\Facades\Route::getRoutes();

foreach ($routes as $r) {
    if (in_array('GET', $r->methods()) && !str_starts_with($r->uri(), 'admin') && !str_starts_with($r->uri(), 'api')) {
        echo sprintf("%-40s %-30s %s\n", $r->uri(), $r->getName() ?? '', substr($r->getActionName(), 0, 45));
    }
}
