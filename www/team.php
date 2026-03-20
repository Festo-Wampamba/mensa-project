<?php
/**
 * ============================================================
 *  MENSA Tech Agency — Meet Our Team
 *  Author : Wampamba Festo (Lead Software Engineer & Architect)
 *  Fetches all 9 team members live from MySQL via PDO.
 * ============================================================
 */
declare(strict_types=1);
require_once __DIR__ . '/db_connect.php';

$members = [];
$dbError = null;

try {
    $pdo  = getDbConnection();
    $stmt = $pdo->query(
        'SELECT id, full_name, role, department, email, phone,
                reg_number, student_no, is_lead
         FROM   team_members
         ORDER  BY is_lead DESC, id ASC'
    );
    $members = $stmt->fetchAll();
} catch (RuntimeException $e) {
    $dbError = $e->getMessage();
}

function getInitials(string $name): string {
    $parts    = explode(' ', trim($name));
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $initials .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $initials ?: '?';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Meet the 9 engineers powering MENSA Tech Agency — led by Wampamba Festo, Lead Software Engineer and Architect." />
  <title>Meet Our Team — MENSA Tech Agency</title>
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
    <ul class="navbar-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="services.php">Services</a></li>
      <li><a href="team.php" class="active">Our Team</a></li>
      <li><a href="contact.php" class="btn-nav">Contact Us</a></li>
    </ul>
  </div>
</nav>

<!-- ── PAGE HEADER ───────────────────────────────────────── -->
<section class="section" style="padding-top:140px; padding-bottom:2rem; background:var(--bg-secondary);">
  <div class="container">
    <div class="reveal">
      <span class="section-eyebrow">04 — The People</span>
      <h1 class="section-title" style="font-size:clamp(2.4rem,5vw,3.8rem);">
        Meet Our <em>Team</em>
      </h1>
      <p class="section-subtitle">
        Nine engineers. Four disciplines. One shared goal: building infrastructure
        and software that performs when it matters most.
      </p>
      <div class="db-info">
        🗄️&nbsp; Live data —
        <strong style="color:var(--accent);">mensa_db.team_members</strong>
        via PDO &mdash; container
        <code style="background:rgba(0,0,0,0.3);padding:1px 6px;border-radius:2px;font-size:0.72rem;">mensa_db</code>
      </div>
    </div>
  </div>
</section>

<!-- ── TEAM GRID ──────────────────────────────────────────── -->
<section class="section" style="padding-top:2.5rem;">
  <div class="container">

    <?php if ($dbError): ?>
      <div class="db-notice reveal">
        <strong>⚠ Database Unavailable:</strong> <?= htmlspecialchars($dbError) ?><br>
        <small>Ensure the Docker containers are running: <code>docker compose up -d</code></small>
      </div>

    <?php elseif (empty($members)): ?>
      <div class="db-notice reveal">
        No team members found. Run <code>docker compose down -v &amp;&amp; docker compose up -d</code> to re-seed.
      </div>

    <?php else: ?>
      <div class="team-grid">
        <?php foreach ($members as $m): ?>
          <?php $isLead = (bool)$m['is_lead']; ?>
          <article class="team-card <?= $isLead ? 'is-lead' : '' ?> reveal">

            <?php if ($isLead): ?>
              <div class="badge-lead">⭐ Lead Engineer</div>
            <?php endif; ?>

            <div class="team-avatar"><?= htmlspecialchars(getInitials($m['full_name'])) ?></div>

            <h3 class="team-name"><?= htmlspecialchars($m['full_name']) ?></h3>
            <p class="team-role"><?= htmlspecialchars($m['role']) ?></p>
            <span class="team-dept"><?= htmlspecialchars($m['department']) ?></span>

            <div style="margin:0.5rem 0;">
              <?php if (!empty($m['reg_number'])): ?>
                <div class="team-meta">Reg: <?= htmlspecialchars($m['reg_number']) ?></div>
              <?php endif; ?>
              <?php if (!empty($m['student_no'])): ?>
                <div class="team-meta">No: <?= htmlspecialchars($m['student_no']) ?></div>
              <?php endif; ?>
            </div>

            <div class="team-contact">
              <?php if (!empty($m['email'])): ?>
                <div><a href="mailto:<?= htmlspecialchars($m['email']) ?>">✉ <?= htmlspecialchars($m['email']) ?></a></div>
              <?php endif; ?>
              <?php if (!empty($m['phone'])): ?>
                <div style="margin-top:3px;">📞 <?= htmlspecialchars($m['phone']) ?></div>
              <?php endif; ?>
            </div>

          </article>
        <?php endforeach; ?>
      </div>

      <p style="text-align:center;margin-top:3rem;font-family:var(--font-mono);font-size:0.72rem;color:var(--text-muted);">
        Displaying
        <strong style="color:var(--accent);"><?= count($members) ?></strong>
        engineers — query: <code>SELECT * FROM mensa_db.team_members ORDER BY is_lead DESC</code>
      </p>
    <?php endif; ?>

  </div>
</section>

<!-- ── CTA ───────────────────────────────────────────────── -->
<section class="cta-section">
  <div class="container">
    <div class="cta-inner reveal">
      <span class="section-eyebrow">Work With Us</span>
      <h2 class="section-title">Ready to Start a Project?</h2>
      <p>Get in touch with our team to discuss your infrastructure and software needs.</p>
      <div class="hero-cta" style="justify-content:center;">
        <a href="contact.php" class="btn-primary">Contact Us →</a>
        <a href="services.php" class="btn-secondary">View Services</a>
      </div>
    </div>
  </div>
</section>

<!-- ── FOOTER ─────────────────────────────────────────────── -->
<footer>
  <div class="footer-inner">
    <p class="footer-copy">
      &copy; <?= date('Y') ?> MENSA Tech Agency &mdash;
      Architected by <span class="accent-name">Wampamba Festo</span>,
      Lead Software Engineer &amp; Architect.
    </p>
    <ul class="footer-links">
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

  const revealEls = document.querySelectorAll('.reveal');
  const observer  = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        setTimeout(() => entry.target.classList.add('visible'), i * 60);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.08 });
  revealEls.forEach(el => observer.observe(el));
</script>

</body>
</html>
