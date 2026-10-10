<?php
// One-time migration: move duplicated inline brand gradient into the shared
// .btn-brand-gradient CSS class. Dry-run by default; pass --apply to write.
$apply = in_array('--apply', $argv ?? []);
$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../resources/views'));
$changed = [];
foreach ($dir as $file) {
    if ($file->isDir() || substr($file, -10) !== '.blade.php') { continue; }
    $orig = file_get_contents($file);
    $html = preg_replace_callback(
        '/<[a-zA-Z][^<>]*?background:\s*linear-gradient\(135deg,\s*#16A34A,\s*#2563EB\);[^<>]*?>/',
        function ($m) {
            $tag = $m[0];
            // Strip the background declaration from the style attribute.
            $tag = str_replace('background: linear-gradient(135deg, #16A34A, #2563EB);', '', $tag);
            // Drop emptied style=" ...box-shadow-less... " attributes.
            $tag = preg_replace('/\s*style="\s*"/', '', $tag);
            $tag = preg_replace('/\s*style=\'\s*\'/', '', $tag);
            // Append shared class.
            if (preg_match('/class="([^"]*)"/', $tag, $cm)) {
                if (strpos($cm[1], 'btn-brand-gradient') === false) {
                    $tag = str_replace($cm[0], 'class="' . $cm[1] . ' btn-brand-gradient"', $tag);
                }
            } elseif (preg_match("/class='([^']*)'/", $tag, $cm)) {
                if (strpos($cm[1], 'btn-brand-gradient') === false) {
                    $tag = str_replace($cm[0], "class='" . $cm[1] . " btn-brand-gradient'", $tag);
                }
            } else {
                $tag = preg_replace('/^<([a-zA-Z]+)/', '<$1 class="btn-brand-gradient"', $tag, 1);
            }
            return $tag;
        },
        $orig
    );
    if ($html !== $orig) {
        $changed[] = $file;
        if ($apply) { file_put_contents($file, $html); }
    }
}
echo ($apply ? 'APPLIED' : 'DRY-RUN') . ' files: ' . count($changed) . "\n";
foreach ($changed as $f) { echo "  {$f}\n"; }
