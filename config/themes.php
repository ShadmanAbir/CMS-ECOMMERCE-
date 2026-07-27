<?php

/*
|--------------------------------------------------------------------------
| Site Configuration Loader
|--------------------------------------------------------------------------
|
| This file dynamically loads site configurations from the sites/ directory.
| Each site has its own site.php file containing all brand settings.
|
| To add a new site: php artisan site:create {slug}
|
*/

$themes = [];
$sitesDir = base_path('sites');

if (is_dir($sitesDir)) {
    foreach (glob("{$sitesDir}/*/site.php") as $siteConfig) {
        $slug = basename(dirname($siteConfig));
        if ($slug === '_template') {
            continue;
        }
        $themes[$slug] = require $siteConfig;
    }
}

return [
    'active' => env('THEME', 'badlab'),
    'themes' => $themes,
];
