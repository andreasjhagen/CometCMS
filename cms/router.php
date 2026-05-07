<?php

/**
 * PHP built-in server router.
 * Mimics Apache's mod_rewrite behaviour from .htaccess:
 * - Serve real files/directories directly (static assets, media …).
 * - Everything else goes through index.php.
 *
 * Usage:
 *   php -S localhost:8000 router.php
 */

$uri  = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$file = __DIR__ . $uri;

// Serve existing files (JS/CSS/images built by Vite, uploaded media …) directly.
if ($uri !== '/' && is_file($file)) {
    return false;
}

// php -S sets SCRIPT_NAME to the request URI path, but Http::path() and the
// session-cookie path calculation in bootstrap.php both derive the app's base
// prefix from dirname(SCRIPT_NAME). Force it to match what Apache+mod_rewrite
// would set (i.e. the actual entry-point file) so path stripping works correctly.
$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/index.php';

require __DIR__ . '/index.php';
