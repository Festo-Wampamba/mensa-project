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
  <meta name="description" content="MENSA Tech Agency — Enterprise web hosting, software development, system administration and networking." />
  <title>MENSA Tech Agency — Empowering Digital Infrastructure</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>

<nav class="navbar">
  <div class="navbar-inner">
    <a href="index.php" class="navbar-logo"><div class="logo-icon">M</div>MENSA</a>
    <ul class="navbar-links">
      <li><a href="index.php" class="active">Home</a></li>
      <li><a href="services.php">Services</a></li>
      <li><a href="team.php">Our Team</a></li>
      <li><a href="#contact" class="btn-nav">Contact Us</a></li>
    </ul>
  </div>
</nav>

<!-- HERO -->
<section class="hero" id="home">
  <div class="container">
    <div class="hero-content">
      <div class="hero-eyebrow"><span>Uganda's Premier Tech Agency</span></div>
      <h1>Engineering the<br><span class="highlight">Digital Future</span> — Reliably.</h1>
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
        <div class="stat-item"><div class="stat-number">99.9%</div><div class="stat-label">Uptime SLA</div></div>
        <div class="stat-item"><div class="stat-number">4</div><div class="stat-label">Core Services</div></div>
        <div class="stat-item"><div class="stat-number">9</div><div class="stat-label">Expert Engineers</div></div>
        <div class="stat-item"><div class="stat-number">24/7</div><div class="stat-label">Support</div></div>
      </div>
    </div>
  </div>
</section>

<!-- SERVICES -->
<section class="section section--alt" id="services">
  <div class="container">
    <span class="section-label">What We Do</span>
    <h2 class="section-title">Core Services</h2>
    <p class="section-subtitle">Four pillars of technology excellence, delivered by certified engineers with real-world infrastructure experience.</p>
    <div class="services-grid">
      <?php if (!empty($services)): ?>
        <?php foreach ($services as $svc): ?>
          <?php $features = json_decode($svc['features'] ?? '[]', true) ?? []; ?>
          <article class="service-card">
            <div class="service-icon"><?= htmlspecialchars($svc['icon']) ?></div>
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
        <article class="service-card"><div class="service-icon">🌐</div><h3 class="service-title">Web Hosting</h3><p class="service-desc">99.9% uptime SLA with 24/7 support.</p></article>
        <article class="service-card"><div class="service-icon">💻</div><h3 class="service-title">Software Development</h3><p class="service-desc">Full-cycle custom software engineering.</p></article>
        <article class="service-card"><div class="service-icon">🖥️</div><h3 class="service-title">System Administration</h3><p class="service-desc">Professional Linux server management.</p></article>
        <article class="service-card"><div class="service-icon">🔗</div><h3 class="service-title">Networking</h3><p class="service-desc">End-to-end network design and DNS.</p></article>
      <?php endif; ?>
    </div>
    <div style="text-align:center;margin-top:48px;">
      <a href="services.php" class="btn-secondary">View All Service Details →</a>
    </div>
  </div>
</section>

<!-- ABOUT -->
<section class="section" id="about">
  <div class="container">
    <div class="about-grid">
      <div class="about-text">
        <span class="section-label">Why MENSA</span>
        <h2 class="section-title">Infrastructure Built to Last</h2>
        <p>We don't just set up servers — we architect resilient, scalable, and secure digital infrastructure. Every configuration decision is made with long-term operational excellence in mind.</p>
        <p>From Dockerised microservices and Bind9 DNS management to hardened Apache configurations and MySQL architecture, MENSA engineers bring production-level thinking to every project.</p>
        <div class="about-features">
          <div class="about-feature"><div class="about-feature-icon">🐳</div><div><h4>Containerised Infrastructure</h4><p>Docker-first approach for portable, reproducible environments</p></div></div>
          <div class="about-feature"><div class="about-feature-icon">🔒</div><div><h4>Security by Design</h4><p>Least-privilege access, encrypted connections, hardened configs</p></div></div>
          <div class="about-feature"><div class="about-feature-icon">📡</div><div><h4>Full DNS Control</h4><p>Authoritative Bind9 DNS with forward & reverse zone management</p></div></div>
        </div>
      </div>
      <div>
        <div class="terminal-card">
          <div class="terminal-header">
            <div class="terminal-dot"></div><div class="terminal-dot"></div><div class="terminal-dot"></div>
            <span class="terminal-title">mensa@server:~$</span>
          </div>
          <div class="terminal-body">
            <div><span class="t-prompt">$ </span><span class="t-cmd">docker compose up -d</span></div>
            <div class="t-out">✔ Network mensa_net    Created</div>
            <div class="t-out">✔ Container mensa_db   Started</div>
            <div class="t-out">✔ Container mensa_web  Started</div>
            <div class="t-out">✔ Container mensa_dns  Started</div>
            <br>
            <div><span class="t-prompt">$ </span><span class="t-cmd">dig @172.28.0.53 mensa.local</span></div>
            <div class="t-out">mensa.local. 86400 IN A 172.28.0.10</div>
            <br>
            <div><span class="t-prompt">$ </span><span class="t-cmd">curl -I http://mensa.local</span></div>
            <div class="t-out">HTTP/1.1 200 OK</div>
            <div class="t-comment"># All systems operational ✔</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TEAM TEASER -->
<section class="section section--alt">
  <div class="container" style="text-align:center;">
    <span class="section-label">The People</span>
    <h2 class="section-title">Led by Experts</h2>
    <p class="section-subtitle" style="margin-inline:auto;">Our team of 9 engineers spans software architecture, system administration, networking, and full-stack development.</p>
    <a href="team.php" class="btn-primary">Meet the Full Team →</a>
  </div>
</section>

<!-- CONTACT -->
<section class="cta-section" id="contact">
  <div class="container">
    <div class="cta-inner">
      <span class="section-label">Get In Touch</span>
      <h2 class="section-title">Ready to Build Something Great?</h2>
      <p>Whether you need managed hosting, a custom application, or a full infrastructure overhaul — the MENSA team is ready to help.</p>
      <div class="hero-cta" style="justify-content:center;">
        <a href="mailto:info@mensa.local" class="btn-primary">info@mensa.local</a>
        <a href="team.php" class="btn-secondary">View Our Team</a>
      </div>
      <div class="contact-methods">
        <div class="contact-pill">📍 Kampala, Uganda</div>
        <div class="contact-pill">📞 +256 700 000 001</div>
        <div class="contact-pill">🌐 mensa.local</div>
      </div>
    </div>
  </div>
</section>

<footer>
  <div class="footer-inner">
    <p class="footer-copy">
      &copy; <?= date('Y') ?> MENSA Tech Agency &mdash;
      Architected by <strong style="color:var(--clr-accent);"><?= htmlspecialchars($lead['full_name'] ?? 'Wampamba Festo') ?></strong>,
      Lead Software Engineer &amp; Architect.
    </p>
    <ul class="footer-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="services.php">Services</a></li>
      <li><a href="team.php">Team</a></li>
    </ul>
  </div>
</footer>

</body>
</html>
