<?php
/**
 * Tiny Guardians Baby Sitters — Contact Form Handler
 *
 * Receives a POST request from contact.html, validates the input,
 * then sends an email to the site owner via:
 *   - PRIMARY  : PHPMailer over Gmail SMTP (recommended, reliable)
 *   - FALLBACK : PHP mail()  (works on most cPanel hosts if SMTP is not configured)
 *
 * HOW TO CONFIGURE
 * ─────────────────
 * 1. Open this file.
 * 2. Fill in the CONFIGURATION section below (recipient, SMTP credentials, etc.).
 * 3. Upload this file (and the vendor/ folder if using PHPMailer) to your cPanel root.
 * 4. Test via the contact page.
 *
 * See CONTACT_FORM_SETUP.md in the repo for step-by-step instructions.
 */

/* ═══════════════════════════════════════════════════════════════
   CONFIGURATION — edit these values before uploading to cPanel
   ═══════════════════════════════════════════════════════════════ */

// Email address that receives contact-form messages (your Gmail)
define('RECIPIENT_EMAIL', 'tinyguardiansbabysitters@gmail.com');
define('RECIPIENT_NAME',  'Tiny Guardians Babysitters');

// "From" address shown in the delivered email.
// On cPanel this is usually an email account you created there
// (e.g. noreply@yourdomain.com). Using Gmail as From with mail() often
// lands in spam; use SMTP instead for Gmail.
define('FROM_EMAIL', 'noreply@yourdomain.com'); // ← change to your domain email
define('FROM_NAME',  'Tiny Guardians Website');

// ── SMTP settings (PHPMailer — recommended) ──────────────────
// Set SMTP_ENABLED to true and fill in your credentials to use PHPMailer.
// For Gmail: host = smtp.gmail.com, port = 587, security = tls
// App Password: https://myaccount.google.com/apppasswords (2-FA must be on)
define('SMTP_ENABLED',  false);              // ← set to true when ready
define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_PORT',     587);
define('SMTP_SECURITY', 'tls');             // 'tls' (port 587) or 'ssl' (port 465)
define('SMTP_USERNAME', 'your@gmail.com');  // ← your Gmail address
define('SMTP_PASSWORD', 'xxxx xxxx xxxx xxxx'); // ← 16-char App Password (remove spaces before saving)

// ── Limits ───────────────────────────────────────────────────
define('MAX_NAME_LEN',    100);
define('MAX_EMAIL_LEN',   254);
define('MAX_PHONE_LEN',    30);
define('MAX_MESSAGE_LEN', 3000);

// Simple IP-based rate limit: max submissions per time window
define('RATE_LIMIT_MAX',     5);   // max requests
define('RATE_LIMIT_WINDOW', 600);  // seconds (10 minutes)

/* ═══════════════════════════════════════════════════════════════
   SCRIPT — no need to edit below this line
   ═══════════════════════════════════════════════════════════════ */

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

