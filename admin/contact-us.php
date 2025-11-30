<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid'] == 0)) {
  header('location:logout.php');
} else {
  $success_message = '';
  $error_message = '';
  if (isset($_POST['submit'])) {
    $pagetitle = $_POST['pagetitle'];
    $pagedes = $_POST['pagedes'];
    $mobnum = $_POST['mobnum'];
    $email = $_POST['email'];
    // Only update the first found contact us page (by id)
    $sql = "SELECT id FROM tblpage WHERE PageType='contactus' ORDER BY id ASC LIMIT 1";
    $query = $dbh->prepare($sql);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);
    if ($result) {
      $contactus_id = $result->id;
      $update_sql = "UPDATE tblpage SET PageTitle=:pagetitle, PageDescription=:pagedes, Email=:email, MobileNumber=:mobnum WHERE id=:id";
      $update_query = $dbh->prepare($update_sql);
      $update_query->bindParam(':pagetitle', $pagetitle, PDO::PARAM_STR);
      $update_query->bindParam(':pagedes', $pagedes, PDO::PARAM_STR);
      $update_query->bindParam(':email', $email, PDO::PARAM_STR);
      $update_query->bindParam(':mobnum', $mobnum, PDO::PARAM_STR);
      $update_query->bindParam(':id', $contactus_id, PDO::PARAM_INT);
      $update_query->execute();
      $success_message = "Contact us page has been updated successfully.";
    } else {
      $error_message = "No 'Contact Us' page found to update!";
    }
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <title>Student Profiling System || Update Contact Us</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="./css/style(v2).css">
    <script src="http://js.nicedit.com/nicEdit-latest.js" type="text/javascript"></script>
    <script type="text/javascript">bkLib.onDomLoaded(nicEditors.allTextAreas);</script>
  </head>

  <body>
    <div class="container-scroller">
      <?php include_once('includes/header.php'); ?>
      <div class="container-fluid page-body-wrapper">
        <?php include_once('includes/sidebar.php'); ?>
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="page-header">
              <h3 class="page-title"> Update Contact Us </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page"> Update Contact Us</li>
                </ol>
              </nav>
            </div>
            <div class="row">
              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <?php if (!empty($success_message)): ?>
                      <div class="alert alert-success">
                        <?php echo htmlentities($success_message); ?>
                      </div>
                    <?php endif; ?>
                    <?php if (!empty($error_message)): ?>
                      <div class="alert alert-danger"><?php echo htmlentities($error_message); ?></div>
                    <?php endif; ?>
                    <h4 class="card-title" style="text-align: center;">Update Contact Us</h4>
                    <form class="forms-sample" method="post">
                      <?php
                      // Only fetch the first found contact us page (by id)
                      $sql = "SELECT * FROM tblpage WHERE PageType='contactus' ORDER BY id ASC LIMIT 1";
                      $query = $dbh->prepare($sql);
                      $query->execute();
                      $row = $query->fetch(PDO::FETCH_OBJ);
                      if ($row) { ?>
                        <div class="form-group">
                          <label for="exampleInputName1">Page Title:</label>
                          <input type="text" name="pagetitle" value="<?php echo htmlspecialchars($row->PageTitle); ?>"
                            class="form-control" required>
                        </div>
                        <div class="form-group">
                          <label for="exampleInputName1">Page Description:</label>
                          <textarea name="pagedes" class="form-control"
                            required><?php echo htmlspecialchars($row->PageDescription); ?></textarea>
                        </div>
                        <div class="form-group">
                          <label for="exampleInputName1">Email:</label>
                          <input type="text" name="email" id="email" required
                            value="<?php echo htmlspecialchars($row->Email); ?>" class="form-control">
                        </div>
                        <div class="form-group">
                          <label for="exampleInputName1">Mobile Number:</label>
                          <input type="text" name="mobnum" id="mobnum" required
                            value="<?php echo htmlspecialchars($row->MobileNumber); ?>" class="form-control" maxlength="10"
                            pattern="[0-9]+">
                        </div>
                      <?php } else { ?>
                        <div class="alert alert-warning">No Contact Us page found!</div>
                      <?php } ?>
                      <button type="submit" class="btn btn-primary mr-2" name="submit">Update</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php include_once('includes/footer.php'); ?>
        </div>
      </div>
    </div>
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="vendors/select2/select2.min.js"></script>
    <script src="vendors/typeahead.js/typeahead.bundle.min.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script src="js/toast.js"></script>
    <script src="js/typeahead.js"></script>
    <script src="js/select2.js"></script>
  </body>

  </html><?php } ?>