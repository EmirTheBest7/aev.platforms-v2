<?php

declare(strict_types=1);

/**
 * apps/account/index.php
 *
 * Testing/demo page showing session state after login, and how to look up
 * the full AuthenticatedUser DTO from a session-stored user ID.
 */

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_layout.php';

use Core\Auth\Repository\UserRepository;

if (!$auth->session()->isAuthenticated()) {
    header('Location: login.php');
    exit;
}

// The session only stores the user ID (see Core\Auth\Session\SessionManager).
// Look up the full AuthenticatedUser DTO when you need more than that —
// here we construct UserRepository directly for demo purposes; a real app
// would likely expose a small "current user" lookup helper alongside
// AuthFacade rather than reaching into Repository/ directly.
$userRepository = new UserRepository($pdo);
$user = $userRepository->findAuthenticatedUserById($auth->session()->currentUserId());

render_page_start('Account');
?>

<h1>Welcome back<?= $user !== null ? ', ' . htmlspecialchars($user->username, ENT_QUOTES) : '' ?></h1>
<p class="subtitle">ALIEV.IO V2 — auth module test page</p>

<?php if ($user !== null): ?>
    <div class="user-info">
        <p><strong>ID:</strong> <code><?= $user->id ?></code></p>
        <p><strong>Email:</strong> <code><?= htmlspecialchars($user->email, ENT_QUOTES) ?></code></p>
        <p><strong>Username:</strong> <code><?= htmlspecialchars($user->username, ENT_QUOTES) ?></code></p>
        <p><strong>Created:</strong> <code><?= $user->createdAt->format('Y-m-d H:i:s') ?></code></p>
        <p><strong>Last login:</strong> <code><?= $user->lastLoginAt?->format('Y-m-d H:i:s') ?? 'this is your first login' ?></code></p>
        <p><strong>Admin:</strong> <code><?= $user->isAdmin() ? 'yes' : 'no' ?></code></p>
    </div>
<?php else: ?>
    <?php render_alert('error', 'Session references a user that no longer exists.'); ?>
<?php endif; ?>

<form method="post" action="logout.php">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($auth->csrf()->getToken(), ENT_QUOTES) ?>">
    <button type="submit">Sign out</button>
</form>

<?php render_page_end(); ?>
