> **Historical record — not current scope.** Written for the original ALIEV.IO ecosystem / earlier phases of the rebuild. The current project is the digital-studio site described in [`../ARCHITECTURE.md`](../ARCHITECTURE.md). Kept for context; do not treat anything here as a requirement.

# Legacy ledger — what was *not* carried into v2, and why

**Nothing has been deleted.** `aev.platforms-master` is untouched and remains the historical reference. This ledger replaces the earlier "removal record": its job is to make it impossible to lose track of anything. Default status is **KEEP**; deletion needs the owner's explicit approval, and is only proposed for items that are accidental, proven duplicate, malicious, or hold sensitive material.

## A. Never to be carried (sensitive or dangerous) — the *feature* stays, the *artefact* doesn't

| Item | Why it must not be copied | What carries on instead |
|---|---|---|
| Credentials in `_inc/functions.php`, `home/2be_deleted/messenger/…`, `home/_api/UI/terminal/Page/valentine/yes_page.php`, `page/hester/script.js` (`SECRET_EXPOSURE_01–08`) | Public in a public repo; compromised | Environment configuration; owner rotates/revokes |
| Hard-coded list of private individuals' names + birthdays in `cron.php` (`PII_EXPOSURE_01`) | Personal data of third parties | Birthday notifications for **registered, consenting users** (`users.birth`) |
| `home/_uploads/user_*` contents: SQLite databases, face-API demo and models, hidden admin page (`PII_EXPOSURE_02`) | User/biometric-adjacent data and private projects | The per-user hosting **concept** with a defined storage layout in v2 |
| `eval` helpers (`file_get_module()`, calculator `eval`) | Arbitrary code execution | Explicit templates; safe arithmetic parser |
| `web4ukraine` third-party script | Lets a remote server redirect visitors (`THIRD_PARTY_RISK_01`) | #StopTheWar card + 4ukraine page self-hosted; owner may supply a static banner |
| Intergram's hidden affiliate-tracker iframe | Tracking/ads, `allow-same-origin` sandbox | Chat kept with `disableLoadmill: true` |

## B. Not carried *yet* (preserved in the legacy tree; scheduled in `MIGRATION.md`)

| Item | State |
|---|---|
| `home/*` apps (auth, Space, profile, Studio, Messenger, Videos, Wallet, Store) | Rebuild on `core/auth` in the owner's order |
| `home/_api/{UI,Docs}` terminal, tools, games, docs app, site error pages | Port; error pages already have v2 equivalents |
| `home/2be_created/*` (music, blog, re:search, settings, hiring/projects/team) | Planned features — complete when scheduled |
| `page/{careers,downloads,updates,investor-relations,services,hester,maps,design_store,DC25,qirimcz,material,universal,history,legal,empty}` | Port page by page |
| Ndot-55, DotlineBold, SF Pro font files | Kept in the legacy tree; owner decides licensing/use (`DESIGN-SYSTEM.md`) |
| `_assets/js/{core-gpu,origElectron,aev-video}.js`, `_assets/css/{header,aev-video}.css`, `_assets/cdn/*`, `_inc/helpers/*`, `_inc/lang/*` | Experiments / utilities / i18n placeholder — keep; wire when needed |
| `.htaccess` UA blocklist | Behaviour is superseded by rate limiting; harmless to keep as reference |

## C. Possible deprecations (owner decision; nothing is acted on)

| Item | Reason for the flag | Counter-evidence |
|---|---|---|
| `home/2be_deleted/*` (Pyper, WebOS admin, dashboard concept, old messenger/studio/dailies/mail, tesco, backups, zuck) | The owner labelled the folder "to be deleted" | They document earlier designs of features that live on (messenger, stories); one holds a secret → that single file is class A |
| `_assets/js/origElectron.js` | `DUPLICATE`: same pixel-grid animation exists inside `core.js` | Different version — compare before deciding |
| `_assets/icon/season2` | Never selected by the icon rule | May be a deliberate unused variant |
| `page/universal`, `page/material` | Template/demo pages with no inbound links | May be early Liquid-Glass/UI experiments |
| `.DS_Store` ×7 | macOS metadata | Plainly accidental — the only item safe to omit now (v2 ignores them) |

## D. Replaced implementation, same capability

| Legacy | v2 | Behaviour kept |
|---|---|---|
| `notify()` (token in URL, blocking, no timeout) | `Notifier` (`TelegramNotifier`/`LogNotifier`) | Message to the owner on every request/lead |
| Order form → text with `ddmmyy_1` | `/contact` (and `/hire`) lead flow with server reference | Same fields and purpose |
| `adminOnly()` token pairs | `core/auth` roles/permissions | Admin gate |
| `random_str()` | ported unchanged (CSPRNG) | — |
| `.htaccess` HTTPS/fbclid/`@nick`/error pages | app + server config (`@nick` returns with profiles) | HTTPS, clean URLs, branded errors |
| Seasonal icon `include` | `SeasonalIcons` | Same rule/dates |
