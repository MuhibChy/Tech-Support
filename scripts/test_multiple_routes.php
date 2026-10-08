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

$routesToTest = [
    '/',
    '/about',
    '/services',
    '/pricing',
    '/contact',
    '/faq',
    '/careers',
    '/industries',
    '/offices',
    '/case-studies',
    '/portfolio',
    '/useful-links',
    '/blog',
    '/knowledge-base',
    '/legal/privacy-policy',
    '/legal/terms-of-service',
    '/legal/cookie-policy',
    '/legal/accessibility',
    '/legal/refund-policy',
    '/legal/service-level-agreement',
    '/login',
    '/register',
    '/get-quote',
];

foreach ($routesToTest as $uri) {
    $request = Illuminate\Http\Request::create($uri, 'GET');
    $app->instance('request', $request);
    $response = $kernel->handle($request);
    $status = $response->getStatusCode();
    $len = strlen($response->getContent());
    echo sprintf("%-35s Status: %d, Length: %d\n", $uri, $status, $len);
}
