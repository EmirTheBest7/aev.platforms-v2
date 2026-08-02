<?php

declare(strict_types=1);

/**
 * apps/account/register.php
 *
 * Testing/demo page exercising Core\Auth\AuthFacade::register().
 * Not a production UI — a minimal reference implementation showing how a
 * real endpoint (e.g. api/v2/auth/register.php) would call into core/auth/.
 */

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_layout.php';

use Core\Auth\DTO\RegistrationData;
use Core\Auth\Exception\DuplicateAccountException;
use Core\Auth\Exception\ValidationException;

/** @var array<string, array<int, string>> $fieldErrors */
$fieldErrors = [];
$generalError = null;
$successMessage = null;

// If already logged in, there's nothing to register for.
if ($auth->session()->isAuthenticated()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check — see docs/AUTH_MODULE.md §9.
    if (!$auth->csrf()->validate($_POST['csrf_token'] ?? null)) {
        $generalError = 'Your form session expired. Please try again.';
    } else {
        try {
            $user = $auth->register(
                data: new RegistrationData(
                    email: (string) ($_POST['email'] ?? ''),
                    username: (string) ($_POST['username'] ?? ''),
                    password: (string) ($_POST['password'] ?? ''),
                    passwordConfirmation: (string) ($_POST['password_confirmation'] ?? ''),
                ),
                ipAddress: $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null,
            );

            // Registration succeeded but does NOT auto-login — that's a
            // deliberate separation of concerns in the service layer.
            // Redirect to the login page with a success flag.
            header('Location: login.php?registered=1');
            exit;

        } catch (ValidationException $e) {
            $fieldErrors = $e->errors();
        } catch (DuplicateAccountException $e) {
            $fieldErrors[$e->field()] = [$e->getMessage()];
        }
    }
}

$csrfToken = $auth->csrf()->getToken();

render_page_start('Register');
?>

<h1>Create an account</h1>
<p class="subtitle">ALIEV.IO V2 — auth module test page</p>

<?php if ($generalError !== null): ?>
    <?php render_alert('error', $generalError); ?>
<?php endif; ?>

<form method="post" action="register.php" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>" required>
    <?php foreach ($fieldErrors['email'] ?? [] as $msg): ?>
        <div class="field-error"><?= htmlspecialchars($msg, ENT_QUOTES) ?></div>
    <?php endforeach; ?>

    <label for="username">Username</label>
    <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES) ?>" required>
    <?php foreach ($fieldErrors['username'] ?? [] as $msg): ?>
        <div class="field-error"><?= htmlspecialchars($msg, ENT_QUOTES) ?></div>
    <?php endforeach; ?>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>
    <?php foreach ($fieldErrors['password'] ?? [] as $msg): ?>
        <div class="field-error"><?= htmlspecialchars($msg, ENT_QUOTES) ?></div>
    <?php endforeach; ?>

    <label for="password_confirmation">Confirm password</label>
    <input type="password" id="password_confirmation" name="password_confirmation" required>
    <?php foreach ($fieldErrors['password_confirmation'] ?? [] as $msg): ?>
        <div class="field-error"><?= htmlspecialchars($msg, ENT_QUOTES) ?></div>
    <?php endforeach; ?>

    <button type="submit">Create account</button>
</form>

<div class="nav">
    Already have an account? <a href="login.php">Sign in</a>
</div>

<?php render_page_end(); ?>
