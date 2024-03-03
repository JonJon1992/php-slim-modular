<?php
declare(strict_types=1);

header('Access-Control-Allow-Origin: ' . env('APP_CORS_ORIGIN', '*'));
header('Access-Control-Allow-Headers: Accept, Content-Type, Origin, X-Requested-With, Authorization, x-auth, x-client, x-crypt, x-time, x-bifrost, x-trace');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, PATCH, DELETE');
header('Access-Control-Allow-Credentials: *');
header('Access-Control-Expose-Headers: true');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0');
header('Pragma: no-cache');