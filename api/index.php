<?php

// 1. Alihkan folder storage dan cache ke /tmp agar tidak Error 500 di Vercel
$storagePath = '/tmp/storage/bootstrap/cache';
if (!is_dir($storagePath)) {
    mkdir($storagePath, 0755, true);
}

putenv("APP_STORAGE=/tmp");
putenv("VIEW_COMPILED_PATH=/tmp/storage/bootstrap/cache");

// 2. Hubungkan kembali ke file utama Laravel Anda
require __DIR__ . '/../public/index.php';
