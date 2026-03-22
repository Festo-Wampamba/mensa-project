<?php
/**
 * ============================================================
 *  MENSA Tech Agency — Homepage
 *  Author : Wampamba Festo (Lead Software Engineer & Architect)
 * ============================================================
 */
declare(strict_types=1);
require_once __DIR__ . '/db_connect.php';

$services = [];
$lead     = null;

try {
    $pdo      = getDbConnection();
    $stmt     = $pdo->query('SELECT * FROM services ORDER BY sort_order ASC');
    $services = $stmt->fetchAll();
    $stmt2    = $pdo->prepare('SELECT full_name, role FROM team_members WHERE is_lead = 1 LIMIT 1');
    $stmt2->execute();
    $lead     = $stmt2->fetch();
} catch (RuntimeException $e) { /* fallback to static */ }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="MENSA Tech Agency — Enterprise web hosting, software development, system administration and networking. Uganda's premier tech agency." />
  <title>MENSA Tech Agency — Engineering the Digital Future</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>

<!-- ── NAVIGATION ─────────────────────────────────────────── -->
<nav class="navbar" id="navbar">
  <div class="navbar-inner">
    <a href="index.php" class="navbar-logo">
      <div class="logo-mark">M</div>
      MENSA
    </a>
    <ul class="navbar-links" id="navLinks">
      <li><a href="index.php" class="active">Home</a></li>
      <li><a href="services.php">Services</a></li>
      <li><a href="team.php">Our Team</a></li>
      <li><a href="requests.php">Job Cards</a></li>
      <li><a href="contact.php" class="btn-nav">Contact Us</a></li>
    </ul>
    <button class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<!-- ── HERO ───────────────────────────────────────────────── -->
<section class="hero" id="home">
  <div class="hero-orb one"></div>
  <div class="hero-orb two"></div>
  <div class="hero-inner">

    <div class="hero-content">
      <div class="hero-label">
        <span>Uganda's Premier Tech Agency</span>
      </div>
      <h1 class="hero-title">
        Engineering the<br>
        <span class="italic">Digital Future</span> —<br>
        Reliably.
      </h1>
      <p class="hero-desc">
        MENSA delivers enterprise-grade infrastructure and software solutions.
        From blazing-fast web hosting to complex system architecture,
        we build the foundations that power your business.
      </p>
      <div class="hero-cta">
        <a href="services.php" class="btn-primary">Explore Services →</a>
        <a href="team.php" class="btn-secondary">Meet Our Team</a>
      </div>
      <div class="hero-stats">
        <div class="stat-item">
          <div class="stat-number">99.9%</div>
          <div class="stat-label">Uptime SLA</div>
        </div>
        <div class="stat-item">
          <div class="stat-number">4</div>
          <div class="stat-label">Core Services</div>
        </div>
        <div class="stat-item">
          <div class="stat-number">9</div>
          <div class="stat-label">Expert Engineers</div>
        </div>
        <div class="stat-item">
          <div class="stat-number">24/7</div>
          <div class="stat-label">Support</div>
        </div>
      </div>
    </div>

    <div class="hero-visual">
      <div class="terminal-card">
        <div class="terminal-header">
          <div class="terminal-dot"></div>
          <div class="terminal-dot"></div>
          <div class="terminal-dot"></div>
          <span class="terminal-title">mensa@server:~$</span>
        </div>
        <div class="terminal-body">
          <div><span class="t-prompt">$ </span><span class="t-cmd">docker compose up -d</span></div>
          <div class="t-out">✔ Network mensa_net    Created</div>
          <div class="t-out">✔ Container mensa_db   Started</div>
          <div class="t-out">✔ Container mensa_web  Started</div>
          <div class="t-out">✔ Container mensa_dns  Started</div>
          <br>
          <div><span class="t-prompt">$ </span><span class="t-cmd">dig @172.28.0.53 bbcmensa.com</span></div>
          <div class="t-out">bbcmensa.com. 86400 IN A 172.28.0.10</div>
          <br>
          <div><span class="t-prompt">$ </span><span class="t-cmd">curl -I http://bbcmensa.com</span></div>
          <div class="t-out">HTTP/1.1 200 OK</div>
          <div class="t-comment"># All systems operational ✔</div>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ── SERVICES ───────────────────────────────────────────── -->
