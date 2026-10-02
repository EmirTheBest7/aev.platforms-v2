# Historical URL map

Single-hop 301s in `routes/legacy.php`. Paths of removed products are an honest 404.

| Historical URL | Now |
|---|---|
| `/page/main/` | `/` |
| `/page/contact/` | `/contact` |
| `/page/downloads/` | `/downloads` |
| `/page/careers/`, `/page/careers/list/` | `/careers` |
| `/page/careers/team/` | `/careers/team` |
| `/page/careers/desc/?job_url=<slug>` | `/careers/<slug>` (well-formed slugs only, else `/careers`) |
| `/home/auth/` | `/home/auth` (canonical slash rule) |
| `/home/_api/…` | unchanged (static bundle + allow-listed endpoints) |
| `/home/_api/index.php/<function>/…` (dynamic dispatch) | **gone by design**: unknown paths 404 |
| `/page/hester/`, `/home/timeline/`, `/home/messenger/`, store, games, … | 404 (removed products) |
