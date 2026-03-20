-- ============================================================
--  MENSA Infrastructure — Database Initialisation Script
--  Author : Wampamba Festo (Lead Software Engineer & Architect)
--  File   : mysql/init.sql
--  DB     : mensa_db  |  User: mensa_user  |  Pass: mensa26
--  Team   : 9 Members — Makerere University 2023/2024
-- ============================================================

USE mensa_db;

-- ── Table: team_members ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS team_members (
    id          INT          UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(120) NOT NULL,
    role        VARCHAR(150) NOT NULL,
    department  VARCHAR(100) NOT NULL DEFAULT 'Engineering',
    email       VARCHAR(180) NOT NULL UNIQUE,
    phone       VARCHAR(30)           DEFAULT NULL,
    reg_number  VARCHAR(60)           DEFAULT NULL COMMENT 'University Registration Number',
    student_no  VARCHAR(30)           DEFAULT NULL COMMENT 'University Student Number',
    bio         TEXT                  DEFAULT NULL,
    is_lead     TINYINT(1)   NOT NULL DEFAULT 0    COMMENT '1 = Lead, 0 = Member',
    joined_date DATE                  DEFAULT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='MENSA Tech Agency — Team Profiles';

-- ── Table: services ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS services (
    id          INT          UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(120) NOT NULL,
    slug        VARCHAR(120) NOT NULL UNIQUE,
    icon        VARCHAR(60)  NOT NULL,
    description TEXT         NOT NULL,
    features    JSON                  DEFAULT NULL,
    sort_order  TINYINT      NOT NULL DEFAULT 0,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  TEAM MEMBERS — 9 Students, Makerere University 2023
-- ============================================================
INSERT INTO team_members
    (full_name, role, department, email, phone, reg_number, student_no, bio, is_lead, joined_date)
VALUES

-- 1. WAMPAMBA FESTO — Team Lead
(
    'Wampamba Festo',
    'Lead Software Engineer & Architect',
    'Engineering',
    'wampamba.festo@mensa.local',
    '+256 700 000 001',
    '23/U/18503/EVE',
    '2300718503',
    'Lead Software Engineer and System Architect at MENSA. Responsible for full infrastructure design, Docker orchestration, DNS management, database architecture, and overall technical direction.',
    1,
    '2024-01-15'
),

-- 2. KAWERE EDRINE
(
    'Kawere Edrine',
    'Systems Engineer',
    'Engineering',
    'kawere.edrine@mensa.local',
    '+256 700 000 002',
    '23/U/09440/PS',
    '2300709440',
    'Systems Engineer at MENSA specialising in infrastructure setup, server configuration, and system integration.',
    0,
    '2024-01-15'
),

-- 3. KYARIKUNDABAKEINE GRACE
(
    'Kyarikundabakeine Grace',
    'Network Engineer',
    'Networking',
    'kyarikundabakeine.grace@mensa.local',
    '+256 700 000 003',
    '23/U/10491/PS',
    '2300710491',
    'Network Engineer at MENSA focused on LAN/WAN architecture, DNS configuration, and network security auditing.',
    0,
    '2024-01-15'
),

-- 4. KAMARI ZAHELLENA
(
    'Kamari Zahellena',
    'Database Administrator',
    'Engineering',
    'kamari.zahellena@mensa.local',
    '+256 700 000 004',
    '23/U/08844/PS',
    '2300708844',
    'Database Administrator at MENSA responsible for schema design, query optimisation, backup strategies, and data integrity.',
    0,
    '2024-01-15'
),

-- 5. TALEMWA DANIELLA
(
    'Talemwa Daniella',
    'Software Developer',
    'Software Development',
    'talemwa.daniella@mensa.local',
    '+256 700 000 005',
    '23/U/17830/EVE',
    '2300717830',
    'Software Developer at MENSA building scalable web applications and REST APIs using modern development practices.',
    0,
    '2024-01-15'
),

-- 6. AWORI BETSY HOPE
(
    'Awori Betsy Hope',
    'Web Developer',
    'Software Development',
    'awori.betsy@mensa.local',
    '+256 700 000 006',
    '23/U/07084/PS',
    '2300707084',
    'Web Developer at MENSA specialising in frontend design, PHP backend development, and responsive user interfaces.',
    0,
    '2024-01-15'
),

-- 7. TUMUSIIME ELVIN LUKE
(
    'Tumusiime Elvin Luke',
    'System Administrator',
    'System Administration',
    'tumusiime.elvin@mensa.local',
    '+256 700 000 007',
    '23/U/18113/PS',
    '2300718113',
    'System Administrator at MENSA managing Linux servers, security hardening, automated backups, and performance monitoring.',
    0,
    '2024-01-15'
),

-- 8. MUNGUJAKI SAMAXWELL
(
    'Mungujaki Samaxwell',
    'Infrastructure Engineer',
    'System Administration',
    'mungujaki.samaxwell@mensa.local',
    '+256 700 000 008',
    '23/U/12023/EVE',
    '2300712023',
    'Infrastructure Engineer at MENSA handling Docker orchestration, CI/CD pipelines, and deployment strategies.',
    0,
    '2024-01-15'
),

-- 9. KIRABO QUEEN ESTHER
(
    'Kirabo Queen Esther',
    'Network Administrator',
    'Networking',
    'kirabo.queenester@mensa.local',
    '+256 700 000 009',
    '23/U/24679/PS',
    '2300724679',
    'Network Administrator at MENSA overseeing firewall policy, VPN setup, bandwidth monitoring, and network incident response.',
    0,
    '2024-01-15'
);

-- ============================================================
--  SERVICES — 4 Core Services
-- ============================================================
INSERT INTO services (title, slug, icon, description, features, sort_order) VALUES
(
    'Web Hosting',
    'web-hosting',
    '🌐',
    'Enterprise-grade web hosting solutions with 99.9% uptime SLA, scalable resources, and 24/7 technical support. From shared hosting to dedicated infrastructure, we have a plan for every scale.',
    '["99.9% Uptime SLA", "Free SSL Certificates", "Daily Automated Backups", "cPanel / Custom Dashboard", "1-Click CMS Installs", "24/7 Technical Support"]',
    1
),
(
    'Software Development',
    'software-development',
    '💻',
    'Full-cycle custom software development from requirements gathering and system design through to deployment and maintenance. We build scalable, secure applications tailored to your business.',
    '["Custom Web Applications", "REST API Design & Development", "Database Architecture", "Agile Development Process", "Code Review & Quality Assurance", "Post-Launch Maintenance"]',
    2
),
(
    'System Administration',
    'system-administration',
    '🖥️',
    'Professional Linux and Windows server administration services. We configure, secure, monitor, and maintain your infrastructure so your team can focus on building great products.',
    '["Linux & Windows Server Management", "Security Hardening & Auditing", "Performance Monitoring & Tuning", "Automated Backup & Recovery", "Docker & Container Orchestration", "Incident Response & On-Call Support"]',
    3
),
(
    'Networking',
    'networking',
    '🔗',
    'End-to-end network design, implementation, and management. From LAN/WAN architecture to DNS configuration and firewall policy, we keep your organisation connected and secure.',
    '["LAN / WAN Architecture Design", "DNS Server Setup & Management (Bind9)", "Firewall Configuration & Management", "VPN Setup & Remote Access", "Network Security Auditing", "Bandwidth Monitoring & Optimisation"]',
    4
);

-- ── Table: contact_submissions ───────────────────────────────
CREATE TABLE IF NOT EXISTS contact_submissions (
    id          INT          UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(120) NOT NULL,
    email       VARCHAR(180) NOT NULL,
    company     VARCHAR(120)          DEFAULT NULL,
    phone       VARCHAR(30)           DEFAULT NULL,
    service     VARCHAR(80)  NOT NULL COMMENT 'Service enquired about',
    budget      VARCHAR(60)           DEFAULT NULL,
    message     TEXT         NOT NULL,
    status      ENUM('new','read','replied') NOT NULL DEFAULT 'new',
    ip_address  VARCHAR(45)           DEFAULT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status    (status),
    INDEX idx_service   (service),
    INDEX idx_created   (created_at),
    INDEX idx_ip        (ip_address)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='MENSA Tech Agency — Client Contact Submissions';

-- ── Grants ───────────────────────────────────────────────────
GRANT SELECT, INSERT, UPDATE, DELETE ON mensa_db.* TO 'mensa_user'@'%';
FLUSH PRIVILEGES;

-- ── Verify (shows in docker logs on first run) ───────────────
SELECT CONCAT('✔  Seeded ', COUNT(*), ' team members') AS init_status FROM team_members;
SELECT id, full_name, reg_number, student_no, is_lead FROM team_members ORDER BY is_lead DESC, id ASC;
SELECT CONCAT('✔  contact_submissions table ready') AS init_status;
