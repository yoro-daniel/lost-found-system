<?php
$databaseUrl = env('DATABASE_URL', '');
$parsedDatabaseUrl = $databaseUrl ? parse_url($databaseUrl) : [];

return [
    'name' => env('APP_NAME', 'Lost and Found Management System'),
    'url' => env('APP_URL', 'http://localhost/Lost%20and%20Found/public'),
    'database' => [
        'host' => $parsedDatabaseUrl['host'] ?? env('DB_HOST', '127.0.0.1'),
        'port' => (string) ($parsedDatabaseUrl['port'] ?? env('DB_PORT', '3306')),
        'name' => isset($parsedDatabaseUrl['path'])
            ? ltrim($parsedDatabaseUrl['path'], '/')
            : env('DB_NAME', 'lost_found_management'),
        'user' => isset($parsedDatabaseUrl['user'])
            ? rawurldecode($parsedDatabaseUrl['user'])
            : env('DB_USER', 'root'),
        'pass' => isset($parsedDatabaseUrl['pass'])
            ? rawurldecode($parsedDatabaseUrl['pass'])
            : env('DB_PASS', ''),
        'ssl_ca_path' => env('DB_SSL_CA_PATH', ''),
        'ssl_ca' => env('DB_SSL_CA', ''),
    ],
    'mail' => [
        'host' => env('MAIL_HOST', 'smtp.gmail.com'),
        'port' => (int) env('MAIL_PORT', '587'),
        'username' => env('MAIL_USERNAME', ''),
        'password' => env('MAIL_PASSWORD', ''),
        'from_address' => env('MAIL_FROM_ADDRESS', ''),
        'from_name' => env('MAIL_FROM_NAME', 'Lost and Found Office'),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'timeout' => (int) env('MAIL_TIMEOUT', '12'),
    ],
    'cloudinary' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME', ''),
        'api_key' => env('CLOUDINARY_API_KEY', ''),
        'api_secret' => env('CLOUDINARY_API_SECRET', ''),
        'folder' => env('CLOUDINARY_FOLDER', 'school-lost-found/items'),
    ],
];
