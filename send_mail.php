<?php
/**
 * MindSprout Technologies — Contact Form Mailer
 * Uses PHPMailer via Composer
 * Sends to: info@mindsprout.tech
 *
 * Setup:
 *   1. Upload this file to your server root (same level as contact.html)
 *   2. Run: composer require phpmailer/phpmailer
 *   3. Fill in your SMTP credentials below
 *   4. Done — form submissions land in info@mindsprout.tech
 */

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// ── CORS (allow your domain only) ──────────────────────────────────
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://mindsprout.tech'); // change to your domain
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// ── Load PHPMailer via Composer ─────────────────────────────────────
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ── Sanitize & validate inputs ──────────────────────────────────────
function clean($val) {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

$fname   = clean($_POST['fname']   ?? '');
$lname   = clean($_POST['lname']   ?? '');
$email   = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone   = clean($_POST['phone']   ?? '');
$service = clean($_POST['service'] ?? 'Not specified');
$message = clean($_POST['message'] ?? '');

// Required field check
if (!$fname || !$lname || !$email || !$phone || !$message) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

// Email format check
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

$fullName = $fname . ' ' . $lname;

// ── SMTP Configuration ──────────────────────────────────────────────
// Fill these in with your cPanel / hosting SMTP details
// For cPanel: use mail.yourdomain.com as host
// For Gmail SMTP: use smtp.gmail.com, port 587, your Gmail + App Password

define('SMTP_HOST',     'mail.mindsprout.tech');  // your mail server
define('SMTP_USERNAME', 'info@mindsprout.tech');  // your email account
define('SMTP_PASSWORD', 'Evraj@3115');   // your email password
define('SMTP_PORT',     465);                     // 465 for SSL, 587 for TLS
define('SMTP_SECURE',   PHPMailer::ENCRYPTION_SMTPS); // SMTPS for 465, STARTTLS for 587
define('FROM_EMAIL',    'info@mindsprout.tech');
define('FROM_NAME',     'MindSprout Technologies');
define('TO_EMAIL',      'info@mindsprout.tech');
define('TO_NAME',       'MindSprout Technologies');

// ── Build & Send Email ──────────────────────────────────────────────
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port       = SMTP_PORT;

    // Sender & recipient
    $mail->setFrom(FROM_EMAIL, FROM_NAME);
    $mail->addAddress(TO_EMAIL, TO_NAME);

    // Reply-to — so you can hit Reply and it goes to the visitor
    $mail->addReplyTo($email, $fullName);

    // Email content — HTML body
    $mail->isHTML(true);
    $mail->Subject = "New Enquiry from {$fullName} — MindSprout Website";
    $mail->Body    = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <style>
            body { font-family: 'Manrope', Arial, sans-serif; background: #050810; color: #e8eeff; margin: 0; padding: 0; }
            .wrapper { max-width: 600px; margin: 0 auto; background: #0d1526; border-radius: 16px; overflow: hidden; }
            .header { background: linear-gradient(135deg, #4f7cff, #00c9ff); padding: 32px 36px; }
            .header h1 { margin: 0; font-size: 22px; color: #fff; font-weight: 700; }
            .header p { margin: 6px 0 0; font-size: 13px; color: rgba(255,255,255,0.8); }
            .body { padding: 32px 36px; }
            .field { margin-bottom: 20px; border-bottom: 1px solid rgba(99,130,255,0.1); padding-bottom: 20px; }
            .field:last-child { border-bottom: none; margin-bottom: 0; }
            .label { font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #4f7cff; margin-bottom: 6px; }
            .value { font-size: 15px; color: #e8eeff; line-height: 1.6; }
            .message-box { background: rgba(79,124,255,0.06); border: 1px solid rgba(79,124,255,0.15); border-radius: 10px; padding: 16px; margin-top: 8px; }
            .footer { padding: 20px 36px; border-top: 1px solid rgba(99,130,255,0.12); font-size: 12px; color: rgba(200,210,255,0.4); text-align: center; }
            a { color: #4f7cff; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class='wrapper'>
            <div class='header'>
                <h1>New Website Enquiry</h1>
                <p>Received from mindsprout.tech contact form</p>
            </div>
            <div class='body'>
                <div class='field'>
                    <div class='label'>Full Name</div>
                    <div class='value'>{$fullName}</div>
                </div>
                <div class='field'>
                    <div class='label'>Email Address</div>
                    <div class='value'><a href='mailto:{$email}'>{$email}</a></div>
                </div>
                <div class='field'>
                    <div class='label'>Phone Number</div>
                    <div class='value'>{$phone}</div>
                </div>
                <div class='field'>
                    <div class='label'>Interested In</div>
                    <div class='value'>{$service}</div>
                </div>
                <div class='field'>
                    <div class='label'>Message</div>
                    <div class='message-box value'>{$message}</div>
                </div>
            </div>
            <div class='footer'>
                MindSprout Technologies &nbsp;&bull;&nbsp; info@mindsprout.tech &nbsp;&bull;&nbsp; +91 869 910 2490<br>
                This email was sent from the contact form at mindsprout.tech
            </div>
        </div>
    </body>
    </html>
    ";

    // Plain text fallback
    $mail->AltBody = "New Enquiry — MindSprout Website\n\n"
        . "Name:     {$fullName}\n"
        . "Email:    {$email}\n"
        . "Phone:    {$phone}\n"
        . "Interest: {$service}\n\n"
        . "Message:\n{$message}\n\n"
        . "---\nMindSprout Technologies | info@mindsprout.tech";

    $mail->send();

    // ── Auto-reply to the visitor ───────────────────────────────────
    $autoReply = new PHPMailer(true);
    $autoReply->isSMTP();
    $autoReply->Host       = SMTP_HOST;
    $autoReply->SMTPAuth   = true;
    $autoReply->Username   = SMTP_USERNAME;
    $autoReply->Password   = SMTP_PASSWORD;
    $autoReply->SMTPSecure = SMTP_SECURE;
    $autoReply->Port       = SMTP_PORT;

    $autoReply->setFrom(FROM_EMAIL, FROM_NAME);
    $autoReply->addAddress($email, $fullName);
    $autoReply->isHTML(true);
    $autoReply->Subject = "We received your message — MindSprout Technologies";
    $autoReply->Body    = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <style>
            body { font-family: Arial, sans-serif; background: #050810; color: #e8eeff; margin: 0; padding: 0; }
            .wrapper { max-width: 600px; margin: 0 auto; background: #0d1526; border-radius: 16px; overflow: hidden; }
            .header { background: linear-gradient(135deg, #4f7cff, #00c9ff); padding: 32px 36px; text-align: center; }
            .header h1 { margin: 0; font-size: 22px; color: #fff; font-weight: 700; }
            .body { padding: 32px 36px; }
            .body p { font-size: 15px; color: #c8d2ff; line-height: 1.75; margin: 0 0 16px; }
            .body strong { color: #e8eeff; }
            .highlight { background: rgba(79,124,255,0.08); border: 1px solid rgba(79,124,255,0.2); border-radius: 10px; padding: 18px 20px; margin: 24px 0; font-size: 14px; color: #c8d2ff; line-height: 1.7; }
            .btn { display: inline-block; background: #4f7cff; color: #fff; text-decoration: none; padding: 12px 28px; border-radius: 30px; font-weight: 700; font-size: 14px; margin-top: 8px; }
            .footer { padding: 20px 36px; border-top: 1px solid rgba(99,130,255,0.12); font-size: 12px; color: rgba(200,210,255,0.4); text-align: center; }
            a { color: #4f7cff; }
        </style>
    </head>
    <body>
        <div class='wrapper'>
            <div class='header'>
                <h1>Thank you, {$fname}!</h1>
            </div>
            <div class='body'>
                <p>We've received your message and will get back to you within <strong>24 business hours</strong>.</p>
                <div class='highlight'>
                    <strong>Your enquiry:</strong> {$service}<br><br>
                    <strong>Your message:</strong><br>{$message}
                </div>
                <p>In the meantime, feel free to explore our services or internship programs:</p>
                <a href='https://mindsprout.tech/internships.html' class='btn'>View Internship Programs</a>
            </div>
            <div class='footer'>
                MindSprout Technologies &nbsp;&bull;&nbsp; <a href='mailto:info@mindsprout.tech'>info@mindsprout.tech</a> &nbsp;&bull;&nbsp; +91 869 910 2490<br>
                Mohali, Punjab, India
            </div>
        </div>
    </body>
    </html>
    ";
    $autoReply->AltBody = "Hi {$fname},\n\nThank you for reaching out to MindSprout Technologies.\n\nWe've received your message and will reply within 24 business hours.\n\nYour enquiry: {$service}\n\n---\nMindSprout Technologies\ninfo@mindsprout.tech | +91 869 910 2490";

    $autoReply->send();

    echo json_encode(['success' => true, 'message' => "Message sent! We'll reply within 24 hours."]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Mail could not be sent. Please email us directly at info@mindsprout.tech',
        'debug'   => $mail->ErrorInfo  // remove this line on production
    ]);
}
