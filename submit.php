<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json; charset=utf-8');

function loadEnvironment() {
    if (!file_exists('vendor/autoload.php')) {
        throw new Exception('Composer autoload file not found. Run: composer install');
    }
    require 'vendor/autoload.php';

    if (file_exists('.env')) {
        $envFile = file_get_contents('.env');
        foreach (explode("\n", $envFile) as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, " '\"\t\n\r\0\x0B");
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

function sendResponse($success, $message, $statusCode) {
    http_response_code($statusCode);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

try {
    loadEnvironment();
} catch (Exception $e) {
    error_log('Configuration error: ' . $e->getMessage());
    sendResponse(false, 'Configuration error: ' . $e->getMessage(), 500);
}

function sendEmailViaSMTP($recipientEmail) {
    $mail = new PHPMailer(true);
    try {
        $smtpUser = $_ENV['SMTP_USER'] ?? '';
        $smtpPass = $_ENV['SMTP_PASSWORD'] ?? '';

        if (empty($smtpUser) || empty($smtpPass)) {
            throw new Exception('SMTP credentials not found in .env file');
        }

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($smtpUser, 'Email Sender');
        $mail->addAddress($recipientEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Test Email from PHP Mailer';
        $mail->Body    = '<h1>Hello!</h1><p>This is a test email sent from PHP Mailer using SMTP.</p>';
        $mail->AltBody = 'This is a test email sent from PHP Mailer using SMTP.';

        if (!$mail->send()) {
            throw new Exception('SMTP Error: ' . $mail->ErrorInfo);
        }

        return ['success' => true, 'message' => 'Email has been sent successfully to ' . htmlspecialchars($recipientEmail)];
    } catch (Exception $e) {
        error_log('PHPMailer Error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Email Error: ' . $e->getMessage()];
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method Not Allowed', 405);
}

try {
    $recipientEmail = trim($_POST['recipient_email'] ?? '');

    if (empty($recipientEmail)) {
        sendResponse(false, 'Email address is required', 400);
    }

    if (!isValidEmail($recipientEmail)) {
        sendResponse(false, 'Invalid email address format', 400);
    }

    $result = sendEmailViaSMTP($recipientEmail);
    $statusCode = $result['success'] ? 200 : 500;
    sendResponse($result['success'], $result['message'], $statusCode);

} catch (Exception $e) {
    error_log('Unexpected error: ' . $e->getMessage());
    sendResponse(false, 'Error: ' . $e->getMessage(), 500);
}