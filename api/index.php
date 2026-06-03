<?php

// Mengalihkan folder kompilasi view secara aman ke direktori tmp Vercel
putenv('VIEW_COMPILED_PATH=/tmp');

// Memanggil file inisialisasi utama Laravel
require __DIR__ . '/../public/index.php';
