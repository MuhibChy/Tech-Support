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

$customer = \App\Models\User::where('role', 'customer')->first();
auth()->login($customer);

$portalRoutes = [
    '/portal/manual',
    '/portal/tickets',
    '/portal/invoices',
    '/portal/projects',
    '/portal/services',
];

foreach ($portalRoutes as $uri) {
    $request = Illuminate\Http\Request::create($uri, 'GET');
    $app->instance('request', $request);
    $response = $kernel->handle($request);
    echo sprintf("%-30s Status: %d, Length: %d\n", $uri, $response->getStatusCode(), strlen($response->getContent()));
}
