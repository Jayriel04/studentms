<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsstaffid']) == 0) {
  header('location:logout.php');
  exit;
} else {
  // Delete
  if (isset($_GET['delid'])) {
    $rid = intval($_GET['delid']);
    $sql = "DELETE FROM tblnotice WHERE ID = :rid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':rid', $rid, PDO::PARAM_INT);
    $query->execute();
    echo "<script>if(window.showToast) showToast('Notice deleted successfully.','success');</script>";
    echo "<script>window.location.href = 'manage-notice.php'</script>";
    exit;
  }

  // Add Notice (moved from add-notice.php)
  $add_success_message = $add_error_message = '';
  $openAddModal = false;
  if (isset($_POST['add_notice']) || isset($_POST['submit'])) {
    $nottitle = trim($_POST['nottitle'] ?? '');
    $notmsg = trim($_POST['notmsg'] ?? '');

    if ($nottitle === '' || $notmsg === '') {
      $add_error_message = "Please provide both title and message.";
      $openAddModal = true;
    } else {
      $sql = "INSERT INTO tblnotice (NoticeTitle, NoticeMsg, CreationDate) VALUES (:nottitle, :notmsg, NOW())";
      $query = $dbh->prepare($sql);
      $query->bindParam(':nottitle', $nottitle, PDO::PARAM_STR);
      $query->bindParam(':notmsg', $notmsg, PDO::PARAM_STR);
      $query->execute();
      $LastInsertId = $dbh->lastInsertId();

      if ($LastInsertId > 0) {
        $add_success_message = "Notice has been added.";
        // Handle mentions: extract mentions like "@FirstName FamilyName"
        preg_match_all('/@([A-Za-z]+)\s+([A-Za-z]+)/', $notmsg, $matches, PREG_SET_ORDER);
        if (!empty($matches)) {
          foreach ($matches as $match) {
            $firstName = trim($match[1]);
            $familyName = trim($match[2]);
            $studentStmt = $dbh->prepare("SELECT StuID FROM tblstudent WHERE FirstName = :fname AND FamilyName = :lname LIMIT 1");
            $studentStmt->bindValue(':fname', $firstName, PDO::PARAM_STR);
            $studentStmt->bindValue(':lname', $familyName, PDO::PARAM_STR);
            $studentStmt->execute();
            $student = $studentStmt->fetch(PDO::FETCH_OBJ);
            if ($student) {
              $messageSQL = "INSERT INTO tblmessages (SenderID, SenderType, RecipientStuID, Subject, Message, IsRead, Timestamp) VALUES (:sid, :stype, :stuid, :subject, :msg, 0, NOW())";
              $messageStmt = $dbh->prepare($messageSQL);
              $messageStmt->execute([
                ':sid' => $_SESSION['sturecmsstaffid'],
                ':stype' => 'staff',
                ':stuid' => $student->StuID,
                ':subject' => "You were mentioned in a notice: " . $nottitle,
                ':msg' => "You were mentioned in the notice titled '{$nottitle}'.\n\nContent:\n" . $notmsg
              ]);
            }
          }
        }
      } else {
        $add_error_message = "Something Went Wrong. Please try again.";
        $openAddModal = true;
      }
    }
  }

  // Edit Notice (moved into modal)
  $edit_success_message = $edit_error_message = '';
  $openEditModal = false;
  if (isset($_POST['edit_notice'])) {
    $edit_id = intval($_POST['edit_id'] ?? 0);
    $edit_title = trim($_POST['edit_nottitle'] ?? '');
    $edit_msg = trim($_POST['edit_notmsg'] ?? '');

    if ($edit_id <= 0 || $edit_title === '' || $edit_msg === '') {
      $edit_error_message = "Please provide both title and message.";
      $openEditModal = true;
    } else {
      $sql = "UPDATE tblnotice SET NoticeTitle = :nottitle, NoticeMsg = :notmsg WHERE ID = :eid";
      $query = $dbh->prepare($sql);
      $query->bindParam(':nottitle', $edit_title, PDO::PARAM_STR);
      $query->bindParam(':notmsg', $edit_msg, PDO::PARAM_STR);
      $query->bindParam(':eid', $edit_id, PDO::PARAM_INT);
      if ($query->execute()) {
        $edit_success_message = "Notice has been updated successfully.";

        // Handle mentions on update
        preg_match_all('/@([A-Za-z]+)\s+([A-Za-z]+)/', $edit_msg, $matches, PREG_SET_ORDER);
        if (!empty($matches)) {
          foreach ($matches as $match) {
            $firstName = trim($match[1]);
            $familyName = trim($match[2]);
            $studentStmt = $dbh->prepare("SELECT StuID FROM tblstudent WHERE FirstName = :fname AND FamilyName = :lname LIMIT 1");
            $studentStmt->bindValue(':fname', $firstName, PDO::PARAM_STR);
            $studentStmt->bindValue(':lname', $familyName, PDO::PARAM_STR);
            $studentStmt->execute();
            $student = $studentStmt->fetch(PDO::FETCH_OBJ);
            if ($student) {
              $messageSQL = "INSERT INTO tblmessages (SenderID, SenderType, RecipientStuID, Subject, Message, IsRead, Timestamp) VALUES (:sid, :stype, :stuid, :subject, :msg, 0, NOW())";
              $messageStmt = $dbh->prepare($messageSQL);
              $messageStmt->execute([
                ':sid' => $_SESSION['sturecmsstaffid'],
                ':stype' => 'staff',
                ':stuid' => $student->StuID,
                ':subject' => "You were mentioned in an updated notice: " . $edit_title,
                ':msg' => "You were mentioned in the updated notice titled '{$edit_title}'.\n\nContent:\n" . $edit_msg
              ]);
            }
          }
        }
      } else {
        $edit_error_message = "Something went wrong. Please try again.";
        $openEditModal = true;
      }
    }
  }

  // Search functionality
  $searchdata = '';
  if (isset($_POST['search'])) {
    $searchdata = trim($_POST['searchdata'] ?? '');
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <title>Student Profiling System || Manage Notice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="./css/toaster.css">
    <link rel="stylesheet" href="./css/style(v2).css">
    <link rel="stylesheet" href="./css/modal.css">
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
              <h3 class="page-title">Manage Notice</h3>
              <button type="button" class="add-btn" data-toggle="modal" data-target="#addNoticeModal"
                style="margin-right: 20px;">+ Add New Notice</button>
            </div>
            <div class="row">
              <div class="col-md-12 grid-margin stretch-card">
                <div class="table-card">
                  <div class="table-header">
                    <h2 class="table-title">Manage Notices</h2>
                    <div class="table-actions">
                      <form method="post" class="d-flex" style="gap: 12px;">
                        <input type="text" name="searchdata" class="search-box" placeholder="Search by Notice Title"
                          value="<?php echo htmlentities($searchdata); ?>">
                        <button type="submit" name="search" class="filter-btn">🔍 Search</button>
                      </form>
                    </div>
                  </div>

                  <!-- Add Notice Modal -->
                  <div class="new-modal-overlay" id="addNoticeModalOverlay">
                    <div class="new-modal">
                      <div class="new-modal-header">
                        <h2 class="new-modal-title">Add New Notice</h2>
                        <button type="button" class="new-close-btn">&times;</button>
                      </div>
                      <form method="post" id="addNoticeForm">
                        <div class="new-form-group">
                          <label for="nottitle_modal" class="new-form-label">Notice Title</label>
                          <input type="text" name="nottitle" id="nottitle_modal" class="new-form-input" required
                            placeholder="Enter notice title" style="text-transform: capitalize;">
                        </div>
                        <div class="new-form-group">
                          <label for="notmsg_modal" class="new-form-label">Notice Message</label>
                          <textarea name="notmsg" id="notmsg_modal" class="new-form-textarea" required
                            placeholder="Enter notice details..." style="text-transform: capitalize;"></textarea>
                          <small class="text-muted">Use @FirstName LastName to mention students.</small>
                        </div>

                        <div class="new-modal-footer">
                          <button type="button" class="new-btn new-btn-cancel">Cancel</button>
                          <button type="submit" class="new-btn new-btn-submit" name="add_notice">Add Notice</button>
                        </div>
                      </form>
                    </div>
                  </div>

                  <!-- Edit Notice Modal -->
                  <div class="new-modal-overlay" id="editNoticeModalOverlay">
                    <div class="new-modal">
                      <div class="new-modal-header">
                        <h2 class="new-modal-title">Edit Notice</h2>
                        <button type="button" class="new-close-btn">&times;</button>
                      </div>
                      <form method="post" id="editNoticeForm">
                        <input type="hidden" name="edit_id" id="edit_id_modal">
                        <div class="new-form-group">
                          <label for="edit_nottitle_modal" class="new-form-label">Notice Title</label>
                          <input type="text" name="edit_nottitle" id="edit_nottitle_modal" class="new-form-input" required
                            placeholder="Enter notice title" style="text-transform: capitalize;">
                        </div>
                        <div class="new-form-group">
                          <label for="edit_notmsg_modal" class="new-form-label">Notice Message</label>
                          <textarea name="edit_notmsg" id="edit_notmsg_modal" class="new-form-textarea" required
                            placeholder="Enter notice details..." style="text-transform: capitalize;"></textarea>
                          <small class="text-muted">Use @FirstName LastName to mention students.</small>
                        </div>

                        <div class="new-modal-footer">
                          <button type="button" class="new-btn new-btn-cancel">Cancel</button>
                          <button type="submit" class="new-btn new-btn-submit" name="edit_notice">Save Changes</button>
                        </div>
                      </form>
                    </div>
                  </div>

                  <div class="table-wrapper">
                    <table class="table">
                      <thead>
                        <tr>
                          <th>Notice Title</th>
                          <th>Notice Date</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $sql = "SELECT NoticeTitle, CreationDate, ID as nid, NoticeMsg FROM tblnotice";
                        if (!empty($searchdata)) {
                          $sql .= " WHERE NoticeTitle LIKE :searchdata";
                        }
                        $sql .= " ORDER BY CreationDate DESC";
                        $query = $dbh->prepare($sql);
                        if (!empty($searchdata)) {
                          $query->bindValue(':searchdata', '%' . $searchdata . '%', PDO::PARAM_STR);
                        }
                        $query->execute();
                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                        if ($query->rowCount() > 0) {
                          foreach ($results as $row) { ?>
                            <tr>
                              <td data-label="Notice Title"><?php echo htmlentities($row->NoticeTitle); ?></td>
                              <td data-label="Notice Date"><?php echo date('M d, Y', strtotime($row->CreationDate)); ?></td>
                              <td data-label="Action">
                                 <div class="action-buttons">
                                   <button type="button" class="action-btn edit btn-edit-notice" title="Edit"
                                     data-id="<?php echo htmlentities($row->nid); ?>"
                                     data-title="<?php echo htmlentities($row->NoticeTitle); ?>"
                                     data-msg="<?php echo htmlspecialchars($row->NoticeMsg, ENT_QUOTES); ?>">✏️</button>
                                   <a href="manage-notice.php?delid=<?php echo htmlentities($row->nid); ?>"
                                     onclick="return confirm('Do you really want to Delete ?');" class="action-btn"
                                     style="background: #fee2e2; color: #ef4444;" title="Delete">🗑️</a>
                                 </div>
                               </td>
                            </tr>
                          <?php }
                        } else { ?>
                          <tr>
                            <td colspan="3" style="text-align: center; color: red;">No Record Found</td>
                          </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script src="vendors/select2/select2.min.js"></script>
    <script src="vendors/typeahead.js/typeahead.bundle.min.js"></script>
    <script src="js/mention.js"></script>
    <script src="js/toast.js"></script>
    <?php if (!empty($add_success_message)): ?>
      <script>toastr.success('<?php echo addslashes($add_success_message); ?>');</script>
    <?php endif; ?>
    <?php if (!empty($add_error_message)): ?>
      <script>toastr.error('<?php echo addslashes($add_error_message); ?>');</script>
    <?php endif; ?>
    <?php if (!empty($edit_success_message)): ?>
      <script>toastr.success('<?php echo addslashes($edit_success_message); ?>');</script>
    <?php endif; ?>
    <?php if (!empty($edit_error_message)): ?>
      <script>toastr.error('<?php echo addslashes($edit_error_message); ?>');</script>
    <?php endif; ?>
    <script src="js/manage-notice.js"></script>
  </body>

  </html>
<?php } ?>