<section class="section section--alt" id="services">
  <div class="container">
    <div class="section-header reveal">
      <span class="section-eyebrow">02 — What We Do</span>
      <h2 class="section-title">Core Services</h2>
      <p class="section-subtitle">
        Four pillars of technology excellence, delivered by certified engineers
        with real-world infrastructure experience.
      </p>
    </div>

    <?php $iconMap = ['web-hosting'=>'&#127760;','software-development'=>'&#128187;','system-administration'=>'&#128421;&#65039;','networking'=>'&#128279;']; ?>
    <div class="services-grid">
      <?php if (!empty($services)): ?>
        <?php foreach ($services as $i => $svc): ?>
          <?php $features = json_decode($svc['features'] ?? '[]', true) ?? []; ?>
          <article class="service-card reveal">
            <div class="service-number"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></div>
            <div class="service-icon"><?= $iconMap[$svc['slug'] ?? ''] ?? htmlspecialchars($svc['icon']) ?></div>
            <h3 class="service-title"><?= htmlspecialchars($svc['title']) ?></h3>
            <p class="service-desc"><?= htmlspecialchars(mb_strimwidth($svc['description'], 0, 110, '…')) ?></p>
            <ul class="service-features">
              <?php foreach (array_slice($features, 0, 3) as $f): ?>
                <li><?= htmlspecialchars($f) ?></li>
              <?php endforeach; ?>
            </ul>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <article class="service-card reveal">
          <div class="service-number">01</div>
          <div class="service-icon">🌐</div>
          <h3 class="service-title">Web Hosting</h3>
          <p class="service-desc">99.9% uptime SLA with 24/7 technical support.</p>
        </article>
        <article class="service-card reveal">
          <div class="service-number">02</div>
          <div class="service-icon">💻</div>
          <h3 class="service-title">Software Development</h3>
          <p class="service-desc">Full-cycle custom software engineering.</p>
        </article>
        <article class="service-card reveal">
          <div class="service-number">03</div>
          <div class="service-icon">🖥️</div>
          <h3 class="service-title">System Administration</h3>
          <p class="service-desc">Professional Linux server management.</p>
        </article>
        <article class="service-card reveal">
          <div class="service-number">04</div>
          <div class="service-icon">🔗</div>
          <h3 class="service-title">Networking</h3>
          <p class="service-desc">End-to-end network design and DNS.</p>
        </article>
      <?php endif; ?>
    </div>

    <div style="text-align:center; margin-top:3rem;" class="reveal">
      <a href="services.php" class="btn-secondary">View All Service Details →</a>
    </div>
  </div>
</section>

<!-- ── ABOUT ──────────────────────────────────────────────── -->
<section class="section" id="about">
  <div class="container">
    <div class="about-grid">

      <div class="reveal">
        <span class="section-eyebrow">03 — Why MENSA</span>
        <h2 class="section-title">Infrastructure Built to Last</h2>
        <p class="about-text" style="color:var(--text-secondary);font-size:.97rem;line-height:1.85;margin-bottom:1.2rem;">
          We don't just set up servers — we architect <strong style="color:var(--text-primary)">resilient, scalable, and secure</strong>
          digital infrastructure. Every configuration decision is made with long-term operational excellence in mind.
        </p>
        <p style="color:var(--text-secondary);font-size:.97rem;line-height:1.85;margin-bottom:2rem;">
          From Dockerised microservices and Bind9 DNS management to hardened Apache configurations
          and MySQL architecture, MENSA engineers bring production-level thinking to every project.
        </p>
        <div class="about-pillars">
          <div class="about-pillar">
            <div class="about-pillar-icon">🐳</div>
            <div>
              <h4>Containerised Infrastructure</h4>
              <p>Docker-first approach for portable, reproducible environments</p>
            </div>
          </div>
          <div class="about-pillar">
            <div class="about-pillar-icon">🔒</div>
            <div>
              <h4>Security by Design</h4>
              <p>Least-privilege access, encrypted connections, hardened configs</p>
            </div>
          </div>
          <div class="about-pillar">
            <div class="about-pillar-icon">📡</div>
            <div>
              <h4>Full DNS Control</h4>
              <p>Authoritative Bind9 DNS with forward &amp; reverse zone management</p>
            </div>
          </div>
        </div>
      </div>

      <div class="reveal">
        <div class="terminal-card">
          <div class="terminal-header">
            <div class="terminal-dot"></div>
            <div class="terminal-dot"></div>
            <div class="terminal-dot"></div>
            <span class="terminal-title">mensa@prod — docker ps</span>
          </div>
          <div class="terminal-body">
            <div><span class="t-prompt">$ </span><span class="t-cmd">docker ps --format "table {{.Names}}\t{{.Status}}"</span></div>
            <div class="t-out">NAMES          STATUS</div>
            <div class="t-out">mensa_web      Up 14 days (healthy)</div>
            <div class="t-out">mensa_db       Up 14 days (healthy)</div>
            <div class="t-out">mensa_dns      Up 14 days</div>
            <br>
            <div><span class="t-prompt">$ </span><span class="t-cmd">mysql -u mensa_user -e "SELECT COUNT(*) FROM team_members"</span></div>
            <div class="t-out">+----------+</div>
            <div class="t-out">| COUNT(*) |</div>
            <div class="t-out">+----------+</div>
            <div class="t-out">|        9 |</div>
            <div class="t-out">+----------+</div>
            <br>
            <div class="t-comment"># 9 engineers. 4 services. 1 mission. ✔</div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ── TEAM TEASER ────────────────────────────────────────── -->
