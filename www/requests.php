<?php
/**
 * ============================================================
 *  MENSA Tech Agency — Digital Job Card Dashboard
 *  Author : Wampamba Festo (Lead Software Engineer & Architect)
 *  File   : www/requests.php
 *
 *  Internal dashboard for viewing client contact submissions
 *  as "Job Cards". Supports marking submissions as read.
 * ============================================================
 */
declare(strict_types=1);
require_once __DIR__ . '/db_connect.php';

$dbError = null;

// ── POST Handler: Mark a submission as read ──────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['mark_read_id'])) {
    try {
        $pdo  = getDbConnection();
        $stmt = $pdo->prepare("UPDATE contact_submissions SET status = 'read' WHERE id = ? AND status = 'new'");
        $stmt->execute([(int) $_POST['mark_read_id']]);
    } catch (RuntimeException $e) {
        error_log('[MENSA] Failed to mark submission read: ' . $e->getMessage());
    }
    header('Location: requests.php');
    exit;
}

// ── Fetch all submissions ────────────────────────────────────
$submissions = [];
try {
    $pdo  = getDbConnection();
    $stmt = $pdo->query(
        'SELECT id, full_name, email, company, phone, service,
                budget, message, status, created_at
         FROM   contact_submissions
         ORDER  BY created_at DESC'
    );
    $submissions = $stmt->fetchAll();
} catch (RuntimeException $e) {
    $dbError = $e->getMessage();
}

