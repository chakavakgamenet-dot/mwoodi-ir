<?php
/**
 * Production route smoke-check. Fails deployment if required public API routes
 * are not registered exactly where the frontend expects them.
 */
$required = [
    'GET api/v1/health',
    'POST api/v1/auth/register',
    'POST api/v1/auth/login',
    'POST api/v1/auth/admin-login',
];

$routes = app('router')->getRoutes();
$registered = [];
foreach ($routes as $route) {
    foreach ((array) $route->methods() as $method) {
        if ($method === 'HEAD') continue;
        $registered[] = strtoupper($method).' '.$route->uri();
    }
}

foreach ($required as $need) {
    if (!in_array($need, $registered, true)) {
        fwrite(STDERR, "[MWoodi] FATAL: required route missing: {$need}\n");
        fwrite(STDERR, "[MWoodi] Registered API routes:\n");
        foreach ($registered as $r) {
            if (str_contains($r, 'api/v1')) fwrite(STDERR, "  {$r}\n");
        }
        exit(1);
    }
}

echo "[MWoodi] required API routes verified: health, register, login, admin-login\n";
