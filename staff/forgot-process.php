<?php
ob_start(); // Start output buffering to prevent "headers already sent" errors
session_start();
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);
include __DIR__ . '/../includes/dbconnection.php';
include __DIR__ . '/../includes/mail_config.php';

// Try to load PHPMailer from vendor if present
$hasPHPMailer = false;
$phpmailerSrc = __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
$phpmailerException = __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
$phpmailerSMTP = __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
if (file_exists($phpmailerSrc)) {
    if (file_exists($phpmailerException))
        require_once $phpmailerException;
    if (file_exists($phpmailerSMTP))
        require_once $phpmailerSMTP;
    require_once $phpmailerSrc;
    $hasPHPMailer = class_exists('PHPMailer\\PHPMailer\\PHPMailer');
}

function send_otp_email($toEmail, $code, $userName = 'Staff')
{
    global $hasPHPMailer, $MAIL_HOST, $MAIL_USERNAME, $MAIL_PASSWORD, $MAIL_PORT, $MAIL_ENCRYPTION, $MAIL_FROM, $MAIL_FROM_NAME;

    $currentYear = date('Y');
    $subject = 'Your Student Profiling System Staff Password Reset Code';
    
    $bodyHtml = <<<EOT
<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
    <div style="background-color: #f4f4f4; padding: 20px; text-align: center; border-bottom: 1px solid #ddd;">
        <h2 style="color: #333; margin: 0;">Staff Password Reset Request</h2>
    </div>
    <div style="padding: 30px;">
        <p>Hi {$userName},</p>
        <p>We recently received a request to reset the password for your Student Profiling System staff account. To complete this process, please use the following One-Time Password (OTP) code:</p>
        <div style="background-color: #e0f7fa; border-left: 5px solid #00bcd4; padding: 15px; margin: 20px 0; text-align: center; font-size: 24px; font-weight: bold; color: #00796b; border-radius: 4px;">
            {$code}
        </div>
        <p>This code is valid for 15 minutes. For your security, never share this code with anyone.</p>
        <p>If you're having trouble entering the code, you can try copying and pasting it manually.</p>
        <p>If you did not request a password reset, you can safely ignore this email.</p>
        <p>Thank you,<br>The Student Profiling System Team</p>
    </div>
    <div style="background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #ddd;">
        <p>&copy; {$currentYear} Student Profiling System. All rights reserved.</p>
    </div>
</div>
EOT;

    $bodyText = <<<EOT
Hi {$userName},

We recently received a request to reset the password for your Student Profiling System staff account. To complete this process, please use the following One-Time Password (OTP) code:

{$code}

This code is valid for 15 minutes. For your security, never share this code with anyone.
If you did not request a password reset, you can safely ignore this email.
Thank you, The Student Profiling System Team.
EOT;

    if ($hasPHPMailer) {
        try {
            if (empty($MAIL_FROM) && !empty($MAIL_USERNAME))
                $MAIL_FROM = $MAIL_USERNAME;
            if (empty($MAIL_FROM_NAME))
                $MAIL_FROM_NAME = 'StudentMS';

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $MAIL_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = $MAIL_USERNAME;
            $mail->Password = $MAIL_PASSWORD;
            if (!empty($MAIL_ENCRYPTION))
                $mail->SMTPSecure = $MAIL_ENCRYPTION;
            $mail->Port = (int) $MAIL_PORT;

            $mail->setFrom($MAIL_FROM, $MAIL_FROM_NAME ?: 'No-Reply');
            $mail->addAddress($toEmail);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $bodyHtml;
            $mail->AltBody = $bodyText;
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('PHPMailer error: ' . $e->getMessage());
        }
    }

    // Fallback to PHP mail()
    $headers = 'From: ' . ($MAIL_FROM_NAME ? $MAIL_FROM_NAME . ' <' . $MAIL_FROM . '>' : $MAIL_FROM) . "\r\n";
    $headers .= 'Content-Type: text/plain; charset=utf-8';
    return @mail($toEmail, $subject, $bodyText, $headers);
}

// Clear expired session OTP data
if (!empty($_SESSION['staff_fp_reset_expires']) && time() > intval($_SESSION['staff_fp_reset_expires'])) {
    unset($_SESSION['staff_fp_reset_code'], $_SESSION['staff_fp_reset_email'], $_SESSION['staff_fp_reset_expires']);
}

// Handle POST request to send OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $sql = "SELECT ID, Email, StaffName FROM tblstaff WHERE Email = :email LIMIT 1";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            $error = 'Email not found in our records.';
        } else {
            $staff = $stmt->fetch(PDO::FETCH_OBJ);
            $code = random_int(100000, 999999);
            if (send_otp_email($email, $code, $staff->StaffName)) {
                $_SESSION['staff_fp_reset_code'] = (string) $code;
                $_SESSION['staff_fp_reset_email'] = $email;
                $_SESSION['staff_fp_reset_expires'] = time() + 15 * 60; // 15 minutes
                header('Location: verify-otp-process.php');
                exit;
            } else {
                $error = 'Failed to send OTP email. Please try again later.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Student Profiling System</title>
    <link rel="icon" href="../images/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="../images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../images/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../images/android-chrome-192x192.png">
    <link rel="manifest" href="../images/site.webmanifest">
    <link rel="stylesheet" href="css/login-new.css">
</head>

<body>
    <div class="container">
        <div class="welcome-section">
            <div class="welcome-content">
                <h1>RESET PASSWORD</h1>
                <p class="headline">Student Profiling System</p>
                <p>If you've forgotten your password, enter your email address below. We'll send you a secure code to reset it.</p>
            </div>
            <div class="circle-decoration"></div>
        </div>

        <div class="form-section">
            <h2>Forgot Password</h2>
            <p class="subtitle">Enter your staff email to receive a 6-digit reset code.</p>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; padding: .75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem;"><?php echo htmlentities($error); ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="input-group">
                    <div class="input-wrapper">
                        <span class="icon">✉️</span>
                        <input type="email" id="email" name="email" placeholder="Email address" required autofocus>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Send Reset Code</button>
                <a href="login.php" class="btn btn-secondary">Back to Sign In</a>
            </form>
        </div>
    </div>
</body>

</html>