<?php
session_start();
error_reporting(0);
include __DIR__ . '/../includes/dbconnection.php';
include __DIR__ . '/../includes/mail_config.php';

// PHPMailer will be used for reliable SMTP email sending.
// Make sure to install it via Composer: `composer require phpmailer/phpmailer`
// Try to load Composer autoload (PHPMailer). If unavailable, we'll fall back to PHP mail().
$hasPHPMailer = false;
// Avoid loading composer autoload (vendor/autoload.php) because it runs
// platform checks that can fatal when system PHP doesn't match composer.lock.
// Instead, load PHPMailer directly from its package files if they exist.
$phpmailerSrc = __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
$phpmailerException = __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
$phpmailerSMTP = __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
if (file_exists($phpmailerSrc)) {
  // Load PHPMailer core plus required support classes
  if (file_exists($phpmailerException)) {
    require_once $phpmailerException;
  }
  if (file_exists($phpmailerSMTP)) {
    require_once $phpmailerSMTP;
  }
  require_once $phpmailerSrc;
  $hasPHPMailer = class_exists('PHPMailer\\PHPMailer\\PHPMailer');
}

function sendResetCodeSMTP($email, $code, $dbh)
{
  global $hasPHPMailer, $MAIL_HOST, $MAIL_USERNAME, $MAIL_PASSWORD, $MAIL_PORT, $MAIL_ENCRYPTION, $MAIL_FROM, $MAIL_FROM_NAME;

  $subject = 'Password Reset Code';
  $body = "Your password reset code is: $code. This code will expire in 15 minutes.";

  if ($hasPHPMailer) {
    try {
      // Ensure MAIL_FROM is populated; fallback to MAIL_USERNAME if needed
      if (empty($MAIL_FROM) && !empty($MAIL_USERNAME)) {
        $MAIL_FROM = $MAIL_USERNAME;
      }
      if (empty($MAIL_FROM_NAME)) {
        $MAIL_FROM_NAME = 'StudentMS';
      }
      $mail = new PHPMailer\PHPMailer\PHPMailer(true);
      // Server settings - use configured values
      $mail->isSMTP();
      // Optional debug mode (set MAIL_DEBUG=1 in environment to enable)
      $mail_debug = getenv('MAIL_DEBUG');
      if ($mail_debug && in_array(strtolower($mail_debug), ['1', 'true', 'yes'], true)) {
        $mail->SMTPDebug = 2; // show client/server messages
        $mail->Debugoutput = function ($str, $level) {
          error_log("PHPMailer: [$level] $str");
        };
      }
      $mail->Host = $MAIL_HOST;
      $mail->SMTPAuth = true;
      $mail->Username = $MAIL_USERNAME;
      $mail->Password = $MAIL_PASSWORD;
      if (!empty($MAIL_ENCRYPTION)) {
        $mail->SMTPSecure = $MAIL_ENCRYPTION;
      }
      $mail->Port = (int) $MAIL_PORT;

      // Validate From address
      if (empty($MAIL_FROM) || !filter_var($MAIL_FROM, FILTER_VALIDATE_EMAIL)) {
        error_log('Mail error: invalid MAIL_FROM configured: ' . var_export($MAIL_FROM, true));
        throw new \Exception('Invalid mail from address');
      }
      // Recipients
      $mail->setFrom($MAIL_FROM, $MAIL_FROM_NAME ?: 'No-Reply');
      $mail->addAddress($email);

      // Content
      $mail->isHTML(true);
      $mail->Subject = $subject;
      $mail->Body = "<p>Your password reset code is: <strong>$code</strong></p><p>This code will expire in 15 minutes.</p>";

      $mail->send();
      return true;
    } catch (\PHPMailer\PHPMailer\Exception $e) {
      error_log('Mail error: ' . $e->getMessage());
      // Fall through to fallback mail()
    } catch (Exception $e) {
      error_log('Mail error: ' . $e->getMessage());
    }
  }

  // Fallback: use PHP mail(). Use configured From header.
  $headers = 'From: ' . ($MAIL_FROM_NAME ? $MAIL_FROM_NAME . ' <' . $MAIL_FROM . '>' : $MAIL_FROM) . "\r\n" . 'Content-Type: text/plain; charset=utf-8';
  return @mail($email, $subject, $body, $headers);
}

