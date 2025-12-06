<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsstaffid']) == 0) {
  header('location:logout.php');
} else {
  $toast_msg = null;
  $toast_type = 'info';

  if (isset($_POST['change_password'])) {
    $staffid = $_SESSION['sturecmsstaffid'];
    $current = isset($_POST['currentpassword']) ? trim($_POST['currentpassword']) : '';
    $new = isset($_POST['newpassword']) ? trim($_POST['newpassword']) : '';
    $confirm = isset($_POST['confirmpassword']) ? trim($_POST['confirmpassword']) : '';

    if ($new === '' || $confirm === '') {
      $toast_msg = 'New password cannot be empty';
      $toast_type = 'warning';
    } elseif ($new !== $confirm) {
      $toast_msg = 'New Password and Confirm Password do not match';
      $toast_type = 'warning';
    } else {
      $sql = "SELECT Password FROM tblstaff WHERE ID=:staffid";
      $query = $dbh->prepare($sql);
      $query->bindParam(':staffid', $staffid, PDO::PARAM_STR);
      $query->execute();
      $result = $query->fetch(PDO::FETCH_OBJ);

      if ($result && password_verify($current, $result->Password)) {
        $new_hashed = password_hash($new, PASSWORD_DEFAULT);
        $con = "UPDATE tblstaff SET Password=:newpassword WHERE ID=:staffid";
        $chngpwd1 = $dbh->prepare($con);
        $chngpwd1->bindParam(':staffid', $staffid, PDO::PARAM_STR);
        $chngpwd1->bindParam(':newpassword', $new_hashed, PDO::PARAM_STR);
        $chngpwd1->execute();
        $toast_msg = 'Your password was successfully changed.';
        $toast_type = 'success';
      } else {
        $toast_msg = 'Your current password is incorrect.';
        $toast_type = 'warning';
      }
    }
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <title>Staff Profiling System || Change Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="../images/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="../images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../images/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../images/android-chrome-192x192.png">
    <link rel="manifest" href="../images/site.webmanifest">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="./css/style(v2).css">
    <link rel="stylesheet" href="./css/responsive.css">
  </head>

  <body>
    <div class="container-scroller">
      <?php include_once('includes/header.php'); ?>
      <div class="container-fluid page-body-wrapper">
        <?php include_once('includes/sidebar.php'); ?>
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="page-header">
              <h3 class="page-title">Change Password</h3>
            </div>
            <div class="password-card">
              <h1 class="card-title">Change Password</h1>

              <form method="post" name="changepassword" onsubmit="return handleSubmit(event);">
                <div class="form-group">
                  <label class="form-label">Current Password</label>
                  <div class="password-input-wrapper">
                    <input type="password" class="form-input" id="currentpassword" name="currentpassword" required>
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('currentpassword', this)">
                      <i class="icon-eye"></i>
                    </button>
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label">New Password</label>
                  <div class="password-input-wrapper">
                    <input type="password" class="form-input" id="newpassword" name="newpassword" oninput="checkPasswordStrength()" onfocus="showRequirements()" required>
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('newpassword', this)">
                      <i class="icon-eye"></i>
                    </button>
                  </div>

                  <div class="password-strength" id="passwordStrength">
                    <div class="strength-bar">
                      <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <div class="strength-text" id="strengthText"></div>
                  </div>

                  <div class="password-requirements" id="requirements">
                    <div class="requirement" id="req-length">
                      <span class="requirement-icon">○</span>
                      <span>At least 8 characters</span>
                    </div>
                    <div class="requirement" id="req-lowercase">
                      <span class="requirement-icon">○</span>
                      <span>One lowercase letter</span>
                    </div>
                    <div class="requirement" id="req-number">
                      <span class="requirement-icon">○</span>
                      <span>One number</span>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label">Confirm Password</label>
                  <div class="password-input-wrapper">
                    <input type="password" class="form-input" id="confirmpassword" name="confirmpassword" oninput="checkPasswordMatch()" required>
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirmpassword', this)">
                      <i class="icon-eye"></i>
                    </button>
                  </div>
                  <div class="error-message" id="matchError">Passwords do not match</div>
                </div>

                <div class="form-actions">
                  <a href="dashboard.php" class="btn btn-back">Back</a>
                  <button type="submit" class="btn btn-submit" name="change_password">Change Password</button>
                </div>
              </form>
            </div>

            <div class="row">
              <div class="col-12 grid-margin stretch-card">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="vendors/select2/select2.min.js"></script>
    <script src="vendors/typeahead.js/typeahead.bundle.min.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script src="js/typeahead.js"></script>
    <script src="js/select2.js"></script>
    <script src="js/toast.js"></script>
    <?php if (isset($toast_msg) && $toast_msg): ?>
      <script>
        document.addEventListener('DOMContentLoaded', function () { if (window.showToast) showToast(<?php echo json_encode($toast_msg); ?>, <?php echo json_encode($toast_type); ?>); });
      </script>
    <?php endif; ?>

    <script>
      function togglePasswordVisibility(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector('i');

        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.remove('icon-eye');
          icon.classList.add('icon-eye-slash'); // Assuming you have a slash icon
        } else {
          input.type = 'password';
          icon.classList.remove('icon-eye-slash');
          icon.classList.add('icon-eye');
        }
      }

      function showRequirements() {
        document.getElementById('requirements').classList.add('show');
      }

      function checkPasswordStrength() {
        const password = document.getElementById('newpassword').value;
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        const strengthDiv = document.getElementById('passwordStrength');

        if (password.length === 0) {
          strengthDiv.classList.remove('show');
          return;
        }

        strengthDiv.classList.add('show');

        const hasLength = password.length >= 8;
        const hasLowercase = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);

        updateRequirement('req-length', hasLength);
        updateRequirement('req-lowercase', hasLowercase);
        updateRequirement('req-number', hasNumber);

        const metRequirements = [hasLength, hasLowercase, hasNumber].filter(Boolean).length;

        strengthFill.className = 'strength-fill';
        strengthText.className = 'strength-text';

        if (metRequirements <= 1) {
          strengthFill.classList.add('weak');
          strengthText.classList.add('weak');
          strengthText.textContent = 'Weak password';
        } else if (metRequirements === 2) {
          strengthFill.classList.add('medium');
          strengthText.classList.add('medium');
          strengthText.textContent = 'Medium password';
        } else {
          strengthFill.classList.add('strong');
          strengthText.classList.add('strong');
          strengthText.textContent = 'Strong password';
        }
      }

      function updateRequirement(id, met) {
        const element = document.getElementById(id);
        const icon = element.querySelector('.requirement-icon');

        if (met) {
          element.classList.add('met');
          icon.textContent = '✓';
        } else {
          element.classList.remove('met');
          icon.textContent = '○';
        }
      }

      function checkPasswordMatch() {
        const newPassword = document.getElementById('newpassword').value;
        const confirmPassword = document.getElementById('confirmpassword').value;
        const errorDiv = document.getElementById('matchError');

        if (confirmPassword.length > 0 && newPassword !== confirmPassword) {
          errorDiv.classList.add('show');
          return false;
        } else {
          errorDiv.classList.remove('show');
          return true;
        }
      }

      function handleSubmit(event) {
        if (!checkPasswordMatch()) {
          event.preventDefault();
          if (window.showToast) showToast('Passwords do not match!', 'warning');
          return false;
        }
        return true;
      }
    </script>
  </body>

  </html>
<?php } ?>