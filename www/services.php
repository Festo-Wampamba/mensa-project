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
  <title>Our Services — MENSA Tech Agency</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <style>
    .service-full {
      background: var(--clr-bg-card); border: 1px solid var(--clr-border);
      border-radius: var(--radius-lg); padding: 48px 40px;
      display: grid; grid-template-columns: 1fr 1fr; gap: 40px;
      align-items: start; transition: all var(--transition);
    }
    .service-full:hover { border-color: var(--clr-accent); box-shadow: var(--shadow-glow); }
    .service-full:nth-child(even) { direction: rtl; }
    .service-full:nth-child(even) > * { direction: ltr; }
    .service-num { font-family: var(--font-display); font-size: 4rem; font-weight: 800; color: rgba(0,212,255,0.07); line-height: 1; margin-bottom: 16px; }
    .features-list { list-style: none; display: flex; flex-direction: column; gap: 10px; }
    .features-list li {
      display: flex; align-items: center; gap: 12px;
      font-size: 0.9rem; color: var(--clr-text); padding: 8px 12px;
      background: rgba(0,212,255,0.04); border-radius: var(--radius-sm);
      border: 1px solid var(--clr-border);
    }
    .features-list li::before { content: '✓'; color: var(--clr-accent); font-weight: 700; flex-shrink: 0; }
    .features-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--clr-accent); margin-bottom: 14px; font-family: var(--font-display); }
    @media (max-width: 768px) {
      .service-full { grid-template-columns: 1fr; direction: ltr !important; }
      .service-full:nth-child(even) { direction: ltr; }
    }
  </style>
</head>
<body>

<nav class="navbar">
  <div class="navbar-inner">
    <a href="index.php" class="navbar-logo"><div class="logo-icon">M</div>MENSA</a>
    <ul class="navbar-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="services.php" class="active">Services</a></li>
      <li><a href="team.php">Our Team</a></li>
      <li><a href="index.php#contact" class="btn-nav">Contact Us</a></li>
    </ul>
  </div>
</nav>

<section class="section" style="padding-top:140px;padding-bottom:40px;">
  <div class="container">
    <span class="section-label">What We Offer</span>
    <h1 class="section-title">Enterprise Services,<br>Delivered by Engineers.</h1>
    <p class="section-subtitle">MENSA's four core service pillars — staffed by specialists who live and breathe infrastructure.</p>
  </div>
</section>

<section class="section" style="padding-top:20px;">
  <div class="container">
    <?php if ($dbError): ?>
      <div class="db-notice" style="margin-bottom:40px;">⚠ DB unavailable: <?= htmlspecialchars($dbError) ?></div>
    <?php endif; ?>

    <?php if (!empty($services)): ?>
      <div style="display:flex;flex-direction:column;gap:40px;">
        <?php foreach ($services as $i => $svc): ?>
          <?php $features = json_decode($svc['features'] ?? '[]', true) ?? []; ?>
          <article class="service-full">
            <div>
              <div class="service-num"><?= str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) ?></div>
              <div class="service-icon" style="margin-bottom:20px;"><?= htmlspecialchars($svc['icon']) ?></div>
              <h2 style="font-size:1.6rem;margin-bottom:16px;"><?= htmlspecialchars($svc['title']) ?></h2>
              <p style="color:var(--clr-text-muted);font-size:0.95rem;line-height:1.8;"><?= htmlspecialchars($svc['description']) ?></p>
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
        <article class="service-card"><div class="service-icon">🌐</div><h3 class="service-title">Web Hosting</h3><p class="service-desc">Enterprise-grade hosting with 99.9% SLA.</p></article>
        <article class="service-card"><div class="service-icon">💻</div><h3 class="service-title">Software Development</h3><p class="service-desc">Full-cycle custom applications.</p></article>
        <article class="service-card"><div class="service-icon">🖥️</div><h3 class="service-title">System Administration</h3><p class="service-desc">Professional Linux server management.</p></article>
        <article class="service-card"><div class="service-icon">🔗</div><h3 class="service-title">Networking</h3><p class="service-desc">End-to-end network design and DNS.</p></article>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="cta-section">
  <div class="container">
    <div class="cta-inner">
      <span class="section-label">Let's Talk</span>
      <h2 class="section-title">Start Your Project Today</h2>
      <p>Tell us what you're building and we'll design the right solution.</p>
      <div class="hero-cta" style="justify-content:center;">
        <a href="mailto:info@mensa.local" class="btn-primary">Get In Touch →</a>
        <a href="team.php" class="btn-secondary">Meet the Team</a>
      </div>
    </div>
  </div>
</section>

<footer>
  <div class="footer-inner">
    <p class="footer-copy">
      &copy; <?= date('Y') ?> MENSA Tech Agency &mdash;
      Architected by <strong style="color:var(--clr-accent);">Wampamba Festo</strong>,
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
