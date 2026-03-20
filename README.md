# MENSA Tech Agency — Project README

**Author:** Wampamba Festo — Lead Software Engineer & Architect
**University:** Makerere University | **Year:** 2023/2024
**Stack:** Apache/PHP 8.2 · MySQL 8.0 · Bind9 DNS · Docker Compose

---

## Team Members

| # | Full Name | Reg Number | Student No | Role |
|---|---|---|---|---|
| 1 | **Wampamba Festo** ⭐ | 23/U/18503/EVE | 2300718503 | Lead Software Engineer & Architect |
| 2 | Kawere Edrine | 23/U/09440/PS | 2300709440 | Systems Engineer |
| 3 | Kyarikundabakeine Grace | 23/U/10491/PS | 2300710491 | Network Engineer |
| 4 | Kamari Zahellena | 23/U/08844/PS | 2300708844 | Database Administrator |
| 5 | Talemwa Daniella | 23/U/17830/EVE | 2300717830 | Software Developer |
| 6 | Awori Betsy Hope | 23/U/07084/PS | 2300707084 | Web Developer |
| 7 | Tumusiime Elvin Luke | 23/U/18113/PS | 2300718113 | System Administrator |
| 8 | Mungujaki Samaxwell | 23/U/12023/EVE | 2300712023 | Infrastructure Engineer |
| 9 | Kirabo Queen Esther | 23/U/24679/PS | 2300724679 | Network Administrator |

---

## Credentials (All Passwords = mensa26)

| Service | Username | Password | Notes |
|---|---|---|---|
| MySQL Root | root | mensa26 | Container-internal only |
| MySQL App User | mensa_user | mensa26 | Used by PHP/PDO |
| Bind9 DNS | — | — | No auth required |

---

## Project Structure

```
mensa-project/
├── .env                        ← Credentials & config (password: mensa26)
├── .gitignore
├── docker-compose.yml          ← Orchestrates all 3 containers
├── README.md
│
├── apache/
│   ├── mensa.conf              ← Apache VirtualHost (HTTP + HTTPS template)
│   └── php.ini                 ← PHP runtime settings
│
├── bind9/
│   ├── named.conf.options      ← Global DNS options & forwarders
│   ├── named.conf.local        ← Zone declarations
│   └── zones/
│       ├── db.mensa.local      ← Forward zone (hostname → IP)
│       └── db.172.28.0         ← Reverse zone (IP → hostname)
│
├── mysql/
│   └── init.sql                ← Schema + all 9 team members seeded
│
└── www/                        ← Apache DocumentRoot
    ├── index.php               ← Homepage (dynamic services from DB)
    ├── team.php                ← Team page (all 9 members from DB)
    ├── services.php            ← Services page (features from DB)
    ├── db_connect.php          ← PDO connection (web access blocked)
    └── assets/
        └── css/
            └── style.css
```

---

## Quick Start (5 Commands)

```bash
# 1. Fix Ubuntu port 53 conflict (run once)
sudo sed -i 's/#DNSStubListener=yes/DNSStubListener=no/' /etc/systemd/resolved.conf
echo "DNSStubListener=no" | sudo tee -a /etc/systemd/resolved.conf
sudo systemctl restart systemd-resolved

# 2. Extract and enter the project
tar -xzf mensa-project.tar.gz && cd mensa-project

# 3. Pull Docker images
docker compose pull

# 4. Start all containers
docker compose up -d

# 5. Add domain to hosts file & open browser
echo "127.0.0.1 mensa.local www.mensa.local" | sudo tee -a /etc/hosts
# Then visit: http://mensa.local
```

---

## Container Overview

| Container | Image | IP | Ports |
|---|---|---|---|
| mensa_web | php:8.2-apache | 172.28.0.10 | 80, 443 (host) |
| mensa_db | mysql:8.0 | 172.28.0.20 | 3306 (internal only) |
| mensa_dns | ubuntu/bind9 | 172.28.0.53 | 53 UDP+TCP (host) |

---

## Verify Everything Works

```bash
# Check all containers are up
docker compose ps

# Test DNS — forward resolution
dig @127.0.0.1 mensa.local A
# Expected: 172.28.0.10

# Test DNS — reverse resolution
dig @127.0.0.1 -x 172.28.0.10
# Expected: mensa.local.

# Test web server
curl -I http://mensa.local
# Expected: HTTP/1.1 200 OK

# Test team page fetches from DB
curl -s http://mensa.local/team.php | grep "Wampamba"
# Expected: finds the name

# Test security — db_connect.php must be blocked
curl -I http://mensa.local/db_connect.php
# Expected: 403 Forbidden

# Connect to MySQL directly
docker exec -it mensa_db mysql -u mensa_user -pmensa26 mensa_db
# Inside MySQL:
SELECT full_name, reg_number, student_no FROM team_members ORDER BY is_lead DESC, id ASC;
EXIT;
```

---

## Container Commands

```bash
docker compose up -d            # Start all containers
docker compose stop             # Stop (data preserved)
docker compose down             # Remove containers (data preserved)
docker compose down -v          # Full reset including database

docker compose logs -f web      # Apache/PHP logs
docker compose logs -f db       # MySQL logs
docker compose logs -f dns      # Bind9 logs

docker compose restart web      # Restart web container
docker compose restart dns      # Restart DNS container
docker exec -it mensa_web bash  # Shell into web container
docker exec -it mensa_db  bash  # Shell into MySQL container
```

---

## DNS Zone Management

After editing any zone file in `bind9/zones/`:
1. Increment the **Serial** number in the SOA record (`2025062001` → `2025062002`)
2. Run: `docker compose restart dns`
3. Verify: `docker exec mensa_dns named-checkzone mensa.local /etc/bind/zones/db.mensa.local`

---

*Designed and built by Wampamba Festo — Lead Software Engineer & Architect, MENSA Tech Agency*
