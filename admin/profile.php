<?php
session_start();
error_reporting(0);

$toast_msg = $_SESSION['profile_update_msg'] ?? null;
unset($_SESSION['profile_update_msg']);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid'] == 0)) {
  header('location:logout.php');
} else {
  if (isset($_POST['submit'])) {
    $adminid = $_SESSION['sturecmsaid'];
    $AName = $_POST['adminname'];
    $email = $_POST['email'];

    // Handle image upload
    $image = $_FILES["profilepic"]["name"];
    if ($image != '') {
      $extension = substr($image, strlen($image) - 4, strlen($image));
      $allowed_extensions = array(".jpg", "jpeg", ".png", ".gif");
      if (!in_array($extension, $allowed_extensions)) {
        echo "<script>alert('Invalid format. Only jpg / jpeg/ png /gif format allowed');</script>";
      } else {
        $image = md5($image) . time() . $extension;
        move_uploaded_file($_FILES["profilepic"]["tmp_name"], "images/" . $image);
        $sql = "update tbladmin set AdminName=:adminname, Email=:email, Image=:image where ID=:aid";
      }
    } else {
      $sql = "update tbladmin set AdminName=:adminname, Email=:email where ID=:aid";
    }

    $query = $dbh->prepare($sql);
    $query->bindParam(':adminname', $AName, PDO::PARAM_STR);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    if ($image != '') {
      $query->bindParam(':image', $image, PDO::PARAM_STR);
    }
    $query->bindParam(':aid', $adminid, PDO::PARAM_STR);
    $query->execute();

    $_SESSION['profile_update_msg'] = 'Your profile has been updated successfully!';
    echo "<script>window.location.href ='profile.php'</script>";

  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>

    <title>Student Profiling System || Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="../images/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="../images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../images/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../images/android-chrome-192x192.png">
    <link rel="manifest" href="../images/site.webmanifest">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="./css/profile.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style(v2).css">
    <link rel="stylesheet" href="css/responsive.css">
  </head>

  <body>
    <div class="container-scroller">
      <?php include_once('includes/header.php'); ?>
      <div class="container-fluid page-body-wrapper">
        <?php include_once('includes/sidebar.php'); ?>
        <div class="main-panel">
          <div class="content-wrapper" style="background: #ecf0f4; padding: 40px;">
            <div class="container">
              <div class="profile-card">
                <h1 class="profile-title">Admin Profile</h1>

                <form method="post" enctype="multipart/form-data">
                  <?php
                  $adminid = $_SESSION['sturecmsaid'];
                  $sql = "SELECT * from tbladmin where ID=:aid";
                  $query = $dbh->prepare($sql);
                  $query->bindParam(':aid', $adminid, PDO::PARAM_STR);
                  $query->execute();
                  $results = $query->fetchAll(PDO::FETCH_OBJ);
                  if ($query->rowCount() > 0) {
                    foreach ($results as $row) {
                      $profileImage = !empty($row->Image) ? 'images/' . htmlentities($row->Image) : 'images/faces/face8.jpg';
                      ?>
                      <div class="form-group">
                        <label class="form-label">Admin Name</label>
                        <input type="text" name="adminname" class="form-input" value="<?php echo htmlentities($row->AdminName); ?>" required>
                      </div>

                      <div class="form-group">
                        <label class="form-label">User Name</label>
                        <input type="text" class="form-input" value="<?php echo htmlentities($row->UserName); ?>" readonly>
                      </div>

                      <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" value="<?php echo htmlentities($row->Email); ?>" required>
                      </div>

                      <div class="form-group">
                        <label class="form-label">Admin Registration Date</label>
                        <input type="text" class="form-input" value="<?php echo htmlentities($row->AdminRegdate); ?>" readonly>
                      </div>

                      <div class="profile-image-section">
                        <label class="form-label">Current Profile Image</label>
                        <div class="current-image-wrapper">
                          <img src="<?php echo $profileImage; ?>" alt="Profile" class="profile-image" id="currentImage">
                          <div class="image-info">
                            <div class="image-label">
                              <?php echo htmlentities($row->AdminName); ?>
                            </div>
                            <div class="image-description">Current profile picture</div>
                          </div>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="form-label">Update Profile Image</label>
                        <div class="file-upload-wrapper">
                          <div class="file-upload-area" onclick="document.getElementById('fileInput').click()">
                            <div class="upload-icon">📷</div>
                            <div class="upload-text">Click to choose a new profile image</div>
                          </div>
                          <input type="file" name="profilepic" id="fileInput" class="file-input" accept="image/jpeg,image/png,image/jpg" onchange="handleFileSelect(event)">

                          <div class="file-preview" id="filePreview">
                            <div class="file-preview-icon">📁</div>
                            <div class="file-preview-details">
                              <div class="file-preview-name" id="fileName">image.jpg</div>
                              <div class="file-preview-size" id="fileSize">245 KB</div>
                            </div>
                            <button type="button" class="remove-file-btn" onclick="removeFile()">×</button>
                          </div>
                        </div>
                      </div>

                      <div class="form-actions">
                        <a href="dashboard.php" class="btn btn-back">Back</a>
                        <button type="submit" name="submit" class="btn btn-update">Update</button>
                      </div>
                    <?php }
                  } ?>
                </form>
              </div>
            </div>
          </div>
          <?php include_once('includes/footer.php'); ?>
        </div>
      </div>
    </div>
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script src="js/toast.js"></script>
    <script>
      const defaultImage = "<?php echo $profileImage; ?>";

      function handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
          const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
          if (!validTypes.includes(file.type)) {
            alert('Please select a valid image file (JPG, PNG, or GIF)');
            event.target.value = '';
            return;
          }

          document.getElementById('fileName').textContent = file.name;
          document.getElementById('fileSize').textContent = formatFileSize(file.size);
          document.getElementById('filePreview').classList.add('show');

          const reader = new FileReader();
          reader.onload = function (e) {
            document.getElementById('currentImage').src = e.target.result;
          };
          reader.readAsDataURL(file);
        }
      }

      function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
      }

      function removeFile() {
        document.getElementById('fileInput').value = '';
        document.getElementById('filePreview').classList.remove('show');
        document.getElementById('currentImage').src = defaultImage;
      }
    </script>
    <?php if (isset($toast_msg) && $toast_msg): ?>
      <script>document.addEventListener('DOMContentLoaded', function () { showToast(<?php echo json_encode($toast_msg); ?>, 'success'); });</script>
    <?php endif; ?>
  </body>

  </html><?php } ?>