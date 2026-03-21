# MENSA Tech Agency

> **Uganda's Premier Tech Agency** — Enterprise web hosting, software development, system administration, and networking.
> Architected by **Wampamba Festo**, Lead Software Engineer & Architect — Makerere University 2023/2024.

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Final Tech Stack](#2-final-tech-stack)
3. [Architecture](#3-architecture)
4. [Directory Structure](#4-directory-structure)
5. [Database Schema](#5-database-schema)
6. [Local Development Setup](#6-local-development-setup)
7. [Cloud Production Deployment](#7-cloud-production-deployment)
8. [UI/UX & Frontend](#8-uiux--frontend)
9. [Contact Form System](#9-contact-form-system)
10. [Digital Job Card Dashboard](#10-digital-job-card-dashboard)
11. [Major Milestones & Architectural Decisions](#11-major-milestones--architectural-decisions)
12. [Critical Fixes & Troubleshooting Log](#12-critical-fixes--troubleshooting-log)
13. [Team Members](#13-team-members)
14. [Branch Strategy](#14-branch-strategy)

---

## 1. Project Overview

MENSA Tech Agency is a full-stack web application and DevOps project built by a 9-member team of Computer Science students at Makerere University. The project demonstrates a production-ready, decoupled cloud architecture transitioning seamlessly from a local Docker environment to a live cloud deployment on Render + Aiven.

**Live pages:**

| Page | URL | Description |
| --- | --- | --- |
| Homepage | `/index.php` | Hero, services preview, about section, CTA |
| Services | `/services.php` | Full service detail pages with feature lists |
| Team | `/team.php` | Live team roster fetched from MySQL via PDO |
| Contact | `/contact.php` | Client enquiry form with DB persistence |
| Job Cards | `/requests.php` | Internal dashboard — view and triage client submissions |

---

## 2. Final Tech Stack

| Layer | Technology | Notes |
| --- | --- | --- |
| **Web Server** | Apache 2.4 | Custom Debian-based Docker image |
| **Language** | PHP 8.2 | `strict_types`, PDO, `htmlspecialchars` everywhere |
| **Database** | MySQL 8.0 | `utf8mb4_unicode_ci`, `InnoDB` |
| **Local DNS** | Bind9 | Resolves `bbcmensa.com` → `172.28.0.10` locally |
| **Orchestration** | Docker & Docker Compose | 3-service stack: web, db, dns |
| **Cloud — Web** | Render (Free Web Service) | Auto-deploys from `main` branch via `render.yaml` |
| **Cloud — Database** | Aiven (Free Cloud MySQL) | Hybrid cloud pivot; SSL-enforced endpoint |
| **IaC** | `render.yaml` | Blueprint with `sync: false` for secrets |
| **Version Control** | Git & GitHub | Conventional Commits, `development` → `main` PR flow |

---

## 3. Architecture

### Local Network (`172.28.0.0/24`)

```text
                    ┌─────────────────────────────┐
  Browser           │       Docker Network         │
  bbcmensa.com ───▶ │  mensa_dns  172.28.0.53      │
                    │       ↓ DNS resolution        │
                    │  mensa_web  172.28.0.10:80    │
                    │       ↓ PDO / MySQL           │
                    │  mensa_db   172.28.0.20:3306  │
                    └─────────────────────────────┘
```

### Cloud (Hybrid — Two Providers)

```text
  User ──▶ Render (mensa-web Docker container)
                ↓ DB_HOST / DB_PORT / DB_NAME / DB_USER / DB_PASS
           Aiven Cloud MySQL (European region)
           mensa-db-free-webserver-db1.i.aivencloud.com:PORT
```

The hybrid split was an intentional architectural decision: Render's private database tier requires a paid plan. Aiven provides a free managed MySQL instance, and the two are connected over the public internet using Aiven's SSL-enforced endpoint.

---

## 4. Directory Structure

```text
mensa-project/
├── Dockerfile                  # Custom php:8.2-apache image
├── docker-compose.yml          # 3-service orchestration (web, db, dns)
├── render.yaml                 # Render.com IaC blueprint (free tier)
├── .gitignore
├── README.md
│
├── apache/
│   ├── mensa.conf              # VirtualHost: bbcmensa.com, blocks db_connect.php
│   └── php.ini                 # display_errors, expose_php off, Africa/Kampala TZ
│
├── bind9/
│   ├── named.conf.local        # Forward + reverse zone declarations
│   ├── named.conf.options      # Resolver options
│   └── zones/
│       ├── db.bbcmensa.com     # A / NS / CNAME / MX / TXT records
│       └── db.172.28.0         # Reverse PTR zone
│
├── mysql/
│   ├── Dockerfile              # mysql:8.0 with init.sql baked in
│   ├── init.sql                # Creates tables, seeds 9 members + 4 services
│   └── fix_aiven.sql           # One-time patch: fixes emoji icons + real phone numbers in Aiven
│
└── www/
    ├── db_connect.php          # PDO singleton — reads env vars, never exposes credentials
    ├── index.php               # Homepage
    ├── services.php            # Services detail page
    ├── team.php                # Team roster (live DB query)
    ├── contact.php             # Client contact form (GET display + POST fallback)
    ├── submit_contact.php      # AJAX JSON handler — validates, rate-limits, persists to DB
    ├── requests.php            # Internal Job Card dashboard — view & triage submissions
    └── assets/
        └── css/
            └── style.css       # Editorial dark theme — gold accent, Playfair Display
```

---

## 5. Database Schema

### `mensa_db.team_members`

| Column | Type | Notes |
| --- | --- | --- |
| `id` | INT UNSIGNED PK | Auto-increment |
| `full_name` | VARCHAR(120) | Required |
| `role` | VARCHAR(150) | e.g. "Lead Software Engineer" |
| `department` | VARCHAR(100) | Default: 'Engineering' |
| `email` | VARCHAR(180) UNIQUE | |
| `phone` | VARCHAR(30) | Real Ugandan (+256) numbers |
| `reg_number` | VARCHAR(60) | University registration number |
| `student_no` | VARCHAR(30) | University student number |
| `bio` | TEXT | |
| `is_lead` | TINYINT(1) | 1 = Lead, 0 = Member |
| `joined_date` | DATE | |
| `created_at` | TIMESTAMP | Auto |
| `updated_at` | TIMESTAMP | Auto on update |

### `mensa_db.services`

| Column | Type | Notes |
| --- | --- | --- |
| `id` | INT UNSIGNED PK | |
| `title` | VARCHAR(120) | Service name |
| `slug` | VARCHAR(120) UNIQUE | URL-friendly key — used for icon mapping |
| `icon` | VARCHAR(60) | Stored emoji (may be garbled in Aiven — see §12) |
| `description` | TEXT | Full service description |
| `features` | JSON | Array of feature strings |
| `sort_order` | TINYINT | Display order |
| `created_at` | TIMESTAMP | |

### `mensa_db.contact_submissions`

| Column | Type | Notes |
| --- | --- | --- |
| `id` | INT UNSIGNED PK | |
| `full_name` | VARCHAR(120) | Required |
| `email` | VARCHAR(180) | Required, validated |
| `company` | VARCHAR(120) | Optional |
| `phone` | VARCHAR(30) | Optional |
| `service` | VARCHAR(80) | Selected service |
| `budget` | VARCHAR(60) | Budget range (optional) |
| `message` | TEXT | Project details |
| `status` | ENUM | `new` / `read` / `replied` |
| `ip_address` | VARCHAR(45) | For soft rate-limiting |
| `created_at` | TIMESTAMP | Auto |
| `updated_at` | TIMESTAMP | Auto on update |

**Indexes:** `idx_status`, `idx_service`, `idx_created`, `idx_ip`

---

## 6. Local Development Setup

### Prerequisites

- Docker Desktop (or Docker Engine + Compose plugin)
- Git

### Steps

```bash
# 1. Clone the repository
git clone https://github.com/Festo-Wampamba/mensa-project.git
cd mensa-project

# 2. (First run only) Fix file permissions for MySQL init script
chmod 644 mysql/init.sql

# 3. Start all services
docker compose up -d --build

# 4. Verify containers are healthy
docker compose ps

# 5. Configure local DNS (Linux)
# Add to /etc/resolv.conf (or your OS DNS settings):
#   nameserver 127.0.0.1
# Then test:
dig @127.0.0.1 bbcmensa.com

# 6. Open in browser
http://bbcmensa.com     # (with local DNS configured)
# OR
http://localhost        # (direct)
```

### Reset / Re-seed the Database

```bash
# Wipe volumes and re-run init.sql from scratch
docker compose down -v
docker compose up -d
```

### View Logs

```bash
docker compose logs -f web     # Apache + PHP errors
docker compose logs -f db      # MySQL boot / seed output
docker compose logs -f dns     # Bind9 query log
```

### Inspecting contact_submissions Locally

Run SQL queries directly against the local MySQL container without needing any external tool:

```bash
# Tabular view — all columns, one row per line
docker exec -it mensa_db mysql -u root -pmensa26 mensa_db \
  -e "SELECT * FROM contact_submissions;"

# Vertical view — each column on its own line (great for long messages)
docker exec -it mensa_db mysql -u root -pmensa26 mensa_db \
  -e "SELECT * FROM contact_submissions \G"
```

### Environment Variables (local)

Injected by Docker Compose `environment:` block in `docker-compose.yml`:

| Variable | Local Value |
| --- | --- |
| `DB_HOST` | `db` (container name) |
| `DB_PORT` | `3306` |
| `DB_NAME` | `mensa_db` |
| `DB_USER` | `mensa_user` |
| `DB_PASS` | `mensa26` |

---

## 7. Cloud Production Deployment

### Render (Web Tier)

1. Push to `main` on GitHub.
2. Render picks up `render.yaml` automatically.
3. Builds the Docker image from `Dockerfile`.
4. **Environment Variables** — set manually in the Render dashboard (never in `render.yaml`):

| Key | Source |
| --- | --- |
| `DB_HOST` | Aiven service hostname |
| `DB_PORT` | Aiven service port |
| `DB_NAME` | `defaultdb` (Aiven default) |
| `DB_USER` | `avnadmin` |
| `DB_PASS` | Aiven password (Render encrypted env vars) |

### Aiven (Database Tier)

- Service: Free Cloud MySQL
- Region: Europe (GCP)
- Hostname format: `mensa-db-free-webserver-db1.i.aivencloud.com`
  *(note the `.i.` sub-subdomain — critical; omitting it causes "Unknown host" — see §12)*
- SSL enforced by Aiven by default.

### Seeding Aiven (one-time)

```bash
# Use an ephemeral Docker MySQL client — avoids installing MySQL locally
# --default-character-set=utf8mb4 is critical to prevent emoji corruption
docker run -i --rm mysql:8.0 mysql \
  --default-character-set=utf8mb4 \
  -h mensa-db-free-webserver-db1.i.aivencloud.com \
  -P <PORT> -u avnadmin -p"<PASSWORD>" \
  defaultdb < mysql/init.sql
```

### Patching Live Data (emoji icons + phone numbers)

```bash
# Run fix_aiven.sql against the live Aiven instance
docker run -i --rm mysql:8.0 mysql \
  --default-character-set=utf8mb4 \
  -h mensa-db-free-webserver-db1.i.aivencloud.com \
  -P <PORT> -u avnadmin -p"<PASSWORD>" \
  defaultdb < mysql/fix_aiven.sql
```

---

## 8. UI/UX & Frontend

### Design System (v2 — editorial dark luxury)

| Token | Value | Purpose |
| --- | --- | --- |
| `--bg-primary` | `#0a0a0f` | Page background |
| `--bg-secondary` | `#111118` | Alt sections |
| `--bg-card` | `#16161f` | Card backgrounds |
| `--bg-card-hover` | `#1c1c28` | Card hover state |
| `--accent` | `#c9a84c` | Gold accent — primary CTA colour |
| `--accent-glow` | `rgba(201,168,76,0.12)` | Soft badge / glow backgrounds |
| `--text-primary` | `#eae8e4` | Body text |
| `--text-secondary` | `#9a9a9a` | Muted descriptions |
| `--success` | `#4ade80` | Confirmed / reviewed states |
| `--error` | `#f87171` | Validation errors |
| `--font-display` | Playfair Display | Serif headings — editorial, authoritative |
| `--font-body` | DM Sans | Clean body text |
| `--font-mono` | JetBrains Mono | Labels, code, section numbers |
| `--radius-sm` | `2px` | Sharp, precise corners |

### Key Design Choices

- **Section numbering** (`01 —`, `02 —`, ...) via `.section-eyebrow` — structured portfolio feel
- **Gold italic headlines** — distinctive, warm contrast against dark background
- **Terminal card** on homepage — relevant to the infra-heavy product
- **Scroll-reveal animations** — `IntersectionObserver` with staggered `setTimeout`
- **Service cards** with ghost number overlay (`opacity: 0.06`) — depth without clutter
- **Lead team card** — gold border + `pulseGold` keyframe animation
- **Mobile hamburger** — fully accessible toggle with `aria-expanded` attribute

---

## 9. Contact Form System

### User Flow

```text
User fills contact.php form
        ↓
JS fetch → POST submit_contact.php (JSON + X-Requested-With header)
        ↓
PHP validates: full_name, email, service (required); message ≥ 10 chars
        ↓
Soft rate-limit: max 3 submissions per IP per hour
        ↓
PDO parameterised INSERT → mensa_db.contact_submissions
        ↓
JSON {success: true} → JS shows success state
```

### Fallback (no JavaScript)

`contact.php` also handles direct `$_POST` submissions, validating and inserting server-side, then rendering a success or error flash message inline.

### Security

- `submit_contact.php` rejects any request missing the `X-Requested-With: XMLHttpRequest` header.
- All inputs validated and length-checked server-side (never trust JS-only validation).
- PDO prepared statements — no SQL injection possible.
- `db_connect.php` blocked at Apache level (`Require all denied`) — never accessible via HTTP.
- Credentials injected via environment variables only — never hardcoded.
- Soft IP-based rate-limit (3 per hour) prevents basic form spam.
- All output escaped with `htmlspecialchars()`.

---

## 10. Digital Job Card Dashboard

`requests.php` is an internal-facing page for the MENSA team to review and triage all client contact submissions without logging into a database tool.

### Features

- **Summary bar** — shows total, new, and reviewed counts at the top using `--font-mono`.
- **CSS grid** — `minmax(350px, 1fr)` collapses to a single column on narrow screens.
- **Job Card** per submission — displays service badge, client name, email, phone, company, budget, formatted timestamp, and full message.
- **Glowing notification dot** — gold (`--accent`) pulsing dot in the absolute top-right corner of every card with `status = 'new'`.
- **Mark as Read** — small form with a POST handler at page top; uses a PDO prepared statement scoped to `status = 'new'` (idempotent), then PRG redirect to prevent double-submit.
- **Reviewed state** — cards already marked read display `[✓] Reviewed` in `--success` green.
- **Full security** — all output through `htmlspecialchars()`; submission ID cast to `(int)` before use.

### Viewing Submissions in MySQL (CLI)

```bash
# Tabular — good for scanning all fields quickly
docker exec -it mensa_db mysql -u root -pmensa26 mensa_db \
  -e "SELECT * FROM contact_submissions;"

# Vertical — best for reading long messages clearly
docker exec -it mensa_db mysql -u root -pmensa26 mensa_db \
  -e "SELECT * FROM contact_submissions \G"
```

Access the dashboard at: `http://bbcmensa.com/requests.php` (local) or `https://mensa-web.onrender.com/requests.php` (production).

---

## 11. Major Milestones & Architectural Decisions

| # | Milestone | Decision |
| --- | --- | --- |
| 1 | **Custom Dockerfile** | Built on `php:8.2-apache`, automatically installs `pdo_mysql`, enables `mod_rewrite`, `mod_ssl`, `mod_headers`. |
| 2 | **Professional Git history** | Conventional Commits (`feat:`, `chore:`, `fix:`, `docs:`, `style:`) — atomic, logical commits with detailed bodies. |
| 3 | **Infrastructure as Code** | `render.yaml` Blueprint defines web service declaratively; `sync: false` keeps secrets out of source control. |
| 4 | **Hybrid Cloud pivot** | Render (web, free tier) + Aiven (MySQL, free tier) — two providers connected over the internet. Chosen to avoid Render's paywalled private database. |
| 5 | **Slug-based icon map** | After emoji data was corrupted in Aiven (see §12), icons were moved out of the database entirely. A PHP `$iconMap` array keyed by service slug outputs HTML numeric entities (`&#127760;`) that the browser decodes — 100% encoding-safe. |
| 6 | **Contact form with DB persistence** | `contact_submissions` table tracks all client enquiries with a status lifecycle (`new → read → replied`). |
| 7 | **Editorial UI redesign** | Gold/dark aesthetic (Playfair Display + DM Sans + JetBrains Mono) replacing the original cyan/navy theme — warmer, more premium feel. |
| 8 | **Internal Job Card dashboard** | `requests.php` gives the team a UI to triage submissions without database access — PRG-safe POST handler, glowing notification dots, Mark-as-Read workflow. |
| 9 | **Idempotent DB init** | Added `DROP TABLE IF EXISTS` guards to `init.sql` so the seed script can be re-run on a fresh container without duplicate-key errors. |

---

## 12. Critical Fixes & Troubleshooting Log

This section documents every major problem the team hit during the project — the symptom, the root cause, and exactly how it was resolved.

---

### 12.1 — MySQL `init.sql` Permission Denied

**Symptom:** The `mensa_db` container started successfully but `init.sql` was silently skipped. MySQL logs showed `Permission denied` when attempting to read the init script.

**Root cause:** The file was created on a system with a restrictive umask, leaving it without read permission for the MySQL process inside the container.

**Fix:**

```bash
chmod 644 mysql/init.sql
docker compose down -v && docker compose up -d
```

The `-v` flag is essential — it wipes the MySQL data volume so the init script runs again on a clean slate.

---

### 12.2 — GitHub Merge Conflict & Lost License File

**Symptom:** After pushing to GitHub, a merge conflict appeared between the local `README.md` and GitHub's default init commit. Attempting to resolve it accidentally staged the GPL-3.0 `LICENSE` file for deletion.

**Root cause:** GitHub initialised the repo with its own commit while local work already existed. The diverged histories caused a conflict on the first `git pull`.

**Fix:**

```bash
git pull -X ours          # keep local changes on conflict
git reset --hard origin/main   # restore LICENSE from remote
git push origin main
```

---

### 12.3 — Render Blueprint Errors (render.yml vs render.yaml)

**Symptom 1:** Render dashboard reported "blueprint file not found" even though the file existed in the repository.

**Root cause:** The file was named `render.yml` (missing the `a`). Render specifically looks for `render.yaml`.

**Fix:** Renamed the file: `git mv render.yml render.yaml`, committed, pushed.

**Symptom 2:** Even after renaming, Render threw an `unexpected EOF` error and refused to parse the blueprint.

**Root cause:** VS Code had the file open in an unsaved state — the buffer was empty when Git staged it, producing a 0-byte file on GitHub.

**Fix:** Saved the file in VS Code (`Ctrl+S`), verified it had content, then pushed again.

---

### 12.4 — Hardcoded Aiven Password Nearly Committed

**Symptom:** During Render configuration, the Aiven database password was pasted directly into `render.yaml` under the `value:` key and was about to be committed to GitHub (a public repository).

**Root cause:** Misunderstanding of how Render's `sync: false` option works — it must be used to mark secrets so Render reads them from its encrypted dashboard instead of the YAML file.

**Fix:** Reverted the `render.yaml` to use `sync: false` on all DB credential keys before committing. The actual credentials were pasted into Render's Environment Variables dashboard only, where they are stored encrypted.

---

### 12.5 — Aiven "Unknown MySQL Server Host" Error

**Symptom:** When running the seed command against Aiven, MySQL client returned:

```text
ERROR 2005 (HY000): Unknown MySQL server host 'mensa-db-free-webserver-db1.aivencloud.com'
```

**Root cause:** The Aiven hostname has a non-obvious `.i.` sub-subdomain that is easy to miss when copying from the dashboard:

- ❌ Wrong: `mensa-db-free-webserver-db1.aivencloud.com`
- ✅ Correct: `mensa-db-free-webserver-db1.i.aivencloud.com`

**Fix:** Corrected the hostname in the Render environment variables and in all local seed commands. Added the correct hostname to this README to prevent recurrence.

---

### 12.6 — Aiven Service Went Offline (Free Tier Expiry)

**Symptom:** The live Render site suddenly stopped loading team members and services. The page fell back to static fallback content. PHP error logs on Render showed:

```text
PDOException: SQLSTATE[HY000] [2002] Connection refused
```

**Root cause:** Aiven's free-tier MySQL service has a limited active period. When the service went idle or exceeded its free allocation, Aiven automatically powered it down. The database was simply not running.

**Fix:**

1. Logged into the Aiven console at `console.aiven.io`.
2. Located the `mensa-db-free` service — it showed status **Powered Off**.
3. Clicked **Power On** and waited approximately 3–5 minutes for the service to come back online and accept connections.
4. Verified by re-running the seed command and checking the live site.

**Prevention:** Aiven free-tier services can power off without warning. If the site falls back to static content unexpectedly, always check the Aiven console first before assuming a code or configuration error.

---

### 12.7 — Emoji Icons Corrupted in Aiven (Mojibake)

**Symptom:** Service icons (🌐 💻 🖥️ 🔗) displayed as garbled sequences (`ðŸŒ`, `ðŸ'»`, `ðŸ–¥`, `ðŸ"—`) on the live Render site, while displaying correctly on local Docker.

**Root cause:** When the `init.sql` seed data was pushed to Aiven via the terminal, the MySQL client connection used a charset that did not match `utf8mb4`. The 4-byte emoji characters were transmitted as Latin-1, and Aiven stored the raw bytes rather than the correct Unicode codepoints — a classic **mojibake** encoding failure.

**Attempted fix (failed):** Tried running `UPDATE services SET icon = '🌐' WHERE slug = 'web-hosting'` directly via the terminal Docker MySQL client. This also failed — the same encoding mismatch corrupted the update.

**Attempted fix (also failed):** Tried storing HTML entities (`&#127760;`) as string literals in the database via SQL. The `htmlspecialchars()` call in PHP double-escaped them on output, rendering the raw entity text rather than the emoji.

**Root fix applied:** Removed the database from the icon rendering pipeline entirely:

```php
// In index.php and services.php — before the services loop:
$iconMap = [
    'web-hosting'          => '&#127760;',        // 🌐
    'software-development' => '&#128187;',         // 💻
    'system-administration'=> '&#128421;&#65039;', // 🖥️
    'networking'           => '&#128279;',         // 🔗
];
// Usage — bypasses $svc['icon'] entirely:
echo $iconMap[$svc['slug'] ?? ''] ?? htmlspecialchars($svc['icon']);
```

HTML numeric entities are pure ASCII — they survive every encoding layer intact and are decoded by the browser at render time. The service `slug` column is also plain ASCII and is never corrupted.

---

### 12.8 — Placeholder Phone Numbers in Production

**Symptom:** The team page on the live site showed fake phone numbers (`+256 700 000 002` through `+256 700 000 009`) for all members except the lead engineer.

**Root cause:** When the database seed was first written, placeholder numbers were used for the 8 non-lead members and were never replaced before the site went live.

**Fix:** Updated all 8 phone numbers in `mysql/init.sql` with the real verified Ugandan (+256) contacts. Created `mysql/fix_aiven.sql` to patch the live Aiven database via a single command, and ran it using an ephemeral Docker MySQL client with `--default-character-set=utf8mb4`.

---

### 12.9 — Contact Details Showing Wrong Location and Phone

**Symptom:** The contact page and homepage CTA section displayed `Kampala, Uganda` as the office location and `+256 700 000 001` as the primary contact number.

**Root cause:** These were hardcoded placeholder values written during initial development and never updated to reflect the team's actual location and contact.

**Fix:** Updated both `www/index.php` and `www/contact.php` to show `Nakawa MUBS` (Makerere University Business School, Nakawa campus) and the real lead engineer's number `+256 754230525`. The `tel:` href attribute in `contact.php` was also corrected so mobile click-to-call dials the right number.

---

### 12.10 — `backdrop-filter` Safari Support

**Symptom:** CSS linter flagged a missing vendor prefix for `backdrop-filter` on the navbar frosted-glass blur effect. The blur did not render on Safari/WebKit browsers.

**Root cause:** Safari requires the `-webkit-` prefix for `backdrop-filter`.

**Fix:** Added `-webkit-backdrop-filter: blur(24px)` alongside the standard `backdrop-filter: blur(24px)` in `style.css`.

---

## 13. Team Members

| # | Name | Role | Department | Phone | Reg Number | Student No |
| --- | --- | --- | --- | --- | --- | --- |
| 1 ⭐ | **Wampamba Festo** | Lead Software Engineer & Architect | Engineering | +256 754230525 | 23/U/18503/EVE | 2300718503 |
| 2 | Kawere Edrine | Systems Engineer | Engineering | +256 787880410 | 23/U/09440/PS | 2300709440 |
| 3 | Kyarikundabakeine Grace | Network Engineer | Networking | +256 769964637 | 23/U/10491/PS | 2300710491 |
| 4 | Kamari Zahellena | Database Administrator | Engineering | +256 742148786 | 23/U/08844/PS | 2300708844 |
| 5 | Talemwa Daniella | Software Developer | Software Development | +256 756232586 | 23/U/17830/EVE | 2300717830 |
| 6 | Awori Betsy Hope | Web Developer | Software Development | +256 707578488 | 23/U/07084/PS | 2300707084 |
| 7 | Tumusiime Elvin Luke | System Administrator | System Administration | +256 707013004 | 23/U/18113/PS | 2300718113 |
| 8 | Mungujaki Samaxwell | Infrastructure Engineer | System Administration | +256 777088297 | 23/U/12023/EVE | 2300712023 |
| 9 | Kirabo Queen Esther | Network Administrator | Networking | +256 741779673 | 23/U/24679/PS | 2300724679 |

---

## 14. Branch Strategy

| Branch | Purpose |
| --- | --- |
| `main` | Stable production branch — deployed to Render automatically |
| `development` | Active development — all features built and tested here first |

### What `development` contains over `main`

- Complete UI/UX overhaul (gold editorial dark theme — Playfair Display, DM Sans, JetBrains Mono)
- `contact.php` and `submit_contact.php` — client enquiry form with AJAX + PHP fallback
- `requests.php` — internal Digital Job Card dashboard with Mark-as-Read workflow
- `contact_submissions` table added to `mysql/init.sql`
- `mysql/fix_aiven.sql` — one-time patch for emoji icons and real phone numbers
- Slug-based emoji icon map in `index.php` and `services.php` (Aiven encoding fix)
- Real phone numbers for all 9 team members
- Updated office location: Nakawa MUBS
- `DROP TABLE IF EXISTS` guards in `init.sql` for idempotent re-seeding
- Fully documented troubleshooting log in this README

### Merging to production

```bash
git checkout main
git merge --no-ff development
git push origin main
```

---

## License

MIT License — see [LICENSE](LICENSE) for details.

This project was developed as a practical laboratory assignment for the Bachelor of Science
in Computer Science programme at Makerere University, Academic Year 2023/2024.
Copyright © 2024 MENSA Tech Agency — Wampamba Festo & Team.
