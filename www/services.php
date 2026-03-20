<?php
/**
 * ============================================================
 *  MENSA Tech Agency — Services Page
 *  Author : Wampamba Festo (Lead Software Engineer & Architect)
 * ============================================================
 */
declare(strict_types=1);
require_once __DIR__ . '/db_connect.php';

$services = [];
$dbError  = null;

try {
    $pdo      = getDbConnection();
    $stmt     = $pdo->query('SELECT * FROM services ORDER BY sort_order ASC');
    $services = $stmt->fetchAll();
} catch (RuntimeException $e) {
    $dbError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="MENSA Tech Agency — Enterprise services: Web Hosting, Software Development, System Administration, and Networking." />
  <title>Our Services — MENSA Tech Agency</title>
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
      <li><a href="index.php">Home</a></li>
      <li><a href="services.php" class="active">Services</a></li>
      <li><a href="team.php">Our Team</a></li>
      <li><a href="contact.php" class="btn-nav">Contact Us</a></li>
    </ul>
    <button class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<!-- ── PAGE HEADER ───────────────────────────────────────── -->
<section class="section" style="padding-top:140px; padding-bottom:3rem; background:var(--bg-secondary);">
  <div class="container">
    <div class="reveal">
      <span class="section-eyebrow">02 — What We Offer</span>
      <h1 class="section-title" style="font-size:clamp(2.4rem,5vw,3.8rem);">
        Enterprise Services,<br><em>Delivered by Engineers.</em>
      </h1>
      <p class="section-subtitle">
        Four core service pillars — staffed by specialists who live and breathe
        infrastructure, software, and networks.
      </p>
    </div>
  </div>
</section>

<!-- ── SERVICES LIST ─────────────────────────────────────── -->
<section class="section" style="padding-top:3rem;">
  <div class="container">

    <?php if ($dbError): ?>
      <div class="db-notice reveal" style="margin-bottom:3rem;">
        ⚠ Database unavailable: <?= htmlspecialchars($dbError) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($services)): ?>
      <div style="display:flex; flex-direction:column; gap:2rem;">
        <?php foreach ($services as $i => $svc): ?>
          <?php $features = json_decode($svc['features'] ?? '[]', true) ?? []; ?>
          <article class="service-full reveal">
            <div>
              <div class="service-num"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></div>
              <div class="service-icon" style="margin-bottom:1.5rem;"><?= htmlspecialchars($svc['icon']) ?></div>
              <h2 style="font-family:var(--font-display);font-size:1.7rem;font-weight:700;letter-spacing:-0.03em;margin-bottom:1rem;">
                <?= htmlspecialchars($svc['title']) ?>
              </h2>
              <p style="color:var(--text-secondary);font-size:0.95rem;line-height:1.85;">
                <?= htmlspecialchars($svc['description']) ?>
              </p>
            </div>
            <div>
              <?php if (!empty($features)): ?>
                <p class="features-label">What's Included</p>
                <ul class="features-list">
                  <?php foreach ($features as $f): ?>
                    <li><?= htmlspecialchars($f) ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

    <?php else: ?>
      <div class="services-grid">
        <article class="service-card reveal">
          <div class="service-number">01</div>
          <div class="service-icon">🌐</div>
          <h3 class="service-title">Web Hosting</h3>
          <p class="service-desc">Enterprise-grade hosting with 99.9% SLA.</p>
        </article>
        <article class="service-card reveal">
          <div class="service-number">02</div>
          <div class="service-icon">💻</div>
          <h3 class="service-title">Software Development</h3>
          <p class="service-desc">Full-cycle custom applications.</p>
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
      </div>
    <?php endif; ?>

  </div>
</section>

<!-- ── CTA ───────────────────────────────────────────────── -->
<section class="cta-section">
  <div class="container">
    <div class="cta-inner reveal">
      <span class="section-eyebrow">Let's Talk</span>
      <h2 class="section-title">Start Your Project Today</h2>
      <p>Tell us what you're building and we'll engineer the right solution.</p>
      <div class="hero-cta" style="justify-content:center;">
        <a href="contact.php" class="btn-primary">Get In Touch →</a>
        <a href="team.php" class="btn-secondary">Meet the Team</a>
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
  window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
  });

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

  const revealEls = document.querySelectorAll('.reveal');
  const observer  = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        setTimeout(() => entry.target.classList.add('visible'), i * 80);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.08 });
  revealEls.forEach(el => observer.observe(el));
</script>

</body>
</html>