$newCount = 0;
foreach ($submissions as $s) {
    if ($s['status'] === 'new') $newCount++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Job Cards — MENSA Tech Agency</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <style>
    /* ── Job Card Grid ──────────────────────────────────────── */
    .jc-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
      gap: 1.5rem;
    }

    .jc-card {
      position: relative;
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      padding: 1.8rem 1.6rem 1.4rem;
      transition: border-color var(--transition), box-shadow var(--transition);
    }
    .jc-card:hover {
      border-color: var(--border-light);
      box-shadow: 0 4px 24px rgba(0,0,0,0.25);
    }

    /* ── Notification Dot ───────────────────────────────────── */
    .jc-dot {
      position: absolute;
      top: 14px;
      right: 14px;
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: var(--accent);
      box-shadow: 0 0 6px var(--accent), 0 0 14px rgba(201, 168, 76, 0.35);
      animation: jcPulse 2s ease-in-out infinite;
    }
    @keyframes jcPulse {
      0%, 100% { box-shadow: 0 0 6px var(--accent), 0 0 14px rgba(201, 168, 76, 0.35); }
      50%      { box-shadow: 0 0 10px var(--accent), 0 0 24px rgba(201, 168, 76, 0.5); }
    }

    /* ── Badge ──────────────────────────────────────────────── */
    .jc-badge {
      display: inline-block;
      font-family: var(--font-mono);
      font-size: 0.68rem;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: var(--accent);
      background: var(--accent-glow);
      border: 1px solid rgba(201, 168, 76, 0.18);
      padding: 0.2rem 0.65rem;
      border-radius: var(--radius-sm);
      margin-bottom: 1rem;
    }

    /* ── Card Content ───────────────────────────────────────── */
    .jc-name {
      font-family: var(--font-display);
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 0.3rem;
    }
    .jc-meta {
      font-size: 0.82rem;
      color: var(--text-secondary);
      margin-bottom: 0.15rem;
    }
    .jc-meta a { color: var(--accent); }
    .jc-message {
      margin-top: 1rem;
      padding-top: 1rem;
      border-top: 1px solid var(--border);
      font-size: 0.9rem;
      color: var(--text-secondary);
      line-height: 1.75;
    }

    /* ── Action Row ─────────────────────────────────────────── */
    .jc-actions {
      margin-top: 1.2rem;
      display: flex;
      align-items: center;
      gap: 0.6rem;
    }
    .jc-btn-read {
      font-family: var(--font-mono);
      font-size: 0.72rem;
      letter-spacing: 0.05em;
      color: var(--bg-primary);
      background: var(--accent);
      border: none;
      padding: 0.4rem 1rem;
      border-radius: var(--radius-sm);
      cursor: pointer;
      transition: background var(--transition);
    }
    .jc-btn-read:hover { background: #dbb85a; }
    .jc-reviewed {
      font-family: var(--font-mono);
      font-size: 0.72rem;
      letter-spacing: 0.05em;
      color: var(--success);
    }

    /* ── Summary Bar ────────────────────────────────────────── */
    .jc-summary {
      display: flex;
      gap: 2rem;
      flex-wrap: wrap;
      margin-bottom: 2rem;
      font-family: var(--font-mono);
      font-size: 0.78rem;
      color: var(--text-secondary);
      letter-spacing: 0.04em;
    }
    .jc-summary strong { color: var(--text-primary); }
    .jc-summary .jc-new-count { color: var(--accent); }

    /* ── Empty State ────────────────────────────────────────── */
    .jc-empty {
      text-align: center;
      padding: 4rem 2rem;
      color: var(--text-muted);
      font-size: 0.95rem;
    }

    @media (max-width: 480px) {
      .jc-grid { grid-template-columns: 1fr; }
    }
  </style>
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
      <li><a href="services.php">Services</a></li>
      <li><a href="team.php">Our Team</a></li>
      <li><a href="requests.php" class="active">Job Cards</a></li>
      <li><a href="contact.php" class="btn-nav">Contact Us</a></li>
    </ul>
    <button class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<!-- ── PAGE HEADER ───────────────────────────────────────── -->
<section class="section" style="padding-top:140px; padding-bottom:2rem; background:var(--bg-secondary);">
  <div class="container">
    <div class="reveal">
      <span class="section-eyebrow">05 — Internal</span>
      <h1 class="section-title" style="font-size:clamp(2.4rem,5vw,3.8rem);">
        Digital <em>Job Cards</em>
      </h1>
      <p class="section-subtitle">
        Client enquiries and project requests — review, triage, and respond.
      </p>
    </div>
  </div>
</section>

<!-- ── JOB CARDS ──────────────────────────────────────────── -->
<section class="section">
  <div class="container">

    <?php if ($dbError): ?>
      <div style="background:rgba(248,113,113,0.08); border:1px solid var(--error); padding:1rem 1.4rem; border-radius:var(--radius-md); color:var(--error); font-size:0.9rem; margin-bottom:2rem;">
        Database unavailable: <?= htmlspecialchars($dbError) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($submissions)): ?>
      <div class="jc-summary">
        <span>Total: <strong><?= count($submissions) ?></strong></span>
        <span>New: <strong class="jc-new-count"><?= $newCount ?></strong></span>
        <span>Reviewed: <strong><?= count($submissions) - $newCount ?></strong></span>
      </div>

      <div class="jc-grid">
        <?php foreach ($submissions as $sub): ?>
          <div class="jc-card reveal">
            <?php if ($sub['status'] === 'new'): ?>
              <span class="jc-dot" title="New submission"></span>
            <?php endif; ?>

            <span class="jc-badge"><?= htmlspecialchars($sub['service']) ?></span>

            <div class="jc-name"><?= htmlspecialchars($sub['full_name']) ?></div>

            <div class="jc-meta">
              <a href="mailto:<?= htmlspecialchars($sub['email']) ?>"><?= htmlspecialchars($sub['email']) ?></a>
            </div>

            <?php if (!empty($sub['phone'])): ?>
              <div class="jc-meta"><?= htmlspecialchars($sub['phone']) ?></div>
            <?php endif; ?>

            <?php if (!empty($sub['company'])): ?>
              <div class="jc-meta"><?= htmlspecialchars($sub['company']) ?></div>
            <?php endif; ?>

            <?php if (!empty($sub['budget'])): ?>
              <div class="jc-meta">Budget: <?= htmlspecialchars($sub['budget']) ?></div>
            <?php endif; ?>

            <div class="jc-meta" style="margin-top:0.5rem; color:var(--text-muted); font-family:var(--font-mono); font-size:0.75rem;">
              <?= date('d M Y · H:i', strtotime($sub['created_at'])) ?>
            </div>

            <div class="jc-message"><?= nl2br(htmlspecialchars($sub['message'])) ?></div>

            <div class="jc-actions">
              <?php if ($sub['status'] === 'new'): ?>
                <form method="POST" action="requests.php">
                  <input type="hidden" name="mark_read_id" value="<?= (int) $sub['id'] ?>">
                  <button type="submit" class="jc-btn-read">Mark as Read</button>
                </form>
              <?php else: ?>
                <span class="jc-reviewed">[&#10003;] Reviewed</span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    <?php else: ?>
      <div class="jc-empty">
        <p>No client submissions yet. They will appear here once someone uses the contact form.</p>
      </div>
    <?php endif; ?>

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
        setTimeout(() => entry.target.classList.add('visible'), i * 60);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.08 });
  revealEls.forEach(el => observer.observe(el));
</script>

</body>
</html>