// ── Helpers ──────────────────────────────────────────────────
function fail(string $msg, int $code = 400): never
{
    http_response_code($code);
    exit(htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'));
}

function sanitize(string $value, int $maxLen): string
{
    $value = trim($value);
    if (mb_strlen($value) > $maxLen) {
        $value = mb_substr($value, 0, $maxLen);
    }
    return $value;
}

// ── Honeypot check ───────────────────────────────────────────
// Bots fill in the hidden "website" field; real users leave it blank.
if (!empty($_POST['website'])) {
    // Return a convincing 200 so bots think they succeeded
    http_response_code(200);
    exit('Message sent successfully.');
}

// ── Rate limiting via session ─────────────────────────────────
session_start();

$now = time();
$ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if (!isset($_SESSION['rl_ip']) || $_SESSION['rl_ip'] !== $ip) {
    // New visitor or IP changed — reset counter
    $_SESSION['rl_ip']    = $ip;
    $_SESSION['rl_count'] = 0;
    $_SESSION['rl_start'] = $now;
}

if (($now - $_SESSION['rl_start']) > RATE_LIMIT_WINDOW) {
    // Window expired — reset
    $_SESSION['rl_count'] = 0;
    $_SESSION['rl_start'] = $now;
}

$_SESSION['rl_count']++;

if ($_SESSION['rl_count'] > RATE_LIMIT_MAX) {
    fail('Too many requests. Please wait a few minutes and try again.', 429);
}

// ── Collect & validate input ─────────────────────────────────
$name    = sanitize($_POST['name']    ?? '', MAX_NAME_LEN);
$email   = sanitize($_POST['email']   ?? '', MAX_EMAIL_LEN);
$phone   = sanitize($_POST['phone']   ?? '', MAX_PHONE_LEN);
$message = sanitize($_POST['message'] ?? '', MAX_MESSAGE_LEN);

if ($name === '' || mb_strlen($name) < 2) {
    fail('Please enter your full name.');
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Please enter a valid email address.');
}

if ($phone === '' || mb_strlen($phone) < 7) {
    fail('Please enter a valid phone number (at least 7 digits).');
}

if (mb_strlen($message) < 10) {
    fail('Message must be at least 10 characters.');
}

// ── Build the email body ──────────────────────────────────────
$subject = 'New contact form message — Tiny Guardians Baby Sitters';

$body = implode("\n", [
    "You have received a new message via the Tiny Guardians website contact form.",
    "",
    "─────────────────────────────────",
    "Name    : {$name}",
    "Email   : {$email}",
    "Phone   : {$phone}",
    "─────────────────────────────────",
    "",
    "Message:",
    $message,
    "",
    "─────────────────────────────────",
    "Sent from: " . ($_SERVER['SERVER_NAME'] ?? 'website'),
    "IP      : {$ip}",
    "Time    : " . date('Y-m-d H:i:s T'),
]);

// ── Send via PHPMailer SMTP (primary) ─────────────────────────
if (SMTP_ENABLED) {
    // PHPMailer can be installed two ways:
    //   A) Composer:  run `composer require phpmailer/phpmailer` then upload vendor/
    //   B) Manual:    download from https://github.com/PHPMailer/PHPMailer/releases
    //                 and place PHPMailer.php, SMTP.php, Exception.php in vendor/phpmailer/

    $autoload = __DIR__ . '/vendor/autoload.php';
    $manualLoad = __DIR__ . '/vendor/phpmailer/PHPMailer.php';

    if (file_exists($autoload)) {
        require_once $autoload;
    } elseif (file_exists($manualLoad)) {
        require_once $manualLoad;
        require_once __DIR__ . '/vendor/phpmailer/SMTP.php';
        require_once __DIR__ . '/vendor/phpmailer/Exception.php';
    } else {
        fail(
            'PHPMailer library not found. ' .
            'Please run `composer require phpmailer/phpmailer` or follow the ' .
            'manual install instructions in CONTACT_FORM_SETUP.md.',
            500
        );
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURITY === 'ssl'
            ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Gmail SMTP requires the From address to match the authenticated username
        $mail->setFrom(SMTP_USERNAME, FROM_NAME);
        $mail->addAddress(RECIPIENT_EMAIL, RECIPIENT_NAME);
        $mail->addReplyTo($email, $name);

        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();

        http_response_code(200);
        exit('Message sent successfully.');
    } catch (PHPMailer\PHPMailer\Exception $e) {
        // Log the real error server-side but return a safe message to the client
        error_log('PHPMailer error: ' . $mail->ErrorInfo);
        fail('Failed to send message. Please try again later.', 500);
    }
}

// ── Fallback: PHP mail() ──────────────────────────────────────
$headers   = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';
$headers[] = 'From: ' . FROM_NAME . ' <' . FROM_EMAIL . '>';
$headers[] = 'Reply-To: ' . $name . ' <' . $email . '>';

$sent = mail(RECIPIENT_EMAIL, $subject, $body, implode("\r\n", $headers));

if (!$sent) {
    error_log('mail() failed for contact form submission from ' . $ip);
    fail('Failed to send message. Please try again later.', 500);
}

http_response_code(200);
exit('Message sent successfully.');
