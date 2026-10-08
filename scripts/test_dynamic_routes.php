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

$dynamic = [
    '/services/penetration-testing',
    '/blog/future-of-it-support-2024',
    '/knowledge-base/how-to-create-support-ticket',
];

foreach ($dynamic as $uri) {
    $request = Illuminate\Http\Request::create($uri, 'GET');
    $app->instance('request', $request);
    $response = $kernel->handle($request);
    $status = $response->getStatusCode();
    $len = strlen($response->getContent());
    echo sprintf("%-45s Status: %d, Length: %d\n", $uri, $status, $len);
}
