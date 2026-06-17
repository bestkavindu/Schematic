# Schematic — Feature Recommendations

> Research-backed, ranked shortlist of features to add to Schematic, the visual database schema builder for Laravel. Every pick is grounded in the actual source and checked against competitor tools (dbdiagram, drawSQL, QuickDBD, ChartDB, Azimutt, dbdocs, Prisma, Blueprint).

## Where the product stands

**Stack:** Laravel 13 · Livewire 4 · Flux · Fortify auth · Alpine.js canvas (`resources/js/schematic.js`) + SVG relationship lines · Tailwind v4 · Vite · JSZip client-side export · Pest tests.

**Already shipped:** infinite canvas (add/drag/duplicate/delete tables, pan, zoom, zoom-to-fit, auto-arrange, multi-select, dot grid, 8 palettes), 14 Laravel column types with nullable/unique/index/pk + default, crow's-foot relationships (1:N / 1:1 with ON DELETE/UPDATE), one-click pivot generator, cosmetic groups, SQL export, Laravel migration + Eloquent model ZIP export, SQL import, multi-project dashboard, favorites, recents, per-owner access control, Fortify auth with 2FA + passkeys.

### Two architectural facts that drive every recommendation
- **All export / import / share logic is client JS.** `payload()` (`resources/js/schematic.js:896`) already produces a complete lossless serialization. `exportSql()`, `exportMigration()`, `_modelFile()`, `_exportZip()`, `parseSqlSchema()` already exist → new formats / diff / AI-target work is "extend existing JS", near-zero infra.
- **`Builder::save()` (`app/Livewire/Schema/Builder.php:82`) is a wholesale-replace in one transaction** with a validated JSON payload → snapshots + structured diffing are nearly free, and diff→migration is a moat rivals structurally can't match. **No Reverb/broadcasting exists** → real-time collaboration is the only genuinely expensive pick.

## Verified gaps between marketing and the real code
Checked against the actual source, not assumed:

| Claim | Reality |
|-------|---------|
| "SQL export for MySQL / Postgres / SQLite" | `exportSql()` (`schematic.js:962`) hard-codes MySQL backticks + `AUTO_INCREMENT` + one type map. **Postgres & SQLite don't exist.** |
| "Share links" | `share()` (`schematic.js:928`) copies `window.location.href` — the owner-only URL; recipients hit `abort_unless(...403)` (`Builder.php:23`). **No-op.** |
| "Schema diffing & migration preview (NEW)" / "real-time collaboration" / "comments" / "history" | Advertised on the home page and Team plan. **None exist.** |
| Composite indexes | `tables.indexes` JSON round-trips through payload, save, validation, and `cardHeight()` reserves space — but **no UI ever writes it.** Half-built. |
| "40+ column types" | Only **14** exist. `enum` chip is shown on the landing page but isn't a real type. |

---

## Top picks — ranked by value-per-effort