// Request reset code
if (isset($_POST['request_code'])) {
  $email = trim($_POST['email']);

  // Verify user exists with given email (column `EmailAddress` in this schema)
  $sql = "SELECT EmailAddress FROM tblstudent WHERE EmailAddress=:email";
  $query = $dbh->prepare($sql);
  $query->bindParam(':email', $email, PDO::PARAM_STR);
  $query->execute();

  if ($query->rowCount() > 0) {
    $code = random_int(100000, 999999);
    $sent = sendResetCodeSMTP($email, $code, $dbh);
    if ($sent) {
      $_SESSION['reset_code'] = $code;
      $_SESSION['reset_email'] = $email;
      $_SESSION['reset_expires'] = time() + 15 * 60; // 15 minutes
      // Redirect to OTP verification page
      header('Location: verify-otp.php');
      exit;
    } else {
      // Email failed to send — do NOT store or expose the OTP in session.
      // Surface a simple client alert and log the failure for debugging.
      echo "<script>alert('Failed to send email. Please contact support.');</script>";
      error_log('Failed to send reset email to ' . $email);
    }
  } else {
    echo "<script>alert('Email id is invalid');</script>";
  }
}

// (Password reset handled on separate page `reset-password.php`)

?>
<?php
// If a previous reset code exists but expired, clear it so the form is shown again
if (!empty($_SESSION['reset_expires']) && time() > intval($_SESSION['reset_expires'])) {
  unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_expires']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

  <title>Student Management System || Student Forgot Password</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <!-- endinject -->
  <!-- Plugin css for this page -->
  <!-- End plugin css for this page -->
  <!-- inject:css -->
  <!-- endinject -->
  <!-- Layout styles -->
  <link rel="stylesheet" href="css/style.css">
  <script type="text/javascript">
    function valid() {
      if (document.chngpwd.newpassword.value != document.chngpwd.confirmpassword.value) {
        alert("New Password and Confirm Password Field do not match  !!");
        document.chngpwd.confirmpassword.focus();
        return false;
      }
      return true;
    }
  </script>

</head>

<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper">
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-12 grid-margin stretch-card d-flex justify-content-center">
              <div class="card mx-auto" style="max-width:420px;">
                <div class="card-body">

                  <?php if (!isset($_SESSION['reset_code'])): ?>
                    <h4 class="card-title text-center mb-2">Forgot Password</h4>
                    <p class="text-muted text-center">Enter your email and we'll send a 6-digit reset code.</p>
                    <form class="pt-3" method="post">
                      <div class="form-group">
                        <div class="input-group">
                          <div class="input-group-prepend">
                            <span class="input-group-text"><i class="icon-envelope"></i></span>
                          </div>
                          <input type="email" class="form-control form-control-lg" placeholder="Email Address"
                            required="true" name="email" value="<?php echo htmlentities($_SESSION['reset_email']); ?>" autofocus>
                        </div>
                        <small class="form-text text-muted">We will send a 6-digit code to this address.</small>
                      </div>
                      <div class="mt-3">
                        <button class="btn btn-success btn-block loginbtn" name="request_code" type="submit">Send
                          Code</button>
                      </div>
                    </form>
                  <?php endif; ?>
                  <div class="my-2 d-flex justify-content-between align-items-center">
                    <a href="login.php" class="auth-link text-black">signin</a>
                  </div>
                  <div class="mb-2">
                    <a href="../index.php" class="btn btn-block btn-facebook auth-form-btn">
                      <i class="icon-social-home mr-2"></i>Back Home </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- content-wrapper ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script>
      if (typeof jQuery === 'undefined') {
        var jq = document.createElement('script');
        jq.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
        jq.crossOrigin = 'anonymous';
        document.head.appendChild(jq);
      }
    </script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <!-- endinject -->
</body>

</html>