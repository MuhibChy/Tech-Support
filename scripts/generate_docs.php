<?php
/**
 * generate_docs.php — Full static public-site export for GitHub Pages.
 *
 * Renders every public GET route that needs no auth/server state into docs/,
 * copies Vite build assets + og-cover.jpg, injects honest static-form fallback
 * notices (never fake success), and writes sitemap.xml / robots.txt / 404.
 */

use Illuminate\Support\Facades\File;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$root = 'https://muhibchy.github.io/Tech-Support';

function bootRootUrl($app, $root, $uri) {
    $request = Illuminate\Http\Request::create($uri, 'GET', [], [], [], [
        'HTTPS' => 'on',
        'SERVER_PORT' => 443,
    ]);
    $app->instance('request', $request);
    Illuminate\Support\Facades\URL::forceRootUrl($root);
    config(['app.url' => $root, 'app.asset_url' => $root]);
}

bootRootUrl($app, $root, '/');

// Concrete slugs (queried from DB; empty tables simply export index pages).
// NOTE: export the FULL catalog — index pages link every active item, so a
// capped export would leave dead links on the static mirror.
$services = App\Models\Service::where('is_active', 1)->orderBy('id')->pluck('slug')->values()->all();
$blogs = App\Models\BlogPost::orderBy('id')->pluck('slug')->values()->all();
$kbs = App\Models\KbArticle::orderBy('id')->pluck('slug')->values()->all();
$careers = [];
try {
    if (class_exists(App\Models\CareerPost::class)) {
        $careers = App\Models\CareerPost::orderBy('id')->pluck('slug')->take(6)->values()->all();
    }
} catch (Throwable $e) { /* table may be empty/unmigrated — index page only */ }
$cases = [];
try {
    if (class_exists(App\Models\CaseStudy::class)) {
        $cases = App\Models\CaseStudy::orderBy('id')->pluck('slug')->take(6)->values()->all();
    }
} catch (Throwable $e) { /* table may be empty/unmigrated — index page only */ }
$portfolio = [];
try {
    if (class_exists(App\Models\PortfolioItem::class)) {
        $portfolio = App\Models\PortfolioItem::orderBy('id')->pluck('slug')->take(6)->values()->all();
    }
} catch (Throwable $e) { /* table may be empty/unmigrated — index page only */ }

$routes = [
    '', 'about', 'services', 'pricing', 'contact', 'blog', 'knowledge-base',
    'faq', 'get-quote', 'careers', 'industries', 'offices', 'case-studies',
    'portfolio', 'useful-links',
    // Auth entry points are public GET routes: exported statically with the
    // honest "static mirror cannot submit forms" notice (never fake success).
    'login', 'register',
    'legal/privacy-policy', 'legal/terms-of-service', 'legal/cookie-policy',
    'legal/accessibility', 'legal/refund-policy', 'legal/service-level-agreement',
];
foreach ($services as $s) { $routes[] = 'services/' . $s; }
foreach ($blogs as $s) { $routes[] = 'blog/' . $s; }
foreach ($kbs as $s) { $routes[] = 'knowledge-base/' . $s; }
foreach ($careers as $s) { if ($s) { $routes[] = 'careers/' . $s; } }
foreach ($cases as $s) { if ($s) { $routes[] = 'case-studies/' . $s; } }
foreach ($portfolio as $s) { if ($s) { $routes[] = 'portfolio/' . $s; } }

// De-duplicate (slug lists may overlap index routes) while preserving order.
$routes = array_values(array_unique($routes));

$docsDir = __DIR__ . '/../docs';
if (is_dir($docsDir)) { File::deleteDirectory($docsDir); }
mkdir($docsDir, 0755, true);

$exported = [];
$failed = [];

