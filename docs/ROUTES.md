# Routes

All routes live in `routes/web.php` (current) and `routes/legacy.php` (historical URLs). Application routes have no trailing slash; the static `_api` bundle keeps it. Unknown paths answer 404 (never 410).

| Method · Path | Controller | Purpose |
|---|---|---|
| GET `/` | `HomeController::home` | Main page |
| POST `/hire` | `HireController::submit` | Work request from the main page (JSON or redirect) |
| GET `/careers` | `CareersController::index` | Job list |
| GET `/careers/team` | `CareersController::team` | Team page (exact route wins over the slug route) |
| GET `/careers/{slug}` | `CareersController::show` | One posting; slug `[A-Za-z0-9_-]{1,32}`, else 404 |
| GET `/privacy`, `/imprint` | `LegalController` | Privacy Policy / Imprint — placeholders until `config/legal.php` + `LEGAL_*` are filled (`docs/LEGAL.md`) |
| GET/POST `/contact` | `ContactController` | Page + form |
| GET `/downloads` | `DownloadsController::index` | Logos, wallpaper maker, documents (`config/downloads.php`) |
| GET `/home/auth` | `AuthController::show` | Log In / Sign Up (404 unless `AUTH_ENABLED=true`) |
| POST `/home/auth/{login,register,logout}` | `AuthController` | Form actions (CSRF, rate limit) |
| GET `/home/auth/reset` | `AuthController::reset` | Honest "not available yet" page |
| GET `/api/prices` | `ApiController::prices` | Ticker snapshot |
| GET `/home/_api/` | `ApiController::root` | 302 → `/home/_api/UI/` |
| GET `/home/_api/{hello,info,me,updates,csrf}` | `ApiController` | JSON allow-list (no CORS, no-store) |
| GET `/home/_api/tools/domain?name=` | `ApiController::domain` | DNS-records check, rate limited |
| POST `/home/_api/valentine/yes` | `ApiController::valentineYes` | Server-side notification (CSRF header, rate limited) |
| GET `/widgets/{clock,calculator}` | `WidgetController` | Mini-apps framed by the main page |

Static (Apache, no PHP): `/build/…`, `/downloads/…` files, `/downloads/wallpapers/create/`, `/home/_api/UI/`, `/home/_api/UI/terminal/…`, `/home/_api/Docs/`, `/manifest.webmanifest`. A directory that ships an `index.html` is served at its trailing-slash URL; other directories belong to the application.

Historical URLs → see `URL-MIGRATION.md`.
