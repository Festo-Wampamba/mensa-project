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
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>

<nav class="navbar">
  <div class="navbar-inner">
    <a href="index.php" class="navbar-logo"><div class="logo-icon">M</div>MENSA</a>
    <ul class="navbar-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="services.php">Services</a></li>
      <li><a href="team.php" class="active">Our Team</a></li>
      <li><a href="index.php#contact" class="btn-nav">Contact Us</a></li>
    </ul>
  </div>
</nav>

<!-- PAGE HEADER -->
<section class="section" style="padding-top:140px; padding-bottom:20px;">
  <div class="container">
    <span class="section-label">The People</span>
    <h1 class="section-title">Meet Our Team</h1>
    <p class="section-subtitle">
      Nine engineers. Four disciplines. One shared goal: building infrastructure
      and software that performs when it matters most.
    </p>

    <!-- Live DB badge — demonstrates PHP → MySQL connection -->
    <div class="db-info">
      🗄️&nbsp;
      Team data fetched live from
      <strong style="color:var(--clr-accent);">MySQL &rarr; mensa_db.team_members</strong>
      via PDO &mdash; container
      <code style="background:rgba(0,0,0,0.3);padding:1px 6px;border-radius:4px;font-size:0.78rem;">mensa_db</code>
    </div>
  </div>
</section>

<!-- TEAM GRID -->
<section class="section" style="padding-top:32px;">
  <div class="container">

    <?php if ($dbError): ?>
      <div class="db-notice">
        <strong>⚠ Database Unavailable:</strong> <?= htmlspecialchars($dbError) ?><br>
        <small>Ensure the Docker containers are running: <code>docker compose up -d</code></small>
      </div>

    <?php elseif (empty($members)): ?>
      <div class="db-notice">No team members found. Run <code>docker compose down -v && docker compose up -d</code> to re-seed.</div>

    <?php else: ?>
      <div class="team-grid">
        <?php foreach ($members as $m): ?>
          <?php $isLead = (bool)$m['is_lead']; ?>
          <article class="team-card <?= $isLead ? 'is-lead' : '' ?>">

            <?php if ($isLead): ?>
              <div class="badge-lead">⭐ Lead Engineer</div>
            <?php endif; ?>

            <div class="team-avatar"><?= htmlspecialchars(getInitials($m['full_name'])) ?></div>

            <h3 class="team-name"><?= htmlspecialchars($m['full_name']) ?></h3>
            <p class="team-role"><?= htmlspecialchars($m['role']) ?></p>
            <span class="team-dept"><?= htmlspecialchars($m['department']) ?></span>

            <!-- Registration & Student Numbers from DB -->
            <div style="margin:6px 0;">
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

      <p style="text-align:center;margin-top:48px;font-size:0.8rem;color:var(--clr-text-muted);">
        Displaying
        <strong style="color:var(--clr-accent);"><?= count($members) ?></strong>
        members — live query: <code>SELECT * FROM mensa_db.team_members ORDER BY is_lead DESC</code>
      </p>
    <?php endif; ?>

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
