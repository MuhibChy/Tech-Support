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

$admin = \App\Models\User::where('email', 'admin@techsupport.com')->first();
auth()->login($admin);

$request = Illuminate\Http\Request::create('/admin/manual', 'GET');
$app->instance('request', $request);
$response = $kernel->handle($request);
echo sprintf("%-30s Status: %d, Length: %d\n", '/admin/manual', $response->getStatusCode(), strlen($response->getContent()));
