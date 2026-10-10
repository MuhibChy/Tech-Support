<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$out = [];
$out['services'] = App\Models\Service::where('is_active', 1)->orderBy('id')->pluck('slug')->take(8)->values()->all();
$out['blog'] = App\Models\BlogPost::orderBy('id')->pluck('slug')->take(6)->values()->all();
$out['kb'] = App\Models\KbArticle::orderBy('id')->pluck('slug')->take(6)->values()->all();
$out['cases'] = class_exists(App\Models\CaseStudy::class) ? App\Models\CaseStudy::orderBy('id')->pluck('slug')->take(6)->values()->all() : [];
$out['portfolio'] = App\Models\PortfolioItem::orderBy('id')->pluck('slug')->take(6)->values()->all();
$out['careers'] = class_exists(App\Models\CareerPost::class) ? App\Models\CareerPost::orderBy('id')->pluck('slug')->take(6)->values()->all() : [];
foreach ($out as $k => $v) { echo $k . '=' . implode(',', $v) . "\n"; }
echo 'counts cases=' . App\Models\CaseStudy::count() . ' port=' . App\Models\PortfolioItem::count() . ' careers=' . App\Models\CareerPost::count() . "\n";
