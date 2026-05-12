<?php
declare(strict_types=1);

$root = dirname(__DIR__);

if (file_exists($root . '/.env')) {
    foreach (file($root . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $value = trim($value, "\"' ");
        $_ENV[trim($key)] = $value;
        putenv(trim($key) . '=' . $value);
    }
}

require_once $root . '/app/helpers.php';

$sessionPath = $root . '/storage/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);
session_start();

spl_autoload_register(function (string $class) use ($root): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $path = $root . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

$vendor = $root . '/vendor/autoload.php';
if (file_exists($vendor)) {
    require_once $vendor;
}

$GLOBALS['config'] = require $root . '/config/app.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
