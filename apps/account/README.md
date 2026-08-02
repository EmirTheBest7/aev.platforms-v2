# `apps/account/` — Auth Module Test Pages

Minimal, dependency-free PHP pages for manually testing `core/auth/`
end-to-end (registration → login → session → logout). **Not a production
UI** — a reference implementation showing how any real page/endpoint would
call into `AuthFacade`. See `docs/AUTH_MODULE.md` for full API docs.

## Files

| File | Purpose |
|---|---|
| `_bootstrap.php` | Configures the DB connection + `AuthFacade`. Edit the constants at the top. |
| `_layout.php` | Shared HTML chrome (no framework — just two helper functions). |
| `register.php` | Registration form → `$auth->register()` |
| `login.php` | Login form → `$auth->login()` |
| `index.php` | Authenticated landing page, shows the `AuthenticatedUser` DTO |
| `logout.php` | POST-only logout handler → `$auth->logout()` |

## Running it locally

### 1. Create the database and load the schema

MySQL/MariaDB:
```bash
mysql -u root -p -e "CREATE DATABASE aliev_io CHARACTER SET utf8mb4"
mysql -u root -p aliev_io < database/reference/auth_schema.sql
```

PostgreSQL:
```bash
createdb aliev_io
psql -d aliev_io -f database/reference/auth_schema.postgres.sql
```

### 2. Configure the connection

Edit the constants at the top of `apps/account/_bootstrap.php`:

```php
const DB_DRIVER   = 'mysql'; // or 'pgsql'
const DB_HOST     = '127.0.0.1';
const DB_DATABASE = 'aliev_io';
const DB_USERNAME = 'your_username';
const DB_PASSWORD = 'your_password';
```

### 3. Serve it with PHP's built-in server

From the repository root:

```bash
php -S localhost:8000
```

Then visit:
- `http://localhost:8000/apps/account/register.php`
- `http://localhost:8000/apps/account/login.php`

## What this exercises

- **Registration:** validation errors, duplicate email/username handling,
  Argon2id password hashing.
- **Login:** credential verification, session creation with ID
  regeneration, `last_login_at` tracking, generic invalid-credentials
  messaging (no user enumeration).
- **Brute force protection:** try logging in with a wrong password 5 times
  in a row (default `maxFailedAttempts`) — the 6th attempt returns an
  `AccountLockedException` with a retry-after time.
- **CSRF protection:** every form includes and validates a CSRF token via
  `$auth->csrf()`.
- **Logout:** full session destruction.

## Security note

`_bootstrap.php` hardcodes database credentials and disables
`sessionCookieSecure` for local HTTP testing. **This is intentional for a
local test app and must never be deployed as-is.** In a real endpoint
(e.g. `api/v2/auth/`), configuration should come from environment
variables and `sessionCookieSecure` should remain `true` behind HTTPS.
