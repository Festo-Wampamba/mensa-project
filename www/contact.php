<?php
/**
 * ============================================================
 *  MENSA Tech Agency — Contact Page
 *  Author : Wampamba Festo (Lead Software Engineer & Architect)
 *  Handles GET display and POST form submission.
 * ============================================================
 */
declare(strict_types=1);

$flash = null;

// Handle non-JS POST fallback
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/db_connect.php';

    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email']     ?? '');
    $company  = trim($_POST['company']   ?? '');
    $phone    = trim($_POST['phone']     ?? '');
    $service  = trim($_POST['service']   ?? '');
    $budget   = trim($_POST['budget']    ?? '');
    $message  = trim($_POST['message']   ?? '');

    $errors = [];
    if ($fullName === '')           $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
    if ($service  === '')           $errors[] = 'Please select a service.';
    if (strlen($message) < 10)     $errors[] = 'Please describe your project (min 10 characters).';

    if (empty($errors)) {
        try {
            $pdo  = getDbConnection();
            $stmt = $pdo->prepare(
                'INSERT INTO contact_submissions
                    (full_name, email, company, phone, service, budget, message, ip_address)
                 VALUES
                    (:full_name, :email, :company, :phone, :service, :budget, :message, :ip)'
            );
            $stmt->execute([
                ':full_name' => $fullName,
                ':email'     => $email,
                ':company'   => $company ?: null,
                ':phone'     => $phone   ?: null,
                ':service'   => $service,
                ':budget'    => $budget  ?: null,
                ':message'   => $message,
                ':ip'        => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
            $flash = ['type' => 'success', 'msg' => 'Thank you! We\'ve received your enquiry and will be in touch within 24 hours.'];
        } catch (RuntimeException $e) {
            error_log('[MENSA CONTACT] ' . $e->getMessage());
            $flash = ['type' => 'error', 'msg' => 'Sorry, we could not save your message right now. Please try again shortly.'];
        }
    } else {
        $flash = ['type' => 'error', 'msg' => implode(' ', $errors)];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Contact MENSA Tech Agency — discuss your project, get a quote, or ask about our services." />
  <title>Contact Us — MENSA Tech Agency</title>
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
      <li><a href="services.php">Services</a></li>
      <li><a href="team.php">Our Team</a></li>
      <li><a href="requests.php">Job Cards</a></li>
      <li><a href="contact.php" class="btn-nav active">Contact Us</a></li>
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
      <span class="section-eyebrow">05 — Get In Touch</span>
      <h1 class="section-title" style="font-size:clamp(2.4rem,5vw,3.8rem);">
        Let's Build<br><em>Something Great.</em>
      </h1>
      <p class="section-subtitle">
        Whether you need managed hosting, a custom application, network design,
        or a full infrastructure overhaul — tell us about it.
      </p>
    </div>
  </div>
</section>

<!-- ── CONTACT GRID ──────────────────────────────────────── -->
<section class="section" style="padding-top:3rem;">
  <div class="container">
    <div class="contact-grid reveal">

      <!-- Left — Info -->
      <div class="contact-info">
        <h3>Have a project in mind?</h3>
        <p>
          Our team of 9 engineers is ready to take on your challenge.
          Fill in the form and one of our leads will get back to you
          within 24 hours.
        </p>

        <div class="contact-detail">
          <div class="contact-detail-icon">📍</div>
          <div class="contact-detail-text">
            <span>Location</span>
            <p>Nakawa MUBS</p>
          </div>
        </div>

        <div class="contact-detail">
          <div class="contact-detail-icon">📞</div>
          <div class="contact-detail-text">
            <span>Phone</span>
            <a href="tel:+256754230525">+256 754230525</a>
          </div>
        </div>

        <div class="contact-detail">
          <div class="contact-detail-icon">✉️</div>
          <div class="contact-detail-text">
            <span>Email</span>
            <a href="mailto:info@bbcmensa.com">info@bbcmensa.com</a>
          </div>
        </div>

        <div class="contact-detail">
          <div class="contact-detail-icon">🌐</div>
          <div class="contact-detail-text">
            <span>Domain</span>
            <p>bbcmensa.com</p>
          </div>
        </div>

        <div class="contact-socials">
          <a href="https://github.com/Festo-Wampamba" target="_blank" rel="noopener" class="social-link" title="GitHub">GH</a>
          <a href="#" class="social-link" title="LinkedIn">LI</a>
          <a href="#" class="social-link" title="Twitter/X">X</a>
        </div>

        <!-- Services quick list -->
        <div style="margin-top:2.5rem; padding-top:2rem; border-top:1px solid var(--border);">
          <p style="font-family:var(--font-mono);font-size:0.68rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.15em;margin-bottom:1rem;">Our Services</p>
          <div style="display:flex;flex-direction:column;gap:0.6rem;">
            <div style="font-size:0.88rem;color:var(--text-secondary);">🌐 Web Hosting &amp; Infrastructure</div>
            <div style="font-size:0.88rem;color:var(--text-secondary);">💻 Software Development</div>
            <div style="font-size:0.88rem;color:var(--text-secondary);">🖥️ System Administration</div>
            <div style="font-size:0.88rem;color:var(--text-secondary);">🔗 Networking &amp; DNS</div>
          </div>
        </div>
      </div>

      <!-- Right — Form -->
      <div class="contact-form" id="contactFormWrapper">

        <!-- Flash message (PHP fallback) -->
        <?php if ($flash !== null): ?>
          <?php if ($flash['type'] === 'success'): ?>
            <div class="form-success show">
              <div class="form-success-icon">✓</div>
              <h3>Message Sent!</h3>
              <p><?= htmlspecialchars($flash['msg']) ?></p>
            </div>
          <?php else: ?>
            <div class="form-error-msg show" id="phpErrorMsg">
              <?= htmlspecialchars($flash['msg']) ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>

        <!-- Form content (hidden on JS success, kept on PHP success) -->
        <?php if ($flash === null || $flash['type'] === 'error'): ?>
        <div id="formContent">
          <div class="form-error-msg" id="jsErrorMsg"></div>

          <div class="form-row">
            <div class="form-group">
              <label for="full_name">Full Name <span class="required">*</span></label>
              <input
                type="text"
                id="full_name"
                name="full_name"
                placeholder="e.g. John Doe"
                value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                autocomplete="name"
                required
              >
            </div>
            <div class="form-group">
              <label for="email">Email Address <span class="required">*</span></label>
              <input
                type="email"
                id="email"
                name="email"
                placeholder="john@example.com"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                autocomplete="email"
                required
              >
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="company">Company / Organisation</label>
              <input
                type="text"
                id="company"
                name="company"
                placeholder="Your company (optional)"
                value="<?= htmlspecialchars($_POST['company'] ?? '') ?>"
                autocomplete="organization"
              >
            </div>
            <div class="form-group">
              <label for="phone">Phone Number</label>
              <input
                type="tel"
                id="phone"
                name="phone"
                placeholder="+256 700 000 000 (optional)"
                value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                autocomplete="tel"
              >
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="service">Service Needed <span class="required">*</span></label>
              <select id="service" name="service" required>
                <option value="">Select a service...</option>
                <option value="Web Hosting"          <?= ($_POST['service'] ?? '') === 'Web Hosting'          ? 'selected' : '' ?>>🌐 Web Hosting</option>
                <option value="Software Development"  <?= ($_POST['service'] ?? '') === 'Software Development'  ? 'selected' : '' ?>>💻 Software Development</option>
                <option value="System Administration" <?= ($_POST['service'] ?? '') === 'System Administration' ? 'selected' : '' ?>>🖥️ System Administration</option>
                <option value="Networking"            <?= ($_POST['service'] ?? '') === 'Networking'            ? 'selected' : '' ?>>🔗 Networking</option>
                <option value="Multiple Services"     <?= ($_POST['service'] ?? '') === 'Multiple Services'     ? 'selected' : '' ?>>📦 Multiple Services</option>
                <option value="General Enquiry"       <?= ($_POST['service'] ?? '') === 'General Enquiry'       ? 'selected' : '' ?>>💬 General Enquiry</option>
              </select>
            </div>
            <div class="form-group">
              <label for="budget">Estimated Budget (UGX)</label>
              <select id="budget" name="budget">
                <option value="">Select budget range...</option>
                <option value="Under 500K"    <?= ($_POST['budget'] ?? '') === 'Under 500K'    ? 'selected' : '' ?>>Under UGX 500,000</option>
                <option value="500K–1M"       <?= ($_POST['budget'] ?? '') === '500K–1M'       ? 'selected' : '' ?>>UGX 500,000 – 1,000,000</option>
                <option value="1M–5M"         <?= ($_POST['budget'] ?? '') === '1M–5M'         ? 'selected' : '' ?>>UGX 1,000,000 – 5,000,000</option>
                <option value="5M–20M"        <?= ($_POST['budget'] ?? '') === '5M–20M'        ? 'selected' : '' ?>>UGX 5,000,000 – 20,000,000</option>
                <option value="20M+"          <?= ($_POST['budget'] ?? '') === '20M+'          ? 'selected' : '' ?>>UGX 20,000,000+</option>
                <option value="Let's discuss" <?= ($_POST['budget'] ?? '') === 'Let\'s discuss' ? 'selected' : '' ?>>Let's discuss</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="message">Project Details <span class="required">*</span></label>
            <textarea
              id="message"
              name="message"
              placeholder="Tell us about your project, goals, timeline, and any specific requirements..."
              required
            ><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
          </div>

          <button type="button" class="form-submit" id="submitBtn" onclick="submitForm()">
            Send Message →
          </button>
        </div>

        <!-- JS success state -->
        <div class="form-success" id="formSuccess">
          <div class="form-success-icon">✓</div>
          <h3>Message Sent!</h3>
          <p>Thank you for reaching out. A member of our team will get back to you within 24 hours.</p>
        </div>

        <?php endif; ?>
      </div><!-- /.contact-form -->

    </div><!-- /.contact-grid -->
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

  function submitForm() {
    const fullName = document.getElementById('full_name').value.trim();
    const email    = document.getElementById('email').value.trim();
    const service  = document.getElementById('service').value;
    const message  = document.getElementById('message').value.trim();
    const errorEl  = document.getElementById('jsErrorMsg');

    errorEl.classList.remove('show');

    const errors = [];
    if (!fullName)                          errors.push('Full name is required.');
    if (!email || !/\S+@\S+\.\S+/.test(email)) errors.push('A valid email address is required.');
    if (!service)                           errors.push('Please select a service.');
    if (message.length < 10)               errors.push('Please describe your project (min 10 characters).');

    if (errors.length > 0) {
      errorEl.textContent = errors.join(' ');
      errorEl.classList.add('show');
      errorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    const btn = document.getElementById('submitBtn');
    btn.textContent = 'Sending...';
    btn.disabled = true;

    const payload = {
      full_name: fullName,
      email:     email,
      company:   document.getElementById('company').value.trim(),
      phone:     document.getElementById('phone').value.trim(),
      service:   service,
      budget:    document.getElementById('budget').value,
      message:   message,
    };

    fetch('submit_contact.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body:    JSON.stringify(payload),
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        document.getElementById('formContent').style.display = 'none';
        document.getElementById('formSuccess').classList.add('show');
        window.scrollTo({ top: document.getElementById('contactFormWrapper').offsetTop - 100, behavior: 'smooth' });
      } else {
        errorEl.textContent = data.error || 'Something went wrong. Please try again.';
        errorEl.classList.add('show');
        btn.textContent = 'Send Message →';
        btn.disabled = false;
      }
    })
    .catch(() => {
      errorEl.textContent = 'Could not connect to the server. Please try again.';
      errorEl.classList.add('show');
      btn.textContent = 'Send Message →';
      btn.disabled = false;
    });
  }

  // Allow Enter in inputs (not textarea) to submit
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && e.target.tagName !== 'SELECT') {
      const btn = document.getElementById('submitBtn');
      if (btn && !btn.disabled) submitForm();
    }
  });
</script>

</body>
</html>
