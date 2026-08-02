<?php

declare(strict_types=1);

/**
 * apps/account/logout.php
 *
 * Testing/demo page exercising Core\Auth\AuthFacade::logout().
 * Requires POST + valid CSRF token, since logout is a state-changing
 * request (destroys the session).
 */

require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!$auth->csrf()->validate($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Invalid CSRF token.');
}

$auth->logout(
    ipAddress: $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null,
);

header('Location: login.php');
exit;
