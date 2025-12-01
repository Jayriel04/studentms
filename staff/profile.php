<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsstaffid'] == 0)) {
  header('location:logout.php');
} else {
  if (isset($_POST['submit'])) {
    $staffid = $_SESSION['sturecmsstaffid'];
    $SName = $_POST['staffname'];
    $email = $_POST['email'];

    // Handle image upload
    $image = $_FILES["profilepic"]["name"];
    $image_updated = false;
    if ($image != '') {
      $extension = substr($image, strlen($image) - 4, strlen($image));
      $allowed_extensions = array(".jpg", ".jpeg", ".png", ".gif");
      if (!in_array($extension, $allowed_extensions)) {
        echo "<script>if(window.showToast) showToast('Invalid format. Only jpg / jpeg/ png /gif format allowed', 'warning'); else alert('Invalid format. Only jpg / jpeg/ png /gif format allowed');</script>";
      } else {
        // Use a consistent image directory
        $image = md5($image) . time() . $extension;
        move_uploaded_file($_FILES["profilepic"]["tmp_name"], "../admin/images/" . $image);
        $sql = "UPDATE tblstaff SET StaffName=:staffname, Email=:email, Image=:image WHERE ID=:staffid";
        $image_updated = true;
      }
    } else {
      $sql = "UPDATE tblstaff SET StaffName=:staffname, Email=:email WHERE ID=:staffid";
    }

    $query = $dbh->prepare($sql);
    $query->bindParam(':staffname', $SName, PDO::PARAM_STR);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    if ($image_updated) {
      $query->bindParam(':image', $image, PDO::PARAM_STR);
    }
    $query->bindParam(':staffid', $staffid, PDO::PARAM_STR);
    $query->execute();

    echo "<script>if(window.showToast) { showToast('Your profile has been updated','success'); setTimeout(function(){ window.location.href ='profile.php'; }, 2000); } else { alert('Your profile has been updated'); window.location.href ='profile.php'; }</script>";
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>

    <title>Staff Profiling System || Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="./css/style(v2).css">

  </head>

  <body>
    <div class="container-scroller">
      <?php include_once('includes/header.php'); ?>
      <div class="container-fluid page-body-wrapper">
        <?php include_once('includes/sidebar.php'); ?>
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="page-header">
              <h3 class="page-title"> Staff Profile </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Staff Profile</li>
                </ol>
              </nav>
            </div>
            <div class="row">

              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title" style="text-align: center;">Staff Profile</h4>

                    <form class="forms-sample" method="post" enctype="multipart/form-data">
                      <?php
                      $staffid = $_SESSION['sturecmsstaffid'];
                      $sql = "SELECT * FROM tblstaff WHERE ID=:staffid";
                      $query = $dbh->prepare($sql);
                      $query->bindParam(':staffid', $staffid, PDO::PARAM_STR);
                      $query->execute();
                      $results = $query->fetchAll(PDO::FETCH_OBJ);
                      if ($query->rowCount() > 0) {
                        foreach ($results as $row) { ?>
                          <div class="form-group">
                            <label for="exampleInputName1">Staff Name</label>
                            <input type="text" name="staffname" value="<?php echo htmlentities($row->StaffName); ?>"
                              class="form-control" required='true'>
                          </div>
                          <div class="form-group">
                            <label for="exampleInputEmail3">User Name</label>
                            <input type="text" name="username" value="<?php echo htmlentities($row->UserName); ?>"
                              class="form-control" readonly>
                          </div>
                          <div class="form-group">
                            <label for="exampleInputCity1">Email</label>
                            <input type="email" name="email" value="<?php echo htmlentities($row->Email); ?>"
                              class="form-control" required='true'>
                          </div>
                          <div class="form-group">
                            <label for="exampleInputCity1">Staff Registration Date</label>
                            <input type="text" name="" value="<?php echo htmlentities($row->StaffRegdate); ?>" readonly=""
                              class="form-control">
                          </div>
                          <div class="form-group">
                            <label>Current Profile Image</label>
                            <br>
                            <?php if (!empty($row->Image)): ?>
                              <img src="../admin/images/<?php echo $row->Image; ?>" width="100" height="100">
                            <?php else: ?>
                              <p>No image available</p>
                            <?php endif; ?>
                          </div>
                          <div class="form-group">
                            <label>Update Profile Image</label>
                            <input type="file" name="profilepic" class="form-control">
                          </div>
                        <?php }
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
  </body>

  </html>
<?php } ?>