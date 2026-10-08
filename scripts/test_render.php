<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/', 'GET');
$app->instance('request', $request);

\Illuminate\Support\Facades\URL::forceRootUrl('https://muhibchy.github.io/Tech-Support');
config([
    'app.url' => 'https://muhibchy.github.io/Tech-Support',
    'app.asset_url' => 'https://muhibchy.github.io/Tech-Support',
]);

$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Length: " . strlen($response->getContent()) . "\n";

$html = $response->getContent();

// Check some links
preg_match_all('/(href|src)="([^"]*)"/', $html, $matches);
$urls = array_unique($matches[2]);
$samples = array_slice($urls, 0, 25);
foreach ($samples as $u) {
    echo "URL: $u\n";
}
