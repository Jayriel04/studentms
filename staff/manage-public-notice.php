<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsstaffid'] == 0)) {
  header('location:logout.php');
} else {
  // Delete
  if (isset($_GET['delid'])) {
    $rid = intval($_GET['delid']);
    $sql = "DELETE FROM tblpublicnotice WHERE ID=:rid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':rid', $rid, PDO::PARAM_INT);
    $query->execute();
    $_SESSION['delete_message'] = 'Public notice has been deleted successfully.';
    echo "<script>window.location.href = 'manage-public-notice.php'</script>";
    exit;
  }

  if (isset($_SESSION['delete_message'])) {
    $delete_message = $_SESSION['delete_message'];
    unset($_SESSION['delete_message']);
  }

  // Add Public Notice
  $add_success_message = $add_error_message = '';
  $openAddModal = false;
  if (isset($_POST['add_public_notice'])) {
    $nottitle = trim($_POST['nottitle'] ?? '');
    $notmsg = trim($_POST['notmsg'] ?? '');
    if ($nottitle === '' || $notmsg === '') {
      $add_error_message = "Please provide both title and message.";
      $openAddModal = true;
    } else {
      $sql = "INSERT INTO tblpublicnotice (NoticeTitle, NoticeMessage, CreationDate) VALUES (:nottitle, :notmsg, NOW())";
      $query = $dbh->prepare($sql);
      $query->bindParam(':nottitle', $nottitle, PDO::PARAM_STR);
      $query->bindParam(':notmsg', $notmsg, PDO::PARAM_STR);
      $query->execute();
      $LastInsertId = $dbh->lastInsertId();
      if ($LastInsertId > 0) {
        $add_success_message = "Public notice has been added successfully.";
      } else {
        $add_error_message = "Something went wrong. Please try again.";
        $openAddModal = true;
      }
    }
  }

  // Edit Public Notice (modal)
  $edit_success_message = $edit_error_message = '';
  $openEditModal = false;
  if (isset($_POST['edit_public_notice'])) {
    $edit_id = intval($_POST['edit_id'] ?? 0);
    $edit_title = trim($_POST['edit_nottitle'] ?? '');
    $edit_msg = trim($_POST['edit_notmsg'] ?? '');

    if ($edit_id <= 0 || $edit_title === '' || $edit_msg === '') {
      $edit_error_message = "Please provide both title and message.";
      $openEditModal = true;
    } else {
      $sql = "UPDATE tblpublicnotice SET NoticeTitle=:nottitle, NoticeMessage=:notmsg WHERE ID=:eid";
      $query = $dbh->prepare($sql);
      $query->bindParam(':nottitle', $edit_title, PDO::PARAM_STR);
      $query->bindParam(':notmsg', $edit_msg, PDO::PARAM_STR);
      $query->bindParam(':eid', $edit_id, PDO::PARAM_INT);
      if ($query->execute()) {
        $edit_success_message = "Public notice has been updated successfully.";
      } else {
        $edit_error_message = "Something went wrong. Please try again.";
        $openEditModal = true;
      }
    }
  }

  // Search
  $searchdata = '';
  if (isset($_POST['search'])) {
    $searchdata = trim($_POST['searchdata'] ?? '');
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <title>Staff Profiling System || Manage Public Notice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/modal.css">
    <link rel="stylesheet" href="./css/style(v2).css">
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
              <h3 class="page-title">Manage Public Notice</h3>
              <button type="button" class="add-btn" data-target="#addPublicModal"
                style="margin-right: 20px;">+ Add New Public
                Notice</button>
            </div>

            <div class="row">
              <div class="col-md-12 grid-margin stretch-card">
                <div class="table-card">
                  <div class="table-header">
                    <h2 class="table-title">Manage Public Notices</h2>
                    <div class="table-actions">
                      <form method="post" class="d-flex" style="gap: 12px;">
                        <input type="text" name="searchdata" class="form-control" placeholder="Search by Notice Title"
                          value="<?php echo htmlentities($searchdata); ?>">
                        <button type="submit" name="search" class="filter-btn" style="width: 20vh;">🔍 Search</button>
                      </form>
                    </div>
                  </div>

                  <!-- Add Public Notice Modal -->
                  <div class="new-modal-overlay" id="addPublicModalOverlay">
                    <div class="new-modal">
                      <div class="new-modal-header">
                        <h2 class="new-modal-title">Add New Public Notice</h2>
                        <button type="button" class="new-close-btn">&times;</button>
                      </div>
                      <form method="post" id="addPublicForm">
                        <div class="new-form-group">
                          <label for="nottitle_public_modal" class="new-form-label">Notice Title</label>
                          <input type="text" name="nottitle" id="nottitle_public_modal" class="new-form-input" required
                            placeholder="Enter notice title">
                        </div>

                        <div class="new-form-group">
                          <label for="notmsg_public_modal" class="new-form-label">Notice Message</label>
                          <textarea name="notmsg" id="notmsg_public_modal" class="new-form-textarea" required
                            placeholder="Enter notice details..."></textarea>
                        </div>

                        <div class="new-modal-footer">
                          <button type="button" class="new-btn new-btn-cancel">Cancel</button>
                          <button type="submit" class="new-btn new-btn-submit" name="add_public_notice">Add Public
                            Notice</button>
                        </div>
                      </form>
                    </div>
                  </div>

                  <!-- Edit Public Notice Modal -->
                  <div class="new-modal-overlay" id="editPublicModalOverlay">
                    <div class="new-modal">
                      <div class="new-modal-header">
                        <h2 class="new-modal-title">Edit Public Notice</h2>
                        <button type="button" class="new-close-btn">&times;</button>
                      </div>
                      <form method="post" id="editPublicForm">
                        <input type="hidden" name="edit_id" id="edit_id_public_modal">
                        <div class="new-form-group">
                          <label for="edit_nottitle_public_modal" class="new-form-label">Notice Title</label>
                          <input type="text" name="edit_nottitle" id="edit_nottitle_public_modal" class="new-form-input"
                            required placeholder="Enter notice title">
                        </div>

                        <div class="new-form-group">
                          <label for="edit_notmsg_public_modal" class="new-form-label">Notice Message</label>
                          <textarea name="edit_notmsg" id="edit_notmsg_public_modal" class="new-form-textarea" required
                            placeholder="Enter notice details..."></textarea>
                        </div>

                        <div class="new-modal-footer">
                          <button type="button" class="new-btn new-btn-cancel">Cancel</button>
                          <button type="submit" class="new-btn new-btn-submit" name="edit_public_notice">Save
                            Changes</button>
                        </div>
                      </form>
                    </div>
                  </div>

                  <div class="table-wrapper">
                    <table class="table">
                      <thead>
                        <tr>
                          <th class="font-weight-bold">Notice Title</th>
                          <th class="font-weight-bold">Notice Date</th>
                          <th class="font-weight-bold">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $sql = "SELECT ID, NoticeTitle, CreationDate, NoticeMessage FROM tblpublicnotice";
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
                                   <button type="button" class="action-btn edit btn-edit-public" title="Edit"
                                     data-id="<?php echo htmlentities($row->ID); ?>"
                                     data-title="<?php echo htmlentities($row->NoticeTitle); ?>"
                                     data-msg="<?php echo htmlspecialchars($row->NoticeMessage, ENT_QUOTES); ?>">✏️</button>
                                   <a href="manage-public-notice.php?delid=<?php echo htmlentities($row->ID); ?>"
                                     onclick="return confirm('Do you really want to Delete ?');" class="action-btn"
                                     style="background: #fee2e2; color: #ef4444;" title="Delete">🗑️</a>
                                 </div>
                               </td>
                            </tr>
                          <?php }
                        } else { ?>
                          <tr>
                            <td colspan="3" style="text-align:center;color:red;">No Record Found</td>
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
    <script src="js/toast.js"></script>
    <script src="js/manage-public-notice.js"></script>
  </body>

  </html>
<?php } ?>