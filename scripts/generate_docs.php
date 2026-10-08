<?php

use Illuminate\Support\Facades\File;

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

// List of routes to generate
$routes = [
    '', // homepage
    'offices',
    // Add more routes as needed
];

$docsDir = __DIR__ . '/../docs';

if (is_dir($docsDir)) {
    // Remove existing docs directory to start fresh
    array_map('unlink', glob("$docsDir/*.*"));
    array_map('rmdir', glob("$docsDir/*", GLOB_ONLYDIR));
    rmdir($docsDir);
}
mkdir($docsDir, 0755, true);

foreach ($routes as $route) {
    $uri = $route === '' ? '/' : "/{$route}";
    $request = Illuminate\Http\Request::create($uri, 'GET');
    $app->instance('request', $request);

    $response = $kernel->handle($request);
    $content = $response->getContent();

    // Determine the file path
    if ($route === '') {
        $filePath = $docsDir . '/index.html';
    } else {
        // Create a directory for the route and put index.html inside
        $filePath = $docsDir . "/" . $route . "/index.html";
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    file_put_contents($filePath, $content);
    echo "Generated: {$filePath}\n";
}

// Copy the built assets
$srcAssets = __DIR__ . '/../public/build';
$destAssets = $docsDir . '/build';

if (is_dir($srcAssets)) {
    // Use Laravel's Filesystem facade to copy directory
    File::copyDirectory($srcAssets, $destAssets);
    echo "Copied assets to {$destAssets}\n";
}

echo "Static site generated in {$docsDir}\n";