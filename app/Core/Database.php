<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $db = config('database');
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $db['host'],
            $db['port'],
            $db['name']
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        $sslCaPath = self::sslCaPath($db);
        if ($sslCaPath) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCaPath;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        self::$pdo = new PDO($dsn, $db['user'], $db['pass'], $options);

        return self::$pdo;
    }

    private static function sslCaPath(array $db): ?string
    {
        if (!empty($db['ssl_ca_path']) && file_exists($db['ssl_ca_path'])) {
            return $db['ssl_ca_path'];
        }

        if (empty($db['ssl_ca'])) {
            return null;
        }

        $path = sys_get_temp_dir() . '/lost-found-mysql-ca.pem';
        $certificate = str_replace('\n', "\n", $db['ssl_ca']);
        if (file_put_contents($path, $certificate) === false) {
            throw new PDOException('Unable to write MySQL SSL CA certificate.');
        }

        return $path;
    }
}
