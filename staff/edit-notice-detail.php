<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsstaffid'] == 0)) {
  header('location:logout.php');
} else {
  $success_message = '';
  if (isset($_POST['submit'])) {
    $nottitle = $_POST['nottitle'];
    $notmsg = $_POST['notmsg']; // The notice message with potential mentions
    $eid = $_GET['editid'];
    $sql = "UPDATE tblnotice SET NoticeTitle=:nottitle, NoticeMsg=:notmsg WHERE ID=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':nottitle', $nottitle, PDO::PARAM_STR);
    $query->bindParam(':notmsg', $notmsg, PDO::PARAM_STR);
    $query->bindParam(':eid', $eid, PDO::PARAM_INT);
    if ($query->execute()) {
      $success_message = "Notice has been updated successfully.";

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
            $messageStmt->execute([':sid' => $_SESSION['sturecmsstaffid'], ':stype' => 'staff', ':stuid' => $student->StuID, ':subject' => "You were mentioned in an updated notice: " . $nottitle, ':msg' => "You were mentioned in the updated notice titled '{$nottitle}'.\n\nContent:\n" . $notmsg]);
          }
        }
      }
    }
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <title>Staff Profiling System || Update Notice</title>
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
              <h3 class="page-title">Update Notice</h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Update Notice</li>
                </ol>
              </nav>
            </div>
            <div class="row">
              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title" style="text-align: center;">Update Notice</h4>
                    <?php if (!empty($success_message)): ?>
                      <div class="alert alert-success">
                        <?php echo htmlentities($success_message); ?>
                      </div>
                    <?php endif; ?>
                    <form class="forms-sample" method="post" enctype="multipart/form-data">
                      <?php
                      $eid = $_GET['editid'];
                      $sql = "SELECT NoticeTitle, NoticeMsg, ID as nid FROM tblnotice WHERE ID=:eid";
                      $query = $dbh->prepare($sql);
                      $query->bindParam(':eid', $eid, PDO::PARAM_INT);
                      $query->execute();
                      $results = $query->fetchAll(PDO::FETCH_OBJ);
                      if ($query->rowCount() > 0) {
                        foreach ($results as $row) { ?>
                          <div class="form-group">
                            <label for="exampleInputName1">Notice Title</label>
                            <input type="text" name="nottitle" value="<?php echo htmlentities($row->NoticeTitle); ?>"
                              class="form-control" required='true'>
                          </div>
                          <div class="form-group">
                            <label for="exampleInputName1">Notice Message</label>
                            <textarea name="notmsg" id="notmsg" class="form-control" style="height: 30vh;"
                              required='true'><?php echo htmlentities($row->NoticeMsg); ?></textarea>
                          </div>
                        <?php }
                      } ?>
                      <button type="submit" class="btn btn-primary mr-2" name="submit">Update</button>
                      <a href="manage-notice.php" class="btn btn-light">Back</a>
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
    <script src="js/toast.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const notemsgTextarea = document.getElementById('notmsg');
        if (notemsgTextarea) {
          initializeMention(notemsgTextarea, 'search.php?mention_suggest=1');
        }
      });
    </script>
  </body>

  </html>
<?php } ?>