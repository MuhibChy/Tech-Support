<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "Services slugs:\n";
foreach (\App\Models\Service::where('is_active', true)->pluck('slug') as $s) {
    echo "  - $s\n";
}

echo "Blog slugs:\n";
foreach (\App\Models\BlogPost::published()->pluck('slug') as $b) {
    echo "  - $b\n";
}

echo "KB slugs:\n";
foreach (\App\Models\KbArticle::where('is_published', true)->pluck('slug') as $k) {
    echo "  - $k\n";
}
