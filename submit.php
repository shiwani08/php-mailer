<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    if (!file_exists('vendor/autoload.php')) {
        throw new Exception('Composer autoload file not found. Run: composer install');
    }
    require 'vendor/autoload.php';

    // Load environment variables from .env file
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
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    $errorMsg = 'Configuration error: ' . $e->getMessage();
    error_log($errorMsg);
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
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

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $recipientEmail = trim($_POST['recipient_email'] ?? '');

        if (empty($recipientEmail)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email address is required']);
        } elseif (!isValidEmail($recipientEmail)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid email address format']);
        } else {
            $response = sendEmailViaSMTP($recipientEmail);
            http_response_code($response['success'] ? 200 : 500);
            echo json_encode($response);
        }
    } catch (Exception $e) {
        http_response_code(500);
        $errorMsg = 'Error: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')';
        error_log('Unexpected error in submit.php: ' . $errorMsg);
        echo json_encode(['success' => false, 'message' => $errorMsg]);
    }
    exit;
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}