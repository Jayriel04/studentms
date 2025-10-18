<?php
session_start();
error_reporting(0);
include __DIR__ . '/../includes/dbconnection.php';
include __DIR__ . '/../includes/mail_config.php';

// Try to load PHPMailer from vendor if present
$hasPHPMailer = false;
$phpmailerSrc = __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
$phpmailerException = __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
$phpmailerSMTP = __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
if (file_exists($phpmailerSrc)) {
  if (file_exists($phpmailerException)) require_once $phpmailerException;
  if (file_exists($phpmailerSMTP)) require_once $phpmailerSMTP;
  require_once $phpmailerSrc;
  $hasPHPMailer = class_exists('PHPMailer\\PHPMailer\\PHPMailer');
}

function send_otp_email($toEmail, $code)
{
  global $hasPHPMailer, $MAIL_HOST, $MAIL_USERNAME, $MAIL_PASSWORD, $MAIL_PORT, $MAIL_ENCRYPTION, $MAIL_FROM, $MAIL_FROM_NAME;

  $subject = 'Password Reset Code';
  $bodyText = "Your password reset code is: $code\nThis code expires in 15 minutes.";
  $bodyHtml = "<p>Your password reset code is: <strong>$code</strong></p><p>This code expires in 15 minutes.</p>";

  if ($hasPHPMailer) {
    try {
      if (empty($MAIL_FROM) && !empty($MAIL_USERNAME)) $MAIL_FROM = $MAIL_USERNAME;
      if (empty($MAIL_FROM_NAME)) $MAIL_FROM_NAME = 'StudentMS';

  $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
      $mail->isSMTP();
      $mail->Host = $MAIL_HOST;
      $mail->SMTPAuth = true;
      $mail->Username = $MAIL_USERNAME;
      $mail->Password = $MAIL_PASSWORD;
      if (!empty($MAIL_ENCRYPTION)) $mail->SMTPSecure = $MAIL_ENCRYPTION;
      $mail->Port = (int)$MAIL_PORT;

      if (empty($MAIL_FROM) || !filter_var($MAIL_FROM, FILTER_VALIDATE_EMAIL)) {
        error_log('Invalid MAIL_FROM configured: '.var_export($MAIL_FROM, true));
        return false;
      }

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
      // fall through to mail()
    }
  }

  // Fallback to PHP mail()
  $headers = 'From: ' . ($MAIL_FROM_NAME ? $MAIL_FROM_NAME . ' <' . $MAIL_FROM . '>' : $MAIL_FROM) . "\r\n";
  $headers .= 'Content-Type: text/plain; charset=utf-8';
  return @mail($toEmail, $subject, $bodyText, $headers);
}

// Clear expired session OTP data
if (!empty($_SESSION['fp_reset_expires']) && time() > intval($_SESSION['fp_reset_expires'])) {
  unset($_SESSION['fp_reset_code'], $_SESSION['fp_reset_email'], $_SESSION['fp_reset_expires']);
}

// Handle POST request to send OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
  $email = trim($_POST['email']);

  // Basic validation
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please enter a valid email address.';
  } else {
    // Check student existence by EmailAddress
    $sql = "SELECT ID, StuID, EmailAddress FROM tblstudent WHERE EmailAddress = :email LIMIT 1";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
      $error = 'Email not found in our records.';
    } else {
      // Generate 6-digit OTP
      $code = random_int(100000, 999999);
      $sent = send_otp_email($email, $code);
      if ($sent) {
        $_SESSION['fp_reset_code'] = (string)$code;
        $_SESSION['fp_reset_email'] = $email;
        $_SESSION['fp_reset_expires'] = time() + 15 * 60; // 15 minutes
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
  <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
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
            <p class="subtitle">Enter your student email to receive a 6-digit reset code.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; padding: .75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem;"><?php echo htmlentities($error); ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="input-group">
                    <div class="input-wrapper">
                        <span class="icon">✉️</span>
                        <input type="email" id="email" name="email" placeholder="Email address" required value="<?php echo isset($_POST['email']) ? htmlentities($_POST['email']) : ''; ?>" autofocus>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Send Reset Code</button>
                <a href="login.php" class="btn btn-secondary">Back to Sign In</a>
            </form>
        </div>
    </div>
</body>

</html>
