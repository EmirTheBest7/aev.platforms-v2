# ALIEV.IO V2 — Authentication Module (`core/auth/`)

Developer documentation for using the authentication core from `api/`,
`apps/`, or any other part of the platform.

- [1. Overview](#1-overview)
- [2. Requirements](#2-requirements)
- [3. Database setup](#3-database-setup)
- [4. Bootstrapping `AuthFacade`](#4-bootstrapping-authfacade)
- [5. Registration](#5-registration)
- [6. Login](#6-login)
- [7. Logout](#7-logout)
- [8. Checking the current session](#8-checking-the-current-session)
- [9. CSRF protection](#9-csrf-protection)
- [10. Exceptions reference](#10-exceptions-reference)
- [11. `AuthenticatedUser` DTO reference](#11-authenticateduser-dto-reference)
- [12. Configuration reference (`AuthConfig`)](#12-configuration-reference-authconfig)
- [13. Using MySQL or PostgreSQL](#13-using-mysql-or-postgresql)
- [14. Extending the module](#14-extending-the-module)
- [15. Security notes](#15-security-notes)

---

## 1. Overview

`core/auth/` is a self-contained, framework-free authentication module. It
handles registration, login, logout, session management, brute-force
protection, CSRF token support, and audit logging.

**You should never call its internal classes directly.** Everything you
need is exposed through a single entry point: `Core\Auth\AuthFacade`.

```
core/auth/
├── AuthFacade.php          ← your only import in most cases
├── Config/AuthConfig.php
├── DTO/                    ← RegistrationData, LoginData, AuthenticatedUser
├── Exception/               ← typed exceptions, see §10
├── Value/                  ← Role, Permission value objects
└── ... (internal — see the module's own architecture notes)
```

`api/v2/auth/*.php` endpoints, `apps/*` pages, and any future internal
tooling all consume auth through `AuthFacade`.

---

## 2. Requirements

- PHP 8.3+
- PDO with either `pdo_mysql` or `pdo_pgsql` extension enabled
- A `users`, `login_attempts`, and `auth_audit_log` table (see §3)

---

## 3. Database setup

Reference schemas (not migrations — adapt into your migration tooling) live in:

- `database/reference/auth_schema.sql` — MySQL / MariaDB
- `database/reference/auth_schema.postgres.sql` — PostgreSQL

Both define three tables: `users`, `login_attempts`, `auth_audit_log`. Load
the one matching your engine before using the module.

---

## 4. Bootstrapping `AuthFacade`

`AuthFacade` needs one thing to get started: a `PDO` connection. Use the
built-in `PdoConnection` factory rather than constructing `PDO` yourself —
it sets safe defaults (exceptions on error, no emulated prepares, etc.).

### MySQL / MariaDB

```php
use Core\Auth\AuthFacade;
use Core\Auth\Database\PdoConnection;

$pdo = PdoConnection::forMysql(
    host: 'localhost',
    database: 'aliev_io',
    username: 'aliev_user',
    password: 'secret',
)->connect();

$auth = AuthFacade::create($pdo);
```

### PostgreSQL

```php
use Core\Auth\AuthFacade;
use Core\Auth\Database\PdoConnection;

$pdo = PdoConnection::forPostgres(
    host: 'localhost',
    database: 'aliev_io',
    username: 'aliev_user',
    password: 'secret',
)->connect();

$auth = AuthFacade::create($pdo);
```

`AuthFacade::create()` works identically for both — the underlying
repositories detect which SQL dialect to speak automatically from the PDO
driver, so your calling code never branches on engine.

### Custom configuration

Pass an `AuthConfig` instance as the second argument to override defaults
(password rules, session lifetime, brute-force thresholds — see §12):

```php
use Core\Auth\Config\AuthConfig;

$config = new AuthConfig(
    passwordMinLength: 12,
    maxFailedAttempts: 3,
);

$auth = AuthFacade::create($pdo, $config);
```

### Recommended: one shared instance per request

Build `$auth` once per request (e.g. in your bootstrap/index.php) and reuse
it — don't reconstruct `AuthFacade` multiple times in the same request.

---

## 5. Registration

```php
use Core\Auth\DTO\RegistrationData;
use Core\Auth\Exception\ValidationException;
use Core\Auth\Exception\DuplicateAccountException;

try {
    $user = $auth->register(
        data: new RegistrationData(
            email: $_POST['email'],
            username: $_POST['username'],
            password: $_POST['password'],
            passwordConfirmation: $_POST['password_confirmation'],
        ),
        ipAddress: $_SERVER['REMOTE_ADDR'],
        userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null,
    );

    // $user is an AuthenticatedUser DTO — see §11.
    // Registration does NOT automatically log the user in or start a
    // session. Call $auth->login(...) afterward if you want that.

} catch (ValidationException $e) {
    // $e->errors() → array<string, array<int, string>>
    // e.g. ['email' => ['Email format is invalid.']]
    foreach ($e->errors() as $field => $messages) {
        // render inline field errors
    }

} catch (DuplicateAccountException $e) {
    // $e->field()  → 'email' or 'username'
    // $e->value()  → the conflicting value
    echo $e->getMessage(); // safe, user-facing message
}
```

**Validation rules enforced** (see `Validation/RegistrationValidator.php`):
- Email: required, valid format, ≤254 chars
- Username: 3–32 chars, letters/numbers/dots/underscores/hyphens only
- Password: ≥10 chars (configurable), needs 1 uppercase, 1 lowercase, 1 digit
- Password confirmation must match exactly

---

## 6. Login

```php
use Core\Auth\DTO\LoginData;
use Core\Auth\Exception\ValidationException;
use Core\Auth\Exception\AccountLockedException;
use Core\Auth\Exception\InvalidCredentialsException;

try {
    $user = $auth->login(new LoginData(
        email: $_POST['email'],
        password: $_POST['password'],
        ipAddress: $_SERVER['REMOTE_ADDR'],
        userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null,
    ));

    // Success: session is already created (session_regenerate_id was
    // called internally), last_login_at was updated, and the audit log
    // recorded a 'login.success' event. $user is ready to use.

} catch (ValidationException $e) {
    // Malformed input (empty fields, bad email format) — NOT wrong
    // credentials. See $e->errors().

} catch (AccountLockedException $e) {
    // Too many recent failed attempts.
    $retryAfter = $e->retryAfterSeconds(); // int, seconds
    http_response_code(429);
    header("Retry-After: {$retryAfter}");

} catch (InvalidCredentialsException $e) {
    // Wrong email OR wrong password — deliberately indistinguishable to
    // prevent user enumeration. Show one generic message to the user.
    http_response_code(401);
}
```

**What happens internally on a successful login:**
1. Input validated (shape only).
2. Account lock status checked.
3. Password verified against the stored Argon2id hash.
4. If the stored hash uses outdated cost parameters, it's transparently
   re-hashed and updated (no action needed from you).
5. Brute-force counters reset; any existing lock is cleared.
6. `last_login_at` updated.
7. Session ID regenerated and user ID stored in session (fixation-safe).
8. `login.success` audit event recorded.

---

## 7. Logout

```php
$auth->logout(
    ipAddress: $_SERVER['REMOTE_ADDR'],
    userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null,
);

// Session data is cleared, the session cookie is expired, and
// session_destroy() is called. Safe to call even if no one is logged in.
```

---

## 8. Checking the current session

Use `$auth->session()` to check authentication state on any page/request
without going through login/logout:

```php
$session = $auth->session();

$session->start(); // idempotent — safe to call even if already started

if ($session->isAuthenticated()) {
    $userId = $session->currentUserId(); // int
    // Look up the full AuthenticatedUser if you need more than the ID —
    // see §14 for wiring a lookup service, or query UserRepository
    // directly if you've bootstrapped one.
} else {
    // redirect to login
}
```

`SessionManager` is the **only** place in the module that touches
`$_SESSION` — don't read/write `$_SESSION` directly elsewhere in the
platform if you can route through this instead, to avoid divergent session
key naming.

---

## 9. CSRF protection

`AuthFacade` exposes CSRF token generation/validation. This module does
**not** enforce CSRF automatically on any request — it's your endpoint's
responsibility to call `validate()` on state-changing requests (POST/PUT/
DELETE).

### Rendering a token in a form

```php
$auth->session()->start(); // CSRF token lives in the session

$token = $auth->csrf()->getToken();
```

```html
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">
```

### Validating on submit

```php
if (!$auth->csrf()->validate($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Invalid CSRF token.');
}
```

### Rotating the token

Optional — call after a successful state-changing request if you want a
fresh token per submission rather than a session-lifetime token:

```php
$auth->csrf()->rotateToken();
```

---

## 10. Exceptions reference

All exceptions extend `Core\Auth\Exception\AuthException`, so you can
catch that single type for a catch-all, or catch specific types for
precise handling. Suggested HTTP status mappings for API endpoints:

| Exception | Thrown by | Suggested HTTP status | Notes |
|---|---|---|---|
| `ValidationException` | `register()`, `login()` | 422 | `$e->errors()` → field-keyed message arrays |
| `DuplicateAccountException` | `register()` | 409 | `$e->field()`, `$e->value()` |
| `InvalidCredentialsException` | `login()` | 401 | Generic message by design (no enumeration) |
| `AccountLockedException` | `login()` | 429 | `$e->retryAfterSeconds()` |

```php
use Core\Auth\Exception\AuthException;

try {
    $auth->login($data);
} catch (AuthException $e) {
    // catch-all fallback if you don't need per-type handling
    log_error($e);
    http_response_code(400);
}
```

---

## 11. `AuthenticatedUser` DTO reference

Returned by `register()` and `login()`. Never contains the password hash.

```php
final readonly class AuthenticatedUser
{
    public int $id;
    public string $email;
    public string $username;
    public ?DateTimeImmutable $lastLoginAt;
    public DateTimeImmutable $createdAt;
    public array $roles;        // array<Role> — empty today, RBAC-ready
    public array $permissions;  // array<Permission> — empty today, RBAC-ready

    public function hasRole(Role|string $role): bool;
    public function hasPermission(Permission|string $permission): bool;
    public function isAdmin(): bool; // convenience: hasRole('admin')
    public function toArray(): array; // JSON-serializable shape
}
```

`toArray()` is the easiest way to hand this back from an API endpoint:

```php
echo json_encode($user->toArray());
```

```json
{
  "id": 42,
  "email": "user@example.com",
  "username": "jdoe",
  "last_login_at": "2026-08-05T10:15:00+00:00",
  "created_at": "2026-07-01T09:00:00+00:00",
  "roles": [],
  "permissions": []
}
```

> **Note:** `roles`/`permissions` are always empty arrays today — no RBAC
> engine exists yet. The shape is stable so integrating one later won't
> break existing callers. See §14.

---

## 12. Configuration reference (`AuthConfig`)

All fields are optional constructor parameters with sensible defaults.

| Parameter | Default | Purpose |
|---|---|---|
| `passwordMinLength` | 10 | Minimum password length |
| `passwordMaxLength` | 128 | Maximum password length |
| `argon2Memory` | 65536 (64 MB) | Argon2id memory cost (KiB) |
| `argon2Time` | 4 | Argon2id iteration count |
| `argon2Threads` | 2 | Argon2id parallelism |
| `usernameMinLength` | 3 | Minimum username length |
| `usernameMaxLength` | 32 | Maximum username length |
| `usernamePattern` | `/^[a-zA-Z0-9_.\-]+$/` | Allowed username characters |
| `maxFailedAttempts` | 5 | Failures before lockout |
| `bruteForceWindowSeconds` | 900 (15 min) | Window failures are counted within |
| `lockoutDurationSeconds` | 900 (15 min) | How long an account stays locked |
| `sessionName` | `aliev_session` | PHP session cookie name |
| `sessionLifetimeSeconds` | 3600 (1 hr) | Session cookie lifetime |
| `sessionCookieSecure` | `true` | Requires HTTPS — see note below |
| `sessionCookieHttpOnly` | `true` | Blocks JS access to the cookie |
| `sessionCookieSameSite` | `Lax` | CSRF-relevant cookie policy |
| `csrfTokenLength` | 32 (bytes) | CSRF token entropy |
| `tokenLength` | 32 (bytes) | General-purpose secure token entropy |

> **Local development note:** `sessionCookieSecure` defaults to `true`,
> which requires HTTPS. If testing over plain HTTP locally, pass
> `sessionCookieSecure: false` in your `AuthConfig` — **never** disable
> this in staging/production.

---

## 13. Using MySQL or PostgreSQL

Both are fully supported. The only thing that changes in your code is
which `PdoConnection` factory you call (§4) — everything downstream
(`AuthFacade`, services, DTOs, exceptions) is identical regardless of
engine.

Internally, `UserRepository` and `LoginAttemptRepository` detect the
active driver via `Core\Auth\Database\DatabaseDriver::fromPdo($pdo)` and
branch only the handful of genuinely engine-specific SQL fragments
(auto-increment retrieval, unix-timestamp conversion, interval arithmetic).
You never need to think about this as a consumer of `AuthFacade`.

---

## 14. Extending the module

**Adding roles/permissions:** `AuthenticatedUser` already has `roles` and
`permissions` properties typed as `array<Role>`/`array<Permission>` (see
`core/auth/Value/`). To wire in real data:
1. Add `roles`, `permissions`, `role_permissions`, `user_roles` tables
   (noted at the bottom of both reference schema files).
2. Extend `UserRepository::findAuthenticatedUserById()` to join/load them
   and populate the DTO.
3. No changes needed anywhere else — `hasRole()`, `hasPermission()`, and
   `isAdmin()` already work against whatever is populated.

**Adding password reset / remember-me tokens:** Reuse
`Core\Auth\Security\TokenGenerator` (already used internally for CSRF) —
it's a general-purpose secure random token generator, not CSRF-specific.

**Swapping storage entirely:** Every repository is bound to an interface
(`UserRepositoryInterface`, `LoginAttemptRepositoryInterface`,
`AuditLoggerInterface`). Implement the interface against a different store
and construct `AuthFacade` directly (not via `::create()`) passing your
custom implementation.

---

## 15. Security notes

- Passwords are hashed with **Argon2id** (`PASSWORD_ARGON2ID`), not bcrypt.
- All SQL uses prepared statements — no string interpolation of user input.
- `InvalidCredentialsException` is deliberately generic to prevent
  attackers from distinguishing "wrong password" from "no such account."
- Session IDs are regenerated on login (`session_regenerate_id(true)`) to
  prevent session fixation.
- CSRF protection is provided but **must be explicitly wired** into your
  endpoints — it is not automatic.
- Brute-force lockout is per-email, tracked via the `login_attempts` table,
  not an in-memory counter — it survives process restarts and works
  correctly behind multiple app servers.
