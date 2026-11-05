<?php
declare(strict_types=1);

header('Access-Control-Allow-Origin: ' . env('APP_CORS_ORIGIN', '*'));
header('Access-Control-Allow-Headers: Accept, Content-Type, Origin, X-Requested-With, Authorization, x-auth, x-client, x-crypt, x-time, x-bifrost, trace-id');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, PATCH, DELETE');
// Allow-Credentials não é enviado: o valor "*" usado antes é inválido (só "true" é aceito),
// então credenciais nunca foram liberadas; a autenticação é por header Authorization.
// Expõe ao front-end os headers do próprio framework (os demais o navegador oculta).
header('Access-Control-Expose-Headers: X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset, Retry-After, X-Cache-Middleware');
// Um único Cache-Control: um segundo header() com o mesmo nome substituiria este.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');