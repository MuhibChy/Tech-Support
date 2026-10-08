<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$initialReq = Illuminate\Http\Request::create('/', 'GET');
$app->instance('request', $initialReq);

\Illuminate\Support\Facades\URL::forceRootUrl('https://muhibchy.github.io/Tech-Support');
\Illuminate\Support\Facades\URL::forceScheme('https');
config([
    'app.url' => 'https://muhibchy.github.io/Tech-Support',
    'app.asset_url' => 'https://muhibchy.github.io/Tech-Support',
]);

$start = microtime(true);
$services = \App\Models\Service::where('is_active', true)->limit(10)->get();
foreach ($services as $s) {
    $req = Illuminate\Http\Request::create('/services/' . $s->slug, 'GET');
    $app->instance('request', $req);
    $res = $kernel->handle($req);
}
$dur = microtime(true) - $start;
echo sprintf("10 services took: %.2f seconds (average %.3fs each)\n", $dur, $dur / 10);
