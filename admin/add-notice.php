<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid'] == 0)) {
  header('location:logout.php');
} else {
  $success_message = $error_message = '';
  if (isset($_POST['submit'])) {
    $nottitle = $_POST['nottitle'];
    $notmsg = $_POST['notmsg']; // The notice message with potential mentions
    $sql = "insert into tblnotice(NoticeTitle,NoticeMsg)values(:nottitle,:notmsg)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':nottitle', $nottitle, PDO::PARAM_STR);
    $query->bindParam(':notmsg', $notmsg, PDO::PARAM_STR);
    $query->execute();
    $LastInsertId = $dbh->lastInsertId();

    if ($LastInsertId > 0) {
      $success_message = "Notice has been added successfully.";

      // Handle mentions: extract mentions in the format "@FirstName FamilyName"
      preg_match_all('/@([A-Za-z]+)\s+([A-Za-z]+)\s/', $notmsg, $matches, PREG_SET_ORDER);

      if (!empty($matches)) {
        foreach ($matches as $match) {
          $firstName = trim($match[1]);
          $familyName = trim($match[2]);

          // Get student ID from the database
          $studentStmt = $dbh->prepare("SELECT StuID FROM tblstudent WHERE FirstName = :fname AND FamilyName = :lname LIMIT 1");
          $studentStmt->bindValue(':fname', $firstName, PDO::PARAM_STR);
          $studentStmt->bindValue(':lname', $familyName, PDO::PARAM_STR);
          $studentStmt->execute();
          $student = $studentStmt->fetch(PDO::FETCH_OBJ);

          if ($student) {
            $messageSQL = "INSERT INTO tblmessages (SenderID, SenderType, RecipientStuID, Subject, Message, IsRead, Timestamp) VALUES (:sid, :stype, :stuid, :subject, :msg, 0, NOW())";
            $messageStmt = $dbh->prepare($messageSQL);
            $messageStmt->execute([':sid' => $_SESSION['sturecmsaid'], ':stype' => 'admin', ':stuid' => $student->StuID, ':subject' => "You were mentioned in a notice: " . $nottitle, ':msg' => "You were mentioned in the notice titled '{$nottitle}'.\n\nContent:\n" . $notmsg]);
          }
        }
      }
    } else {
      $error_message = "Something Went Wrong. Please try again.";
    }
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>

    <title>Student Profiling System|| Add Notice</title>
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
              <h3 class="page-title">Add Notice </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page"> Add Notice</li>
                </ol>
              </nav>
            </div>
            <div class="row">

              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title" style="text-align: center;">Add Notice</h4>
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success"><?php echo htmlentities($success_message); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo htmlentities($error_message); ?></div>
                    <?php endif; ?>

                    <form class="forms-sample" method="post" enctype="multipart/form-data">

                      <div class="form-group">
                        <label for="exampleInputName1">Notice Title</label>
                        <input type="text" name="nottitle" value="" class="form-control" required='true'>
                      </div>

                      <div class="form-group">
                        <label for="exampleInputName1">Notice Message</label>
                        <textarea name="notmsg" id="notmsg" value="" class="form-control" style="height: 30vh;" required='true'></textarea>
                      </div>

                      <button type="submit" class="btn btn-primary mr-2" name="submit">Add</button>

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
    <script src="js/typeahead.js"></script>
    <script src="js/select2.js"></script>
    <script src="js/mention.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const notemsgTextarea = document.getElementById('notmsg');
        if(notemsgTextarea) {
          initializeMention(notemsgTextarea, 'search.php?mention_suggest=1');
        }
      });
    </script>
  </body>

  </html><?php } ?>