foreach ($routes as $route) {
    $uri = $route === '' ? '/' : "/{$route}";
    bootRootUrl($app, $root, $uri);
    try {
        $response = $kernel->handle(Illuminate\Http\Request::create($uri, 'GET', [], [], [], [
            'HTTPS' => 'on', 'SERVER_PORT' => 443,
        ]));
        $status = $response->getStatusCode();
        if ($status >= 400) { $failed[] = "{$route} (HTTP {$status})"; continue; }
        $content = $response->getContent();
    } catch (Throwable $e) {
        $failed[] = "{$route} (EX: " . substr($e->getMessage(), 0, 120) . ")";
        continue;
    }

    $content = applyStaticFormFallback($content, $route);
    $content = applyStaticBackendLinks($content, $root);
    $content = applyStaticSeo($content, $root, $route);

    if ($route === '') {
        $filePath = $docsDir . '/index.html';
    } else {
        $filePath = $docsDir . '/' . $route . '/index.html';
        $dir = dirname($filePath);
        if (!is_dir($dir)) { mkdir($dir, 0755, true); }
    }
    file_put_contents($filePath, $content);
    $exported[] = $route === '' ? '/' : "/{$route}";
    echo "Generated: {$filePath}\n";
    $kernel->terminate($app['request'], $response);
}

// Copy Vite build assets
$srcAssets = __DIR__ . '/../public/build';
if (is_dir($srcAssets)) { File::copyDirectory($srcAssets, $docsDir . '/build'); echo "Copied assets\n"; }

// Copy public root files needed by static site
foreach (['og-cover.jpg', 'favicon.ico'] as $f) {
    $src = __DIR__ . '/../public/' . $f;
    if (is_file($src)) { copy($src, $docsDir . '/' . $f); echo "Copied {$f}\n"; }
}

// robots.txt / sitemap.xml (public URLs only)
file_put_contents($docsDir . '/robots.txt', "User-agent: *\nAllow: /\n\nSitemap: {$root}/sitemap.xml\n");
$sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($exported as $u) {
    $loc = $u === '/' ? $root . '/' : $root . $u . '/';
    $prio = $u === '/' ? '1.0' : '0.7';
    $sitemap .= "  <url><loc>{$loc}</loc><changefreq>weekly</changefreq><priority>{$prio}</priority></url>\n";
}
$sitemap .= '</urlset>';
file_put_contents($docsDir . '/sitemap.xml', $sitemap);

// 404.html (standalone, no PHP)
$notFound = <<<'HTML'
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Page Not Found — TechSupport Solutions</title>
<meta name="robots" content="noindex, follow">
<link rel="canonical" href="https://muhibchy.github.io/Tech-Support/">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
<style>body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#000;color:#fff;font-family:Inter,system-ui,sans-serif;text-align:center;padding:24px}.card{max-width:520px;padding:48px 40px;border:1px solid rgba(255,255,255,.14);border-radius:24px;background:rgba(255,255,255,.05);backdrop-filter:blur(24px)}h1{font-size:72px;margin:0 0 8px}p{color:#aaa}a{display:inline-block;margin-top:24px;padding:12px 28px;border-radius:12px;background:#fff;color:#000;font-weight:600;text-decoration:none}</style>
</head>
<body>
<main class="card">
<p style="font-size:12px;letter-spacing:.25em;color:#888">ERROR 404</p>
<h1>404</h1>
<p>The page you requested could not be found. It may have moved or never existed.</p>
<a href="https://muhibchy.github.io/Tech-Support/">Return to Homepage</a>
</main>
</body>
</html>
HTML;
file_put_contents($docsDir . '/404.html', $notFound);

// .nojekyll (prevents Jekyll processing on Pages)
touch($docsDir . '/.nojekyll');

echo "\nExported " . count($exported) . " pages, " . count($failed) . " failed.\n";
foreach ($failed as $f) { echo "FAILED: {$f}\n"; }
echo "Static site generated in {$docsDir}\n";

/**
 * Inject an honest static-form fallback: a visible notice + a submit
 * interceptor that NEVER fakes success — it blocks POST and points the
 * visitor at real contact channels.
 */
