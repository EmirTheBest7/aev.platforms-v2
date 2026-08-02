<?php

declare(strict_types=1);

/**
 * Minimal shared page chrome for apps/account/* test pages.
 * Intentionally framework-free — just two small helper functions.
 */

function render_page_start(string $title): void
{
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES) ?> — ALIEV.IO V2</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #0f1115;
            color: #e6e8eb;
            display: flex;
            justify-content: center;
            padding: 48px 16px;
            margin: 0;
        }
        .card {
            width: 100%;
            max-width: 420px;
            background: #171a21;
            border: 1px solid #2a2f3a;
            border-radius: 12px;
            padding: 32px;
        }
        h1 {
            font-size: 20px;
            margin: 0 0 4px;
        }
        .subtitle {
            color: #8b93a1;
            font-size: 14px;
            margin: 0 0 24px;
        }
        label {
            display: block;
            font-size: 13px;
            color: #b7bdc8;
            margin: 16px 0 6px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            background: #0f1115;
            border: 1px solid #2a2f3a;
            border-radius: 8px;
            color: #e6e8eb;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #6366f1;
        }
        button {
            width: 100%;
            margin-top: 24px;
            padding: 11px;
            background: #6366f1;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover { background: #4f52d6; }
        .nav {
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
        }
        .nav a { color: #8b93a1; text-decoration: none; }
        .nav a:hover { color: #e6e8eb; }
        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }
        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
        }
        .field-error {
            color: #fca5a5;
            font-size: 12px;
            margin-top: 4px;
        }
        .user-info {
            font-size: 13px;
            color: #b7bdc8;
            line-height: 1.6;
        }
        .user-info strong { color: #e6e8eb; }
        code {
            background: #0f1115;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="card">
    <?php
}

function render_page_end(): void
{
    ?>
    </div>
</body>
</html>
    <?php
}

/**
 * @param array<int, string> $errors
 */
function render_alert(string $type, string $message): void
{
    $class = $type === 'success' ? 'alert-success' : 'alert-error';
    echo '<div class="alert ' . $class . '">' . htmlspecialchars($message, ENT_QUOTES) . '</div>';
}
