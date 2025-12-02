<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsstuid'] == 0)) {
  header('location:logout.php');
} else {
  if (isset($_POST['submit'])) {
    $sid = $_SESSION['sturecmsstuid'];
    $currentpassword = $_POST['currentpassword'];
    $newpassword = $_POST['newpassword'];

    $sql = "SELECT Password FROM tblstudent WHERE StuID=:sid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':sid', $sid, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if ($result && (password_verify($currentpassword, $result->Password) || md5($currentpassword) === $result->Password)) {
      $new_hashed_password = password_hash($newpassword, PASSWORD_DEFAULT);
      $con = "update tblstudent set Password=:newpassword where StuID=:sid";
      $chngpwd1 = $dbh->prepare($con);
      $chngpwd1->bindParam(':sid', $sid, PDO::PARAM_STR);
      $chngpwd1->bindParam(':newpassword', $new_hashed_password, PDO::PARAM_STR);
      $chngpwd1->execute();

      $_SESSION['toast_message'] = ['type' => 'success', 'message' => 'Your password was successfully changed.'];
    } else {
      $_SESSION['toast_message'] = ['type' => 'warning', 'message' => 'Your current password is wrong.'];
    }
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Student Management System|| Student Change Password</title>
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/profile.css" />
    <link rel="stylesheet" href="./css/style(v2).css">
    <style>
      .toast-box {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1050;
        width: 300px;
        background: #fff;
        border: 1px solid #ddd;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 4px;
        display: none;
      }

      .toast-box .toast-header {
        padding: 10px 15px;
        border-bottom: 1px solid #eee;
        font-weight: 600;
      }

      .toast-box .toast-body {
        padding: 15px;
      }

      .toast-box.toast-success .toast-header {
        background-color: #d4edda;
        color: #155724;
      }

      .toast-box.toast-warning .toast-header {
        background-color: #fff3cd;
        color: #856404;
      }

      .toast-box.toast-danger .toast-header {
        background-color: #f8d7da;
        color: #721c24;
      }

      .toast-show {
        display: block;
        animation: toast-in 0.3s;
      }

      @keyframes toast-in {
        from {
          transform: translateX(100%);
          opacity: 0;
        }

        to {
          transform: translateX(0);
          opacity: 1;
        }
      }
    </style>
    <script type="text/javascript">
      function showToast(message, type) {
        var toast = document.getElementById('pageToast');
        if (!toast) return;
        toast.className = 'toast-box toast-show toast-' + type;
        toast.querySelector('.toast-body').textContent = message;
        setTimeout(function () { toast.className = toast.className.replace('toast-show', ''); }, 4000);
      }

      function checkpass() {
        if (document.changepassword.newpassword.value != document.changepassword.confirmpassword.value) {
          showToast('New Password and Confirm Password field does not match', 'warning');
          document.changepassword.confirmpassword.focus();
          return false;
        }
        return true;
      }

    </script>
  </head>

  <body>
    <div class="container-scroller">
      <div id="pageToast" class="toast-box">
        <div class="toast-header">Notification</div>
        <div class="toast-body"></div>
      </div>
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
                  <button type="submit" class="btn btn-submit" name="submit">Change Password</button>
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
    <?php
    if (isset($_SESSION['toast_message'])) {
      $toast = $_SESSION['toast_message'];
      echo "<script>document.addEventListener('DOMContentLoaded', function() { showToast('" . addslashes($toast['message']) . "', '" . addslashes($toast['type']) . "'); });</script>";
      unset($_SESSION['toast_message']);
    }
    ?>
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

  </html><?php } ?>