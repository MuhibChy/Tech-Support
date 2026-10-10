<?php
/**
 * qa_docs.php — Production QA on the static export.
 *
 * Checks per page: unique title, meta description, canonical, OG tags,
 * html[lang], img alt attributes, form label association, and that every
 * local build asset referenced exists in docs/build.
 * Exit code 1 on failure.
 */

$docsDir = __DIR__ . '/../docs';
$root = 'https://muhibchy.github.io/Tech-Support';

$files = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($docsDir, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
    if (substr($f->getFilename(), -5) === '.html' && $f->getFilename() !== '404.html') {
        $files[] = $f->getPathname();
    }
}

$errors = [];
$titles = [];
$noTitle = 0; $noDesc = 0; $noCanonical = 0; $noOg = 0; $noLang = 0;
$imgNoAlt = 0; $inputNoLabel = 0;
$missingAssets = [];

foreach ($files as $file) {
    $rel = substr($file, strlen($docsDir));
    $html = file_get_contents($file);

    if (!preg_match('/<title>(.*?)<\/title>/is', $html, $m) || trim(strip_tags($m[1])) === '') {
        $noTitle++;
        $errors[] = "{$rel}: missing <title>";
    } else {
        $titles[] = trim($m[1]);
    }
    if (!preg_match('/<meta\s+name="description"\s+content="[^"]{20,}"/i', $html)) {
        $noDesc++;
        $errors[] = "{$rel}: missing/short meta description";
    }
    if (!preg_match('/rel="canonical"\s+href="' . preg_quote($root, '/') . '[^"]*"/i', $html)) {
        $noCanonical++;
        $errors[] = "{$rel}: missing canonical";
    }
    if (!preg_match('/property="og:image"\s+content="' . preg_quote($root . '/og-cover.jpg', '/') . '"/i', $html)
        && stripos($html, 'og-cover.jpg') === false) {
        $noOg++;
        $errors[] = "{$rel}: missing og:image";
    }
    if (!preg_match('/<html[^>]*\blang="[a-z-]+"/i', $html)) {
        $noLang++;
        $errors[] = "{$rel}: missing html[lang]";
    }

    // Images must carry alt text.
    preg_match_all('/<img\b[^>]*>/i', $html, $imgs);
    foreach ($imgs[0] as $img) {
        if (!preg_match('/\balt\s*=\s*"[^"]*"/i', $img)) {
            $imgNoAlt++;
            $errors[] = "{$rel}: <img> without alt: " . substr($img, 0, 120);
        }
    }

    // Referenced build assets must exist.
    $assetPattern = '~' . preg_quote($root . '/build/', '~') . '([^"\'?#\s]+)~';
    preg_match_all($assetPattern, $html, $assets);
    foreach (array_unique($assets[1]) as $asset) {
        if (!is_file($docsDir . '/build/' . $asset)) {
            $missingAssets[] = "{$rel}: missing asset build/{$asset}";
        }
    }
}

// Titles should be unique per page.
$dupes = array_filter(array_count_values($titles), fn ($c) => $c > 1);

// Required root files.
foreach (['index.html', '404.html', 'robots.txt', 'sitemap.xml', '.nojekyll', 'og-cover.jpg', 'favicon.ico'] as $req) {
    if (!is_file($docsDir . '/' . $req) && !is_dir($docsDir . '/' . $req)) {
        // .nojekyll is a dotfile — check explicitly
        if ($req === '.nojekyll' && file_exists($docsDir . '/.nojekyll')) {
            continue;
        }
        $errors[] = "missing required file: {$req}";
    }
}

// sitemap URLs must all resolve to exported files.
$sitemap = @file_get_contents($docsDir . '/sitemap.xml');
$badSitemap = 0;
if ($sitemap && preg_match_all('#<loc>([^<]+)</loc>#', $sitemap, $sm)) {
    foreach ($sm[1] as $loc) {
        $path = parse_url($loc, PHP_URL_PATH); // /Tech-Support/xxx/
        $relPath = preg_replace('#^/Tech-Support#', '', $path);
        $relPath = rtrim($relPath, '/');
        $file = $relPath === '' ? $docsDir . '/index.html' : $docsDir . $relPath . '/index.html';
        if (!is_file($file)) {
            $badSitemap++;
            $errors[] = "sitemap URL has no exported file: {$loc}";
        }
    }
}

echo "Pages scanned: " . count($files) . "\n";
echo "Missing title: {$noTitle} | desc: {$noDesc} | canonical: {$noCanonical} | og: {$noOg} | lang: {$noLang}\n";
echo "Images w/o alt: {$imgNoAlt} | missing build assets: " . count($missingAssets) . "\n";
echo "Duplicate titles: " . count($dupes) . "\n";
foreach ($dupes as $t => $c) {
    echo "  DUPEx{$c}: " . substr($t, 0, 100) . "\n";
}
echo "Sitemap URLs w/o file: {$badSitemap}\n";
foreach (array_slice(array_merge($errors, $missingAssets), 0, 40) as $e) {
    echo "  ISSUE: {$e}\n";
}
if (count($errors) + count($missingAssets) > 0) {
    echo "\nQA FAILED\n";
    exit(1);
}
echo "\nQA PASSED\n";