function applyStaticFormFallback($html, $route) {
    if (stripos($html, '<form') === false) { return $html; }
    $notice = <<<'HTML'
<div class="static-form-notice" role="note" style="margin:0 0 1.25rem;padding:1rem 1.25rem;border-radius:1rem;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.06);backdrop-filter:blur(24px);color:#e2e8f0;font-size:.875rem;line-height:1.6;">
<strong style="color:#fff;">Static preview notice:</strong> this page is served from our static mirror, which cannot submit forms. Nothing you enter here is sent anywhere. To reach us, please use the <a href="https://muhibchy.github.io/Tech-Support/contact/" style="color:#67e8f9;">contact page</a> or <a href="https://muhibchy.github.io/Tech-Support/get-quote/" style="color:#67e8f9;">request a quote</a> on the live application.
</div>
HTML;
    // Insert notice directly after every server-POST <form> opening tag.
    // Alpine-only forms (e.g. "@submit.prevent" chat widget) are excluded.
    $html = preg_replace_callback('/<form\b[^>]*>/i', function ($m) use ($notice) {
        $tag = $m[0];
        if (stripos($tag, '@submit.prevent') !== false) { return $tag; }
        if (preg_match('/method\s*=\s*["\']POST["\']/i', $tag) !== 1) { return $tag; }
        return $tag . $notice;
    }, $html);
    // Intercept submits: prevent POST, highlight notice, no fake success.
    // Scoped to forms carrying the notice; the Alpine chat widget is untouched.
    $guard = <<<'HTML'
<script>(function(){document.querySelectorAll("form").forEach(function(f){var n=f.querySelector(".static-form-notice");if(!n)return;f.addEventListener("submit",function(e){e.preventDefault();n.scrollIntoView({behavior:"smooth",block:"center"});n.style.borderColor="rgba(103,232,249,.7)";});});})();
</script>
HTML;
    $html = str_ireplace('</body>', $guard . '</body>', $html);
    return $html;
}

/**
 * Neutralize backend-only interactive links that have no static target:
 * session switchers (language/currency) and auth/account/API endpoints
 * require a live Laravel backend. They are converted to non-navigating
 * elements (no dead links, no fake hrefs) with an honest explanation.
 */
function applyStaticBackendLinks($html, $root) {
    $prefixes = [
        '/lang/', '/currency/',
        '/portal', '/admin', '/api/', '/mfa', '/password', '/email/',
        '/logout', '/healthz', '/stripe/webhook',
    ];
    foreach ($prefixes as $prefix) {
        $quoted = preg_quote($root . $prefix, '/');
        $html = preg_replace_callback(
            '/<a\b([^>]*?)href="' . $quoted . '[^"]*"([^>]*?)>(.*?)<\/a>/is',
            function ($m) {
                $attrs = trim($m[1] . ' ' . $m[2]);
                $attrs = preg_replace('/\s+/', ' ', $attrs);
                return '<span ' . $attrs . ' aria-disabled="true" title="Available on the live application">' . $m[3] . '</span>';
            },
            $html
        );
    }
    return $html;
}

/** Ensure per-page canonical + twitter/OG image tags resolve on Pages. */
function applyStaticSeo($html, $root, $route) {
    $path = $route === '' ? '/' : '/' . $route . '/';
    $canonical = $root . ($route === '' ? '/' : '/' . $route . '/');
    if (stripos($html, 'rel="canonical"') === false) {
        $html = preg_replace('/<\/title>/i', '</title>' . "\n" . '    <link rel="canonical" href="' . $canonical . '">', $html, 1);
    }
    if (stripos($html, 'twitter:card') === false) {
        $tags = "\n" . '    <meta name="twitter:card" content="summary_large_image">' . "\n"
            . '    <meta name="twitter:image" content="' . $root . '/og-cover.jpg">' . "\n"
            . '    <meta property="og:image" content="' . $root . '/og-cover.jpg">';
        if ($route === '') {
            $tags .= "\n" . '    <script type="application/ld+json">{"@context":"https://schema.org","@type":"Organization","name":"TechSupport Solutions","url":"' . $root . '/","description":"IT support, cybersecurity, cloud solutions, and managed services with a structured quote-to-completion workflow and customer portal."}</script>';
        }
        $html = preg_replace('/<\/title>/i', '</title>' . $tags, $html, 1);
    }
    return $html;
}