<section class="section section--alt" id="team">
  <div class="container" style="text-align:center;">
    <div class="reveal">
      <span class="section-eyebrow">04 — The People</span>
      <h2 class="section-title">Led by Experts</h2>
      <p class="section-subtitle" style="margin-inline:auto;">
        Our team of 9 engineers spans software architecture, system administration,
        networking, and full-stack development — all trained at Makerere University.
      </p>
      <a href="team.php" class="btn-primary">Meet the Full Team →</a>
    </div>
  </div>
</section>

<!-- ── CTA / CONTACT ──────────────────────────────────────── -->
<section class="cta-section" id="contact">
  <div class="container">
    <div class="cta-inner reveal">
      <span class="section-eyebrow">05 — Get In Touch</span>
      <h2 class="section-title">Ready to Build Something Great?</h2>
      <p>
        Whether you need managed hosting, a custom application, or a full
        infrastructure overhaul — the MENSA team is ready to help.
      </p>
      <div class="hero-cta" style="justify-content:center;">
        <a href="contact.php" class="btn-primary">Start a Project →</a>
        <a href="team.php" class="btn-secondary">View Our Team</a>
      </div>
      <div class="contact-methods">
        <div class="contact-pill">📍 Nakawa MUBS</div>
        <div class="contact-pill">📞 +256 754230525</div>
        <div class="contact-pill">🌐 bbcmensa.com</div>
      </div>
    </div>
  </div>
</section>

<!-- ── FOOTER ─────────────────────────────────────────────── -->
<footer>
  <div class="footer-inner">
    <p class="footer-copy">
      &copy; <?= date('Y') ?> MENSA Tech Agency &mdash;
      Architected by <span class="accent-name">Festo Wampamba</span>
      as Lead Software Engineer and Mensa Team.
    </p>
    <ul class="footer-links">
      <li><a href="https://github.com/Festo-Wampamba" target="_blank" rel="noopener">GitHub</a></li>
      <li><a href="index.php">Home</a></li>
      <li><a href="services.php">Services</a></li>
      <li><a href="team.php">Team</a></li>
      <li><a href="contact.php">Contact</a></li>
    </ul>
  </div>
</footer>

<script>
  // Navbar scroll
  window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
  });

  // Mobile hamburger
  const toggle   = document.getElementById('navToggle');
  const navLinks = document.getElementById('navLinks');
  toggle.addEventListener('click', () => {
    const open = navLinks.classList.toggle('open');
    toggle.classList.toggle('open', open);
    toggle.setAttribute('aria-expanded', open);
  });
  navLinks.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
    navLinks.classList.remove('open');
    toggle.classList.remove('open');
    toggle.setAttribute('aria-expanded', 'false');
  }));

  // Reveal on scroll
  const revealEls = document.querySelectorAll('.reveal');
  const observer  = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        setTimeout(() => entry.target.classList.add('visible'), i * 80);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });
  revealEls.forEach(el => observer.observe(el));
</script>

</body>
</html>