| # | Feature | What it does | Impact | Effort | Fulfills a promise? |
|---|---------|--------------|--------|--------|----------------------|
| 1 | **Multi-dialect SQL export (Postgres + SQLite)** | Dialect picker; correct PG/SQLite DDL beside MySQL | High | S | Yes — fixes a literal lie |
| 2 | **Native JSON export + import** | Download exact `payload()`; re-import losslessly (positions, colors, groups, FK meta) | High | S | Yes (backup) |
| 3 | **DBML export** | dbdiagram-compatible `Table`/`Ref`/`TableGroup` — switching wedge | High | M | Yes |
| 4 | **Mermaid `erDiagram` export + Copy** | GitHub/GitLab-native ER block, paste into a PR/README | High | S | No (new value) |
| 5 | **Composite index + unique UI** | Editor section for named multi-col indexes/uniques, emitted in SQL + migration | High | M | Yes (explicit gap) |
| 6 | **Token-based read-only share links** | Non-guessable token opens a locked, account-free canvas | High | M | Yes — fixes `share()` |
| 7 | **Factories + seeders in ZIP** | Faker-mapped `*Factory.php` + optional seeder, toggleable | Med | M | No (Blueprint parity) |
| 8 | **Version snapshots + visual diff** | Snapshot each save; "what changed since X" | High | L | Yes (home page) |
| 9 | **Diff-to-migration (ALTER + `down()`)** | Diff → FK-ordered incremental Laravel migration with inverse `down()` | High | M (needs #8) | Yes (home page "NEW") |
| 10 | **AI text-to-schema (BYO-key, diff-first)** | Prompt → payload-shaped schema, previewed as a diff before it lands | High | M | No (zero AI today) |
| 11 | **Reverse-engineer Laravel migrations** | Parse migration files → canvas | Med | L | No (moat) |
| 12 | **Schema linter** | Flags missing FKs, no PK, missing timestamps, unindexed FKs, naming | Med | M | No |
| 13 | **Live-DB introspection import** | Paste `information_schema` result → real schema; no credentials leave the machine | Med | M | No |
| 14 | **Prisma export/import** | Round-trip `schema.prisma` | Med | M | Partially |
| 15 | **Presence cursors + comments (Reverb)** | Live cursors + resolvable comment threads on tables/cols/rels | High | L | Yes (home page) |

Effort key: **S** = a day or less · **M** = a few days · **L** = ~1–2 weeks.

---

## Roadmap

### Ship this week — quick wins, pure client JS, zero infra
All in `resources/js/schematic.js` + a few menu items in `resources/views/livewire/schema/builder.blade.php`. No migrations, no backend.
- **#1 Multi-dialect SQL** — makes the MySQL/PG/SQLite claim true (S)
- **#2 JSON export + import** — lossless backup; `payload()` shape already exists (S)
- **#4 Mermaid + copy** — frictionless share-to-PR (S)
- **#3 DBML export** — the dbdiagram-ecosystem switching wedge (M)

**Outcome:** export breadth goes from "MySQL only" to "MySQL / Postgres / SQLite / JSON / DBML / Mermaid", and the SQL-dialect lie is gone.

### Next — medium, fits the stack, ≤1 migration + 1 Livewire method
- **#6 Read-only token share links** — replaces fake `share()`; unblocks the stubbed "Shared" tab; prerequisite for comments (M)
- **#5 Composite index UI** — finishes the stranded half-built feature (M)
- **#7 Factories + seeders** — Blueprint's most-loved capability, incremental on `_exportZip()` (M)
- **#8 → #9 Versioning → diff → incremental migration** — the strategic spine; #9 (the moat) depends only on #8 (L then M)

### Big bets — differentiating, needs infra / external dep
- **#10 AI text-to-schema** — API-key storage + optional queue, no websockets; marquee paid hook, diff-before-apply + schema-only privacy (M)
- **#15 Reverb presence + comments** — the **only** pick needing net-new websocket infra; scope v1 to presence + comments, defer CRDT co-editing (L, high risk)
- **#11 reverse-engineer migrations / #13 live-DB introspection** — close the "diagram isn't the database" complaint; pursue after the diff/migration spine

---

## #1 recommendation

> **Ship the multi-format export pack (#1–4) this week, then build the versioning → diff → incremental-migration spine (#8 → #9).**

**Why this, not collaboration or AI:**

1. **It fixes an outright falsehood for almost no cost.** Postgres/SQLite export — claimed in marketing — literally don't exist (`exportSql()` is MySQL-only). Adding them is an S-effort refactor of one function. Export breadth is the most-expected, most-cited churn-killer across every competitor ("can't download my schema" is drawSQL's top complaint; formats are universally free). `payload()` makes JSON round-trip nearly free; the in-memory model maps cleanly to DBML/Mermaid. Days of work that close the widest, cheapest gap and make the product honest.

2. **Then versioning/diff/migration is Schematic's only structurally-defensible moat.** `Builder::save()` is already a wholesale snapshot boundary, so capture is one table + a JSON column. Schema-aware diffing is the #1 unmet complaint across the market (Figma's most-upvoted multi-year request; dbdocs paywalls retention). And because the app **already generates full CREATE-TABLE migrations** (`exportMigration()`), extending that to incremental ALTER-from-diff with a generated `down()` is something dbdiagram/drawSQL/QuickDBD **cannot** match — they don't model incremental state. It fulfills the "schema diffing & history" + "migration preview (NEW)" home-page promises and hits Laravel devs' top migration pains (hand-written `down()`, FK-order errors that drive data-destroying `migrate:fresh`).

This sequence front-loads broad, honest, near-free wins, then spends the first real engineering on the one feature no competitor can structurally copy — deferring the only infra-heavy pick (Reverb collaboration) until the moat is shipped.

---

## Verification note
Whichever picks get greenlit, the existing Pest suite (`tests/Feature/Schema`) is the verification surface. New Feature tests must add `RefreshDatabase` or they fail on the in-memory SQLite driver.
