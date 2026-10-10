<?php
/**
 * check_docs_links.php — Static export link audit for GitHub Pages.
 *
 * Scans docs/ for href/src references and classifies each as:
 *   WORKING            target file exists in docs/
 *   EXTERNAL           absolute http(s) URL off-site (not verified live)
 *   BACKEND-ONLY       Laravel session/API/auth URL with no static target
 *                      (must have been neutralized or given a static page)
 *   INTENTIONALLY-DISABLED  span[aria-disabled] (switchers on static mirror)
 *   BROKEN             would 404 on GitHub Pages — must be zero
 *
 * Exit code 1 when BROKEN links exist.
 */

$docsDir = __DIR__ . '/../docs';
$root = 'https://muhibchy.github.io/Tech-Support';

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($docsDir, FilesystemIterator::SKIP_DOTS)
);
$htmlFiles = [];
foreach ($files as $f) {
    if (substr($f->getFilename(), -5) === '.html') {
        $htmlFiles[] = $f->getPathname();
    }
}

// Backend-only prefixes: these MUST NOT appear as <a href> in static HTML.
$backendPrefixes = [
    '/lang/', '/currency/', '/portal', '/admin', '/api/',
    '/mfa', '/password/', '/email/', '/logout', '/healthz',
    '/stripe/webhook',
];

$broken = [];
$backendHits = [];
$working = 0;
$external = 0;
$disabled = 0;

foreach ($htmlFiles as $file) {
    $html = file_get_contents($file);
    $rel = substr($file, strlen($docsDir));

    // Count neutralized switchers.
    $disabled += preg_match_all('/aria-disabled="true"/i', $html);

    // Extract href/src.
    preg_match_all('/(?:href|src)\s*=\s*"([^"]+)"/i', $html, $m);
    foreach ($m[1] as $link) {
        if ($link === '' || $link[0] === '#') {
            continue;
        }
        if (str_starts_with($link, 'data:') || str_starts_with($link, 'mailto:') || str_starts_with($link, 'tel:')) {
            continue;
        }
        // External absolute URL.
        if (preg_match('#^https?://#i', $link)) {
            // On-site Pages URL → map to docs/ path.
            if (str_starts_with($link, $root)) {
                $path = substr($link, strlen($root));
                $path = strtok($path, '?#');
                if ($path === '' || $path === '/') {
                    $working++;
                    continue;
                }
                $candidate = $docsDir . $path;
                if (is_dir($candidate) && is_file($candidate . '/index.html')) {
                    $working++;
                    continue;
                }
                if (is_file($candidate)) {
                    $working++;
                    continue;
                }
                // Extensionless file fallback (e.g. /sitemap.xml has extension — handled above).
                $broken[] = "{$rel} → {$link}";
                continue;
            }
            $external++;
            continue;
        }
        // Root-relative (should not exist — base path requires absolute URLs).
        if (str_starts_with($link, '/') && !str_starts_with($link, '//')) {
            // Allow font/CDN protocol-relative only; plain root-relative is a base-path bug
            // unless it targets a backend route (also bad in static).
            $broken[] = "{$rel} → {$link} (root-relative, breaks under /Tech-Support/)";
            continue;
        }
    }

    // Backend-only links must have been neutralized/exported.
    foreach ($backendPrefixes as $prefix) {
        $quoted = preg_quote($root . $prefix, '/');
        if (preg_match_all('/<a\b[^>]*href="' . $quoted . '[^"]*"[^>]*>/i', $html, $mm)) {
            foreach ($mm[0] as $tag) {
                $backendHits[] = "{$rel} → {$tag}";
            }
        }
    }
}

echo "Files scanned: " . count($htmlFiles) . "\n";
echo "WORKING on-site refs: {$working}\n";
echo "EXTERNAL refs (not verified live): {$external}\n";
echo "NEUTRALIZED switcher spans: {$disabled}\n";
echo "BACKEND-ONLY <a href> remaining: " . count($backendHits) . "\n";
foreach (array_slice($backendHits, 0, 30) as $h) {
    echo "  BACKEND: {$h}\n";
}
echo "BROKEN: " . count($broken) . "\n";
foreach (array_slice($broken, 0, 60) as $b) {
    echo "  BROKEN: {$b}\n";
}

if (count($broken) > 0 || count($backendHits) > 0) {
    echo "\nLINK CHECK FAILED\n";
    exit(1);
}
echo "\nLINK CHECK PASSED — zero broken links, zero backend-only anchors.\n";
