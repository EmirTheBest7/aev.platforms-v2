<?php

declare(strict_types=1);

/**
 * apps/account/login.php
 *
 * Testing/demo page exercising Core\Auth\AuthFacade::login().
 * Not a production UI — a minimal reference implementation showing how a
 * real endpoint (e.g. api/v2/auth/login.php) would call into core/auth/.
 */

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_layout.php';

use Core\Auth\DTO\LoginData;
use Core\Auth\Exception\AccountLockedException;
use Core\Auth\Exception\InvalidCredentialsException;
use Core\Auth\Exception\ValidationException;

/** @var array<string, array<int, string>> $fieldErrors */
$fieldErrors = [];
$generalError = null;

if ($auth->session()->isAuthenticated()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$auth->csrf()->validate($_POST['csrf_token'] ?? null)) {
        $generalError = 'Your form session expired. Please try again.';
    } else {
        try {
            $auth->login(new LoginData(
                email: (string) ($_POST['email'] ?? ''),
                password: (string) ($_POST['password'] ?? ''),
                ipAddress: $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null,
            ));

            header('Location: index.php');
            exit;

        } catch (ValidationException $e) {
            $fieldErrors = $e->errors();
        } catch (AccountLockedException $e) {
            $minutes = (int) ceil($e->retryAfterSeconds() / 60);
            $generalError = "Too many failed attempts. Please try again in about {$minutes} minute(s).";
        } catch (InvalidCredentialsException $e) {
            // Deliberately generic — never reveal whether the email exists.
            $generalError = 'Email or password is incorrect.';
        }
    }
}

$csrfToken = $auth->csrf()->getToken();
$justRegistered = isset($_GET['registered']);

render_page_start('Sign in');
?>

<h1>Sign in</h1>
<p class="subtitle">ALIEV.IO V2 — auth module test page</p>

<?php if ($justRegistered): ?>
    <?php render_alert('success', 'Account created. You can sign in now.'); ?>
<?php endif; ?>

<?php if ($generalError !== null): ?>
    <?php render_alert('error', $generalError); ?>
<?php endif; ?>

<form method="post" action="login.php" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>" required>
    <?php foreach ($fieldErrors['email'] ?? [] as $msg): ?>
        <div class="field-error"><?= htmlspecialchars($msg, ENT_QUOTES) ?></div>
    <?php endforeach; ?>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>
    <?php foreach ($fieldErrors['password'] ?? [] as $msg): ?>
        <div class="field-error"><?= htmlspecialchars($msg, ENT_QUOTES) ?></div>
    <?php endforeach; ?>

    <button type="submit">Sign in</button>
</form>

<div class="nav">
    Don't have an account? <a href="register.php">Create one</a>
</div>

<?php render_page_end(); ?>
