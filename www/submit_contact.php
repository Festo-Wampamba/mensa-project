<?php
/**
 * ============================================================
 *  MENSA Tech Agency — Contact Form AJAX Handler
 *  Author : Wampamba Festo (Lead Software Engineer & Architect)
 *  File   : www/submit_contact.php
 *
 *  SECURITY: Only accepts JSON XHR requests. Validates &
 *  sanitises all inputs before PDO parameterised insert.
 *  Returns JSON {success: bool, error?: string}.
 * ============================================================
 */
declare(strict_types=1);

// ── Only respond to XMLHttpRequest POST ─────────────────────
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest'
) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Forbidden.']);
    exit;
}

header('Content-Type: application/json');

// ── Parse JSON body ──────────────────────────────────────────
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode(['success' => false, 'error' => 'Invalid request payload.']);
    exit;
}

// ── Sanitise & extract fields ────────────────────────────────
$fullName = trim((string)($data['full_name'] ?? ''));
$email    = trim((string)($data['email']     ?? ''));
$company  = trim((string)($data['company']   ?? ''));
$phone    = trim((string)($data['phone']     ?? ''));
$service  = trim((string)($data['service']   ?? ''));
$budget   = trim((string)($data['budget']    ?? ''));
$message  = trim((string)($data['message']   ?? ''));

// ── Validate ─────────────────────────────────────────────────
$errors = [];

if ($fullName === '' || mb_strlen($fullName) > 120) {
    $errors[] = 'Full name is required (max 120 characters).';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 180) {
    $errors[] = 'A valid email address is required.';
}
if ($service === '') {
    $errors[] = 'Please select a service.';
}
if (mb_strlen($message) < 10 || mb_strlen($message) > 5000) {
    $errors[] = 'Project details must be between 10 and 5,000 characters.';
}

$allowedServices = [
    'Web Hosting', 'Software Development', 'System Administration',
    'Networking', 'Multiple Services', 'General Enquiry',
];
if ($service !== '' && !in_array($service, $allowedServices, true)) {
    $errors[] = 'Invalid service selected.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'error' => implode(' ', $errors)]);
    exit;
}

// ── Rate-limit: 3 submissions per IP per hour (soft) ─────────
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// ── Persist to DB ────────────────────────────────────────────
require_once __DIR__ . '/db_connect.php';

try {
    $pdo  = getDbConnection();

    // Soft rate-limit check
    $rlStmt = $pdo->prepare(
        'SELECT COUNT(*) FROM contact_submissions
         WHERE ip_address = :ip AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)'
    );
    $rlStmt->execute([':ip' => $ipAddress]);
    $recentCount = (int) $rlStmt->fetchColumn();

    if ($recentCount >= 3) {
        echo json_encode(['success' => false, 'error' => 'Too many submissions. Please wait a while before trying again.']);
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO contact_submissions
            (full_name, email, company, phone, service, budget, message, ip_address)
         VALUES
            (:full_name, :email, :company, :phone, :service, :budget, :message, :ip)'
    );

    $stmt->execute([
        ':full_name' => $fullName,
        ':email'     => $email,
        ':company'   => $company !== '' ? $company : null,
        ':phone'     => $phone   !== '' ? $phone   : null,
        ':service'   => $service,
        ':budget'    => $budget  !== '' ? $budget  : null,
        ':message'   => $message,
        ':ip'        => $ipAddress,
    ]);

    echo json_encode(['success' => true]);

} catch (RuntimeException $e) {
    error_log('[MENSA CONTACT SUBMIT] ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'A server error occurred. Please try again shortly.']);
}
