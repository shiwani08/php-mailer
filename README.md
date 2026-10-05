# PHP Email Sender with SMTP

A simple, clean email sending application built with PHP and PHPMailer that demonstrates SMTP email functionality using Gmail's SMTP server.

## Features

- 📧 Send emails via Gmail SMTP
- ✅ Email validation (frontend & backend)
- 🔒 Secure credential management with `.env` file
- 📱 Responsive web interface
- 🛠️ Detailed error messages for debugging
- ⚡ Clean, refactored code following SOLID principles

## Tech Stack

- **Frontend:** HTML, CSS, Vanilla JavaScript
- **Backend:** PHP 7.4+
- **Email Library:** PHPMailer
- **Package Manager:** Composer

## Prerequisites

- XAMPP (Apache, PHP, MySQL)
- Composer installed globally
- Gmail account with [App Password](https://myaccount.google.com/apppasswords) enabled
- Basic knowledge of PHP and command line

## Installation

### 1. Clone or Download Project

```bash
cd C:\xampp\htdocs\php-mailing
```

### 2. Install Dependencies

```bash
composer install
```

This installs PHPMailer automatically from the `composer.json` file.

### 3. Configure Environment Variables

Create a `.env` file in the project root:

```bash
SMTP_USER = 'your-email@gmail.com'
SMTP_PASSWORD = 'your-app-password'
```

> **⚠️ Important:** 
> - Use your Gmail address for `SMTP_USER`
> - Use an [App Password](https://myaccount.google.com/apppasswords) (not your regular password)
> - Never commit `.env` file (already in `.gitignore`)

### 4. Start XAMPP

- Open XAMPP Control Panel
- Click **Start** on Apache
- Click **Start** on MySQL

### 5. Access the Application

Open your browser and go to:
```
http://localhost/php-mailing/
```

## Project Structure

```
php-mailing/
├── index.php           # Frontend form & JavaScript
├── submit.php          # Backend SMTP logic
├── style.css           # Styling
├── .env                # Environment variables (credentials)
├── .gitignore          # Git ignore rules
├── composer.json       # PHP dependencies
├── composer.lock       # Locked dependency versions
├── vendor/             # Installed packages (auto-generated)
├── error.log           # Server-side error logs
└── README.md           # This file
```

## How It Works

### Frontend Flow (index.php)

1. User enters email address in form
2. Form is submitted via JavaScript `fetch()`
3. Frontend validates email format
4. Request sent to `submit.php` as FormData
5. Response displayed as success/error message

### Backend Flow (submit.php)

1. Receives POST request with `recipient_email`
2. Loads environment variables from `.env`
3. Validates email format (backend validation)
4. Creates PHPMailer instance
5. Configures Gmail SMTP settings
6. Sends email via SMTP
7. Returns JSON response with status & message

### SMTP Configuration

```php
// Gmail SMTP Settings
Host: smtp.gmail.com
Port: 587
Encryption: STARTTLS
Authentication: Required
```

## Usage

1. **Open the form:** http://localhost/php-mailing/
2. **Enter recipient email:** Any valid email address
3. **Click "Send Email"**
4. **Check result:**
   - ✅ Success message if email sent
   - ❌ Error message if something went wrong

## Debugging

### View Console Errors

1. Open browser **DevTools** (F12)
2. Go to **Console** tab
3. Submit form and check for error messages

### View Server Errors

Check `error.log` file in project root for server-side errors:

```bash
tail -f error.log
```

### Common Issues

| Issue | Solution |
|-------|----------|
| `Configuration error: Composer autoload file not found` | Run `composer install` |
| `SMTP credentials not found in .env file` | Create `.env` with valid credentials |
| `SMTP Error: Could not authenticate` | Check Gmail App Password |
| `HTTP Error 500` | Check browser console & `error.log` |
| Entire PHP code displayed | Restart Apache, ensure PHP is running |

## Gmail Setup Guide

### Get Gmail App Password

1. Go to [Google Account Security](https://myaccount.google.com/security)
2. Enable **2-Step Verification** (if not already)
3. Go to [App Passwords](https://myaccount.google.com/apppasswords)
4. Select **Mail** and **Windows Computer**
5. Copy the 16-character password
6. Add to `.env` file as `SMTP_PASSWORD`

## Code Architecture

### Response Helper Function

```php
function sendResponse($success, $message, $statusCode) {
    http_response_code($statusCode);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}
```

Used consistently throughout for all API responses (DRY principle).

### Environment Loading

```php
function loadEnvironment() {
    // Manually parse .env file
    // Loads SMTP_USER and SMTP_PASSWORD into $_ENV
}
```

Extracts initialization logic for better organization.

### Email Validation

```php
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
```

Reusable validation function used on backend.

### Guard Clause Pattern

```php
// Early exit for validation errors
if (empty($recipientEmail)) {
    sendResponse(false, 'Email address is required', 400);
}
```

Makes code more readable - no deep nesting.

## PHPMailer Configuration

```php
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = $smtpUser;        // From .env
$mail->Password = $smtpPass;        // From .env
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->setFrom($smtpUser, 'Email Sender');
$mail->addAddress($recipientEmail);
$mail->isHTML(true);
$mail->Subject = 'Test Email from PHP Mailer';
$mail->Body = '<h1>Hello!</h1><p>This is a test email sent from PHP Mailer using SMTP.</p>';
$mail->AltBody = 'Plain text version';

$mail->send();
```

## Best Practices Applied

- 🔒 **Security:** Credentials in `.env`, not in code
- 📝 **Logging:** Errors logged to `error.log` file
- ✅ **Validation:** Both frontend & backend email checks
- 🎯 **DRY:** Helper functions eliminate code duplication
- 📊 **JSON API:** Consistent response format for all endpoints
- 🧹 **Clean Code:** SOLID principles, readable flow, guard clauses

## Customization

### Change Email Subject & Body

Edit `submit.php` in `sendEmailViaSMTP()`:

```php
$mail->Subject = 'Your Custom Subject';
$mail->Body = '<h1>Your custom HTML</h1>';
$mail->AltBody = 'Your plain text version';
```

### Use Different SMTP Provider

Change SMTP settings in `sendEmailViaSMTP()`:

```php
$mail->Host = 'smtp.provider.com';  // Your SMTP host
$mail->Port = 587;                   // Your SMTP port
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
```

### Add More Form Fields

1. Update HTML form in `index.php` with new inputs
2. Update `index.php` JavaScript to include new fields in FormData
3. Handle new fields in `submit.php` POST handler

## Testing

### Send Test Email

```
1. Start XAMPP (Apache & MySQL)
2. Open http://localhost/php-mailing/
3. Enter your email address
4. Click "Send Email"
5. Check your inbox
```

### Test Error Scenarios

- **Leave email blank** → "Email address is required"
- **Enter invalid email** → "Invalid email address format"
- **Wrong SMTP password** → Error details in browser console
- **No .env file** → "SMTP credentials not found"

## Troubleshooting Checklist

- [ ] XAMPP Apache is running
- [ ] `.env` file exists with valid credentials
- [ ] `composer install` has been run
- [ ] Gmail App Password is configured
- [ ] Check browser console (F12) for detailed errors
- [ ] Check `error.log` file for server-side errors
- [ ] Verify PHP files have no UTF-8 BOM encoding

## File Permissions

If you get permission errors on Windows:

```bash
# Run Command Prompt as Administrator
icacls "C:\xampp\htdocs\php-mailing" /grant Everyone:F /T
```

## Security Best Practices

⚠️ **Important:**

- ✅ Never commit `.env` file (use `.gitignore`)
- ✅ Never hardcode credentials in source code
- ✅ Use Gmail App Passwords instead of main password
- ✅ Validate all user input (frontend & backend)
- ✅ Log errors to file, don't expose in production
- ✅ Use STARTTLS/TLS for encryption
- ✅ Sanitize user input with `htmlspecialchars()`

## Useful Resources

- [PHPMailer GitHub](https://github.com/PHPMailer/PHPMailer)
- [PHPMailer Documentation](https://phpmailer.worldsecure.net/)
- [Gmail App Passwords](https://myaccount.google.com/apppasswords)
- [Gmail SMTP Settings Guide](https://support.google.com/mail/answer/7126229)
- [Composer Documentation](https://getcomposer.org/doc/)
- [SMTP Protocol Basics](https://en.wikipedia.org/wiki/Simple_Mail_Transfer_Protocol)

## Learning Outcomes

This project demonstrates:

- ✅ How SMTP works with Gmail
- ✅ PHPMailer library usage
- ✅ Environment variable management
- ✅ Frontend-backend communication with fetch API
- ✅ JSON API design
- ✅ Email validation (regex & filters)
- ✅ Error handling and logging
- ✅ Clean code principles (DRY, SOLID)
- ✅ Security best practices

## License

MIT License - Feel free to use this project for learning and development.

## Author

Created with ❤️ using PHP, PHPMailer, and SMTP

---

**Need help?** Check the Debugging section above or review the `error.log` file for detailed server-side errors.
