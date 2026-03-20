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
10. [Major Milestones & Architectural Decisions](#10-major-milestones--architectural-decisions)
11. [Critical Fixes & Troubleshooting Log](#11-critical-fixes--troubleshooting-log)
12. [Team Members](#12-team-members)
13. [Branch Strategy](#13-branch-strategy)

---

## 1. Project Overview

MENSA Tech Agency is a full-stack web application and DevOps project built by a 9-member team of Computer Science students at Makerere University. The project demonstrates a production-ready, decoupled cloud architecture transitioning seamlessly from a local Docker environment to a live cloud deployment.

**Live pages:**

- `/` — Homepage with hero, services preview, about, and CTA
- `/services.php` — Full service detail pages with feature lists
- `/team.php` — Live team roster fetched from MySQL via PDO
- `/contact.php` — Client enquiry form with DB persistence

---

## 2. Final Tech Stack

| Layer | Technology | Notes |
| --- | --- | --- |
| **Web Server** | Apache 2.4 | Custom Debian-based Docker image |
| **Language** | PHP 8.2 | `strict_types`, PDO, `htmlspecialchars` everywhere |
| **Database** | MySQL 8.0 | `utf8mb4_unicode_ci`, `InnoDB` |
| **Local DNS** | Bind9 | Resolves `bbcmensa.com` → `172.28.0.10` locally |
| **Orchestration** | Docker & Docker Compose | 3-service stack: web, db, dns |
| **Cloud — Web** | Render (Free Web Service) | Docker environment |
| **Cloud — Database** | Aiven (Free Cloud MySQL) | Hybrid cloud pivot |
| **IaC** | `render.yaml` | Blueprint with `sync: false` for secrets |
| **Version Control** | Git & GitHub | Conventional Commits |

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
├── render.yml                  # Legacy file (historical — filename typo fixed)
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
│   └── init.sql                # Creates tables, seeds 9 members + 4 services + contact_submissions
│
└── www/
    ├── db_connect.php          # PDO singleton — reads env vars, never exposes credentials
    ├── index.php               # Homepage
    ├── services.php            # Services detail page
    ├── team.php                # Team roster (live DB query)
    ├── contact.php             # Client contact form (GET display + POST fallback)
    ├── submit_contact.php      # AJAX JSON handler — validates, rate-limits, persists to DB
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
| `phone` | VARCHAR(30) | |
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
| `slug` | VARCHAR(120) UNIQUE | URL-friendly key |
| `icon` | VARCHAR(60) | Emoji icon |
| `description` | TEXT | Full service description |
| `features` | JSON | Array of feature strings |
| `sort_order` | TINYINT | Display order |
| `created_at` | TIMESTAMP | |

### `mensa_db.contact_submissions` *(added in development branch)*

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
| `DB_NAME` | `mensa_db` |
| `DB_USER` | `mensa_user` |
| `DB_PASS` | Aiven password (keep secret) |

### Aiven (Database Tier)

- Service: Free Cloud MySQL
- Region: Europe (GCP)
- Hostname format: `mensa-db-free-webserver-db1.i.aivencloud.com`
  *(note the `.i.` sub-subdomain — critical for connectivity)*
- SSL enforced by Aiven by default.

### Seeding Aiven (one-time)

```bash
# Strip local GRANT statements from init.sql first (use aiven_seed.sql)
docker run --rm -it mysql:8.0 \
  mysql -h mensa-db-free-webserver-db1.i.aivencloud.com \
        -P <PORT> -u mensa_user -p mensa_db \
        < mysql/aiven_seed.sql
```

---

## 8. UI/UX & Frontend

### Design System (v2 — development branch)

The frontend was redesigned using an **editorial dark luxury** aesthetic.

| Token | Value | Purpose |
| --- | --- | --- |
| `--bg-primary` | `#0a0a0f` | Page background |
| `--bg-secondary` | `#111118` | Alt sections |
| `--bg-card` | `#16161f` | Card backgrounds |
| `--accent` | `#c9a84c` | Gold accent — primary CTA colour |
| `--text-primary` | `#eae8e4` | Body text |
| `--text-secondary` | `#9a9a9a` | Muted descriptions |
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

### Viewing Submissions

```sql
-- View all new enquiries
SELECT id, full_name, email, service, budget, created_at
FROM   contact_submissions
WHERE  status = 'new'
ORDER  BY created_at DESC;

-- Mark as read
UPDATE contact_submissions SET status = 'read' WHERE id = <ID>;
```

### Security

- `submit_contact.php` rejects any request that is not a JSON `POST` with `X-Requested-With: XMLHttpRequest`.
- All inputs validated and length-checked server-side (never trust JS-only validation).
- PDO prepared statements — no SQL injection possible.
- `db_connect.php` blocked at Apache level (`Require all denied`) — never accessible via HTTP.
- Credentials injected via environment variables only — never hardcoded.
- Soft IP-based rate-limit (3 per hour) prevents basic form spam.

---

## 10. Major Milestones & Architectural Decisions

| # | Milestone | Decision |
| --- | --- | --- |
| 1 | **Custom Dockerfile** | Built on `php:8.2-apache`, automatically installs `pdo_mysql`, enables `mod_rewrite`, `mod_ssl`, `mod_headers`. |
| 2 | **Professional Git history** | Conventional Commits (`feat:`, `chore:`, `fix:`, `docs:`) — atomic, logical commits. |
| 3 | **Infrastructure as Code** | `render.yaml` Blueprint defines web service declaratively; `sync: false` keeps secrets out of source control. |
| 4 | **Hybrid Cloud pivot** | Render (web, free tier) + Aiven (MySQL, free tier) — two providers connected over the internet. Chosen to avoid Render's paywalled private database. |
| 5 | **Secure DB seeding** | Ephemeral Docker container used to run `aiven_seed.sql` against the remote Aiven instance — no local credentials left behind. |
| 6 | **Contact form with DB persistence** | `contact_submissions` table tracks all client enquiries with status lifecycle (`new → read → replied`). |
| 7 | **Editorial UI redesign** | Gold/dark aesthetic (Playfair Display + DM Sans + JetBrains Mono) replacing the original cyan/navy theme — warmer, more premium feel. |

---

## 11. Critical Fixes & Troubleshooting Log

### File Permission Block (MySQL init)

**Problem:** MySQL container started but `init.sql` threw `Permission denied`.
**Fix:** `chmod 644 mysql/init.sql` then `docker compose down -v && docker compose up -d` to force a clean re-init.

### GitHub Merge Conflict & Lost License

**Problem:** Local `README.md` clashed with GitHub's default init commit; GPL-3.0 license accidentally flagged for deletion.
**Fix:** `git pull -X ours` to preserve local work, then `git reset --hard origin/main` to restore the license.

### Render Blueprint Sync Errors

**Problem:** Render threw errors — file initially named `render.yml` (missing `a`) and later uploaded as 0-byte file (EOF error, unsaved VS Code buffer).
**Fix:** Renamed to `render.yaml`, saved buffer to disk, pushed again.

### Security: Hardcoded Password Prevented

**Problem:** Live Aiven DB password was nearly committed directly into `render.yaml`.
**Fix:** Reverted to `sync: false` on all DB env var keys; credentials pasted into Render's encrypted Environment Variables dashboard only.

### Aiven Hostname Typo

**Problem:** `Unknown MySQL server host` error during final seeding.
**Fix:** Missing `.i.` in hostname — correct: `mensa-db-free-webserver-db1.i.aivencloud.com`.

### `backdrop-filter` Safari Support

**Problem:** CSS linter flagged missing vendor prefix for `backdrop-filter` on navbar.
**Fix:** Added `-webkit-backdrop-filter: blur(24px)` alongside the standard property.

---

## 12. Team Members

| # | Name | Role | Reg Number | Student No |
| --- | --- | --- | --- | --- |
| 1 ⭐ | **Wampamba Festo** | Lead Software Engineer & Architect | 23/U/18503/EVE | 2300718503 |
| 2 | Kawere Edrine | Systems Engineer | 23/U/09440/PS | 2300709440 |
| 3 | Kyarikundabakeine Grace | Network Engineer | 23/U/10491/PS | 2300710491 |
| 4 | Kamari Zahellena | Database Administrator | 23/U/08844/PS | 2300708844 |
| 5 | Talemwa Daniella | Software Developer | 23/U/17830/EVE | 2300717830 |
| 6 | Awori Betsy Hope | Web Developer | 23/U/07084/PS | 2300707084 |
| 7 | Tumusiime Elvin Luke | System Administrator | 23/U/18113/PS | 2300718113 |
| 8 | Mungujaki Samaxwell | Infrastructure Engineer | 23/U/12023/EVE | 2300712023 |
| 9 | Kirabo Queen Esther | Network Administrator | 23/U/24679/PS | 2300724679 |

---

## 13. Branch Strategy

| Branch | Purpose |
| --- | --- |
| `main` | Stable production branch — deployed to Render |
| `development` | Active development — UI/UX redesign + contact form feature |

The `development` branch contains:

- Complete UI/UX overhaul (gold editorial dark theme)
- New `contact.php` and `submit_contact.php`
- `contact_submissions` table added to `mysql/init.sql`
- Updated `README.md`

To merge into `main` when ready:

```bash
git checkout main
git merge --no-ff development
git push origin main
```

---

## License

GNU General Public License v3.0 — see [LICENSE](LICENSE) for details.
