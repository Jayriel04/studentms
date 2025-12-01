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
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="./css/style(v2).css">
    <style>
      #appToast {
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 2000;
      }
    </style>

  </head>

  <body>
    <div id="appToast"></div>
    <div class="container-scroller">
      <?php include_once('includes/header.php'); ?>
      <div class="container-fluid page-body-wrapper">
        <?php include_once('includes/sidebar.php'); ?>
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="page-header">
              <h3 class="page-title"> Admin Profile </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Admin Profile</li>
                </ol>
              </nav>
            </div>
            <div class="row">

              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title" style="text-align: center;">Admin Profile</h4>

                    <form class="forms-sample" method="post" enctype="multipart/form-data">
                      <?php
                      $adminid = $_SESSION['sturecmsaid'];
                      $sql = "SELECT * from tbladmin where ID=:aid";
                      $query = $dbh->prepare($sql);
                      $query->bindParam(':aid', $adminid, PDO::PARAM_STR);
                      $query->execute();
                      $results = $query->fetchAll(PDO::FETCH_OBJ);
                      $cnt = 1;
                      if ($query->rowCount() > 0) {
                        foreach ($results as $row) { ?>
                          <div class="form-group">
                            <label for="exampleInputName1">Admin Name</label>
                            <input type="text" name="adminname" value="<?php echo $row->AdminName; ?>" class="form-control"
                              required='true'>
                          </div>
                          <div class="form-group">
                            <label for="exampleInputEmail3">User Name</label>
                            <input type="text" name="username" value="<?php echo $row->UserName; ?>" class="form-control"
                              readonly="">
                          </div>
                          <div class="form-group">
                            <label for="exampleInputCity1">Email</label>
                            <input type="email" name="email" value="<?php echo $row->Email; ?>" class="form-control"
                              required='true'>
                          </div>
                          <div class="form-group">
                            <label for="exampleInputCity1">Admin Registration Date</label>
                            <input type="text" name="" value="<?php echo $row->AdminRegdate; ?>" readonly=""
                              class="form-control">
                          </div>
                          <div class="form-group">
                            <label>Current Profile Image</label>
                            <br>
                            <img src="images/<?php echo !empty($row->Image) ? $row->Image : 'faces/face8.jpg'; ?>" width="100"
                              height="100">
                          </div>
                          <div class="form-group">
                            <label>Update Profile Image</label>
                            <input type="file" name="profilepic" class="form-control">
                          </div>
                          <?php $cnt = $cnt + 1;
                        }
                      } ?>
                      <button type="submit" class="btn btn-primary mr-2" name="submit">Update</button>
                      <a href="dashboard.php" class="btn btn-light">Back</a>

                    </form>
                  </div>
                </div>
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
    <script>
      function showToast(message, type) {
        type = type || 'info';
        var toast = document.createElement('div');
        toast.className = 'toast show bg-' + (type === 'success' ? 'success' : 'danger') + ' text-white';
        toast.setAttribute('role', 'alert');
        toast.innerHTML = '<div class="toast-header bg-success text-white"><strong class="mr-auto">' + (type === 'success' ? 'Success' : 'Notice') + '</strong><button type="button" class="ml-2 mb-1 close" data-dismiss="toast" style="color: #fff;">&times;</button></div><div class="toast-body">' + message + '</div>';
        var container = document.getElementById('appToast');
        container.appendChild(toast);
        // Auto remove after 3s
        setTimeout(function () { try { $(toast).toast('hide'); } catch (e) { toast.remove(); } }, 3000);
      }
      // Delegate close buttons
      document.addEventListener('click', function (e) { if (e.target && e.target.getAttribute && e.target.getAttribute('data-dismiss') === 'toast') { var t = e.target.closest('.toast'); if (t) t.remove(); } });
    </script>
    <?php if (isset($toast_msg) && $toast_msg): ?>
      <script>document.addEventListener('DOMContentLoaded', function () { showToast(<?php echo json_encode($toast_msg); ?>, 'success'); });</script>
    <?php endif; ?>
  </body>

  </html><?php } ?>