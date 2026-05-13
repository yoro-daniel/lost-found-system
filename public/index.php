<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\ClaimController;
use App\Controllers\DashboardController;
use App\Controllers\ItemController;
use App\Controllers\ReportController;
use App\Controllers\UserController;

$route = $_GET['route'] ?? (current_user() ? 'items' : 'login');
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET' => [
        'health' => static function (): void {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'ok']);
        },
        'login' => [AuthController::class, 'login'],
        'otp' => [AuthController::class, 'otp'],
        'register' => [AuthController::class, 'register'],
        'logout' => [AuthController::class, 'logout'],
        'dashboard' => [DashboardController::class, 'index'],
        'items' => [ItemController::class, 'index'],
        'items.create' => [ItemController::class, 'create'],
        'items.edit' => [ItemController::class, 'edit'],
        'claims' => [ClaimController::class, 'index'],
        'users' => [UserController::class, 'index'],
        'reports' => [ReportController::class, 'index'],
    ],
    'POST' => [
        'login.post' => [AuthController::class, 'authenticate'],
        'otp.post' => [AuthController::class, 'verifyOtp'],
        'register.post' => [AuthController::class, 'store'],
        'items.store' => [ItemController::class, 'store'],
        'items.update' => [ItemController::class, 'update'],
        'items.delete' => [ItemController::class, 'delete'],
        'claims.store' => [ClaimController::class, 'store'],
        'claims.review' => [ClaimController::class, 'review'],
        'users.store' => [UserController::class, 'store'],
        'users.update' => [UserController::class, 'update'],
    ],
];

$handler = $routes[$method][$route] ?? null;
if (!$handler) {
    http_response_code(404);
    exit('Route not found.');
}

if (is_callable($handler)) {
    $handler();
    exit;
}

[$controller, $action] = $handler;
(new $controller())->$action();
