<?php
session_start();
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

function send_otp_email($toEmail, $code)
{
  global $hasPHPMailer, $MAIL_HOST, $MAIL_USERNAME, $MAIL_PASSWORD, $MAIL_PORT, $MAIL_ENCRYPTION, $MAIL_FROM, $MAIL_FROM_NAME;

  $subject = 'Password Reset Code';
  $bodyText = "Your password reset code is: $code\nThis code expires in 15 minutes.";
  $bodyHtml = "<p>Your password reset code is: <strong>$code</strong></p><p>This code expires in 15 minutes.</p>";

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
if (!empty($_SESSION['admin_fp_reset_expires']) && time() > intval($_SESSION['admin_fp_reset_expires'])) {
  unset($_SESSION['admin_fp_reset_code'], $_SESSION['admin_fp_reset_email'], $_SESSION['admin_fp_reset_expires']);
}

// Handle POST request to send OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
  $email = trim($_POST['email']);

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please enter a valid email address.';
  } else {
    $sql = "SELECT ID, Email FROM tbladmin WHERE Email = :email LIMIT 1";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
      $error = 'Email not found in our records.';
    } else {
      $code = random_int(100000, 999999);
      if (send_otp_email($email, $code)) {
        $_SESSION['admin_fp_reset_code'] = (string) $code;
        $_SESSION['admin_fp_reset_email'] = $email;
        $_SESSION['admin_fp_reset_expires'] = time() + 15 * 60; // 15 minutes
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
  <meta charset="utf-8">
  <title>Student Profiling System || Forgot Password</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
  <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="./css/style(v2).css">
</head>

<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-center auth">
        <div class="row flex-grow">
          <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left p-5">
              <div class="brand-logo" align="center" style="font-weight:bold">Admin Password Reset</div>
              <h6 class="font-weight-light">Enter your admin email to receive a 6-digit reset code.</h6>
              <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlentities($error); ?></div><?php endif; ?>
              <form class="pt-3" method="post">
                <div class="form-group"><input type="email" class="form-control form-control-lg" name="email"
                    placeholder="Email address" required autofocus></div>
                <div class="mt-3"><button class="btn btn-success btn-block loginbtn" type="submit">Send OTP</button>
                </div>
                <div class="my-2 d-flex justify-content-between align-items-center">
                  <a href="login.php" class="auth-link text-black">Sign in</a>
                  <a href="../index.php" class="auth-link text-black">Back Home</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="vendors/js/vendor.bundle.base.js"></script>
</body>

</html>