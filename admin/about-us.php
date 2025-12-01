<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid'] == 0)) {
  header('location:logout.php');
} else {
  $success_message = '';
  if (isset($_POST['submit'])) {
    $pagetitle = $_POST['pagetitle'];
    $pagedes = $_POST['pagedes'];
    $sql = "update tblpage set PageTitle=:pagetitle,PageDescription=:pagedes where  PageType='aboutus'";
    $query = $dbh->prepare($sql);
    $query->bindParam(':pagetitle', $pagetitle, PDO::PARAM_STR);
    $query->bindParam(':pagedes', $pagedes, PDO::PARAM_STR);

    $query->execute();
    $success_message = "About Us page has been updated successfully.";
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>

    <title>Student Profiling System || Update About Us</title>
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
              <h3 class="page-title"> Update About Us </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page"> Update About Us</li>
                </ol>
              </nav>
            </div>
            <div class="row">

              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title" style="text-align: center;">Update About Us</h4>

                    <?php if (!empty($success_message)): ?>
                      <div class="alert alert-success"><?php echo htmlentities($success_message); ?></div>
                    <?php endif; ?>

                    <form class="forms-sample" method="post">
                      <?php

                      $sql = "SELECT * from  tblpage where PageType='aboutus'";
                      $query = $dbh->prepare($sql);
                      $query->execute();
                      $results = $query->fetchAll(PDO::FETCH_OBJ);
                      $cnt = 1;
                      if ($query->rowCount() > 0) {
                        foreach ($results as $row) { ?>
                          <div class="form-group">
                            <label for="exampleInputName1">Page Title:</label>
                            <input type="text" name="pagetitle" value="<?php echo htmlentities($row->PageTitle); ?>" class="form-control"
                              required='true'>
                          </div>
                          <div class="form-group">
                            <label for="exampleInputName1">Page Description:</label>
                            <textarea name="pagedes" class="form-control"
                              required='true'><?php echo htmlentities($row->PageDescription); ?></textarea>
                          </div>
                          <?php $cnt = $cnt + 1;
                        }
                      } ?>
                      <button type="submit" class="btn btn-primary mr-2" name="submit">Update</button>

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
    <script src="js/toast.js"></script>
    <script src="js/misc.js"></script>
    <script src="js/typeahead.js"></script>
    <script src="js/select2.js"></script>
  </body>

  </html><?php } ?>