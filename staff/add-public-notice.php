<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsstaffid']==0)) {
  header('location:logout.php');
} else {
  $success_message = $error_message = '';
  if(isset($_POST['submit'])) {
    $nottitle = $_POST['nottitle'];
    $notmsg = $_POST['notmsg'];
    $sql = "insert into tblpublicnotice(NoticeTitle,NoticeMessage) values(:nottitle,:notmsg)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':nottitle', $nottitle, PDO::PARAM_STR);
    $query->bindParam(':notmsg', $notmsg, PDO::PARAM_STR);
    $query->execute();
    $LastInsertId = $dbh->lastInsertId();
    if ($LastInsertId > 0) {
      $success_message = "Notice has been added.";
    } else {
      $error_message = "Something Went Wrong. Please try again";
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Student Profiling System || Add Public Notice</title>
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css" />
  </head>
  <body>
    <div class="container-scroller">
      <?php include_once('includes/header.php');?>
      <div class="container-fluid page-body-wrapper">
        <?php include_once('includes/sidebar.php');?>
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="page-header">
              <h3 class="page-title">Add Public Notice </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page"> Add Public Notice</li>
                </ol>
              </nav>
            </div>
            <div class="row">
              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title" style="text-align: center;">Add Public Notice</h4>
                    <?php if($success_message): ?>
                    <div aria-live="polite" aria-atomic="true" style="position: relative; min-height: 40px;">
                      <div class="toast" id="successToast" style="position: absolute; top: 0; right: 0; min-width: 250px; z-index: 1050;" data-delay="3000" data-autohide="true">
                        <div class="toast-header bg-success text-white">
                          <strong class="mr-auto">Success</strong>
                          <small>Now</small>
                          <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                        <div class="toast-body"><?php echo $success_message; ?></div>
                      </div>
                    </div>
                    <script>
                      window.addEventListener('DOMContentLoaded', function(){
                        var toastEl = document.getElementById('successToast');
                        if(toastEl && window.$) {
                          $(toastEl).toast('show');
                        } else if (toastEl && typeof bootstrap !== "undefined") {
                          var toast = new bootstrap.Toast(toastEl);
                          toast.show();
                        }
                      });
                    </script>
                    <?php elseif($error_message): ?>
                    <div aria-live="polite" aria-atomic="true" style="position: relative; min-height: 40px;">
                      <div class="toast" id="errorToast" style="position: absolute; top: 0; right: 0; min-width: 250px; z-index: 1050;" data-delay="4000" data-autohide="true">
                        <div class="toast-header bg-danger text-white">
                          <strong class="mr-auto">Error</strong>
                          <small>Now</small>
                          <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                        <div class="toast-body"><?php echo $error_message; ?></div>
                      </div>
                    </div>
                    <script>
                      window.addEventListener('DOMContentLoaded', function(){
                        var toastEl = document.getElementById('errorToast');
                        if(toastEl && window.$) {
                          $(toastEl).toast('show');
                        } else if (toastEl && typeof bootstrap !== "undefined") {
                          var toast = new bootstrap.Toast(toastEl);
                          toast.show();
                        }
                      });
                    </script>
                    <?php endif; ?>
                    <form class="forms-sample" method="post" enctype="multipart/form-data">
                      <div class="form-group">
                        <label for="exampleInputName1">Notice Title</label>
                        <input type="text" name="nottitle" value="" class="form-control" required='true'>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputName1">Notice Message</label>
                        <textarea name="notmsg" class="form-control" required='true'></textarea>
                      </div>
                      <button type="submit" class="btn btn-primary mr-2" name="submit">Add</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php include_once('includes/footer.php');?>
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
  </body>
</html>
<?php }  ?>