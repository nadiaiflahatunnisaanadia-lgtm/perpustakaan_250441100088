<?php

/*
|--------------------------------------------------------------------------
| Pintu masuk aplikasi Laravel di Vercel
|--------------------------------------------------------------------------
| Vercel menjalankan PHP sebagai serverless function dengan filesystem
| read-only, kecuali folder /tmp. Laravel perlu menulis cache, view
| terkompilasi, dan log, sehingga semuanya diarahkan ke /tmp lebih dulu.
*/

$tmp = '/tmp';

// Folder storage Laravel dipindahkan ke /tmp/storage
$storage = $tmp . '/storage';
foreach ([
    'app/public',
    'framework/cache/data',
    'framework/sessions',
    'framework/views',
    'logs',
] as $dir) {
    if (! is_dir("$storage/$dir")) {
        mkdir("$storage/$dir", 0755, true);
    }
}

// Nilai bawaan; environment variable yang diatur di dashboard Vercel tetap diutamakan.
$bawaan = [
    'LARAVEL_STORAGE_PATH' => $storage,
    'VIEW_COMPILED_PATH'   => "$storage/framework/views",
    'APP_CONFIG_CACHE'     => "$tmp/config.php",
    'APP_ROUTES_CACHE'     => "$tmp/routes.php",
    'APP_EVENTS_CACHE'     => "$tmp/events.php",
    'APP_PACKAGES_CACHE'   => "$tmp/packages.php",
    'APP_SERVICES_CACHE'   => "$tmp/services.php",
    'LOG_CHANNEL'          => 'stderr',
];

foreach ($bawaan as $kunci => $nilai) {
    if (getenv($kunci) === false) {
        putenv("$kunci=$nilai");
        $_ENV[$kunci] = $nilai;
        $_SERVER[$kunci] = $nilai;
    }
}

// Teruskan ke front controller Laravel yang biasa
require __DIR__ . '/../public/index.php';
