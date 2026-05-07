<?php

declare(strict_types=1);

define('COMET_ROOT', dirname(__DIR__));
define('COMET_APP', COMET_ROOT . '/app');
define('COMET_STORAGE', COMET_ROOT . '/storage');

$cometConfig = require COMET_ROOT . '/config/config.php';

// Merge runtime settings from storage/settings.json (takes precedence over config.php)
$_settingsFile = COMET_STORAGE . '/settings.json';
if (is_file($_settingsFile)) {
    $_runtimeSettings = json_decode((string) file_get_contents($_settingsFile), true);
    if (is_array($_runtimeSettings)) {
        $cometConfig = array_replace($cometConfig, $_runtimeSettings);
    }
    unset($_runtimeSettings);
}
unset($_settingsFile);

spl_autoload_register(static function (string $class): void {
    $prefix = 'CometCMS\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $path = COMET_APP . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

    if (is_file($path)) {
        require $path;
    }
});

function comet_config(string $key, mixed $default = null): mixed
{
    global $cometConfig;

    $value = $cometConfig;

    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}

function comet_version(): string
{
    $versionFile = COMET_APP . '/version.php';

    if (is_file($versionFile)) {
        $version = require $versionFile;

        if (is_string($version) && $version !== '') {
            return $version;
        }
    }

    return (string) comet_config('app.version', '1.0.0');
}

date_default_timezone_set((string) comet_config('app.timezone', 'UTC'));

foreach (
    [
        COMET_STORAGE,
        COMET_STORAGE . '/users',
        COMET_STORAGE . '/api-tokens',
        COMET_STORAGE . '/roles',
        COMET_STORAGE . '/content',
        COMET_STORAGE . '/media',
        COMET_STORAGE . '/media-thumbs',
        COMET_STORAGE . '/media-meta',
        COMET_STORAGE . '/sessions',
        COMET_STORAGE . '/cache',
        COMET_STORAGE . '/cache/api',
        COMET_STORAGE . '/cache/login-throttle',
        COMET_STORAGE . '/content-types',
        COMET_STORAGE . '/revisions',
        COMET_STORAGE . '/revisions/content',
        COMET_STORAGE . '/trash',
        COMET_STORAGE . '/trash/content',
        COMET_STORAGE . '/trash/media',
        COMET_STORAGE . '/logs',
        COMET_STORAGE . '/backups',
        COMET_STORAGE . '/updates',
    ] as $directory
) {
    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }
}

$scriptBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$cookiePath = $scriptBase === '' ? '/' : $scriptBase;
$requestPath = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($scriptBase !== '' && $scriptBase !== '/' && str_starts_with($requestPath, $scriptBase)) {
    $requestPath = substr($requestPath, strlen($scriptBase));
}

$requestPath = '/' . ltrim($requestPath, '/');
$needsSession = !str_starts_with($requestPath, '/api/')
    && $requestPath !== '/api'
    && !str_starts_with($requestPath, '/media/')
    && !str_starts_with($requestPath, '/media-thumbs/');

session_name((string) comet_config('security.session_name', 'cometcms_admin'));
session_save_path(COMET_STORAGE . '/sessions');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => $cookiePath,
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax',
]);

if ($needsSession && session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
