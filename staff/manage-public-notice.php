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
    <link rel="stylesheet" href="./css/style(v2).css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
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
              <button type="button" class="add-btn" data-toggle="modal" data-target="#addPublicModal" style="margin-right: 20px;">+ Add New Public
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
                  <!-- ... (modal content remains the same) ... -->
                  <div class="modal fade" id="addPublicModal" tabindex="-1" role="dialog"
                    aria-labelledby="addPublicModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                      <div class="modal-content">
                        <form method="post" id="addPublicForm">
                          <div class="modal-header">
                            <h5 class="modal-title" id="addPublicModalLabel">Add Public Notice</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                          </div>
                          <div class="modal-body">
                            <?php if (!empty($add_success_message)): ?>
                              <div class="alert alert-success"><?php echo htmlentities($add_success_message); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($add_error_message)): ?>
                              <div class="alert alert-danger"><?php echo htmlentities($add_error_message); ?></div>
                            <?php endif; ?>

                            <div class="form-group">
                              <label>Notice Title</label>
                              <input type="text" name="nottitle" id="nottitle_public_modal" class="form-control" required>
                            </div>

                            <div class="form-group">
                              <label>Notice Message</label>
                              <textarea name="notmsg" id="notmsg_public_modal" class="form-control" style="height:30vh;"
                                required></textarea>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" name="add_public_notice">Add</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>

                  <!-- Edit Public Notice Modal -->
                  <div class="modal fade" id="editPublicModal" tabindex="-1" role="dialog"
                    aria-labelledby="editPublicModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                      <div class="modal-content">
                        <form method="post" id="editPublicForm">
                          <div class="modal-header">
                            <h5 class="modal-title" id="editPublicModalLabel">Edit Public Notice</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                          </div>
                          <div class="modal-body">
                            <?php if (!empty($edit_success_message)): ?>
                              <div class="alert alert-success"><?php echo htmlentities($edit_success_message); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($edit_error_message)): ?>
                              <div class="alert alert-danger"><?php echo htmlentities($edit_error_message); ?></div>
                            <?php endif; ?>

                            <input type="hidden" name="edit_id" id="edit_id_public_modal">
                            <div class="form-group">
                              <label>Notice Title</label>
                              <input type="text" name="edit_nottitle" id="edit_nottitle_public_modal" class="form-control"
                                required>
                            </div>

                            <div class="form-group">
                              <label>Notice Message</label>
                              <textarea name="edit_notmsg" id="edit_notmsg_public_modal" class="form-control"
                                style="height:30vh;" required></textarea>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" name="edit_public_notice">Save changes</button>
                          </div>
                        </form>
                      </div>
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
                              <td><?php echo htmlentities($row->NoticeTitle); ?></td>
                              <td><?php echo date('M d, Y', strtotime($row->CreationDate)); ?></td>
                              <td>
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
                            <td colspan="4" style="text-align:center;color:red;">No Record Found</td>
                          </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <?php include_once('includes/footer.php'); ?>
          </div>
        </div>
      </div>
    </div>

    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        <?php if (isset($delete_message)): ?>
          toastr.options = { "positionClass": "toast-top-right", "closeButton": true };
          toastr.success(<?php echo json_encode($delete_message); ?>);
        <?php endif; ?>

        <?php if (!empty($add_success_message)): ?>
          toastr.options = { "positionClass": "toast-top-right", "closeButton": true };
          toastr.success(<?php echo json_encode($add_success_message); ?>);
          if (window.$) $('#addPublicModal').modal('hide'); else if (typeof bootstrap !== "undefined") try { new bootstrap.Modal(document.getElementById('addPublicModal')).hide(); } catch (e) { }
        <?php endif; ?>

        <?php if (!empty($add_error_message)): ?>
          toastr.options = { "positionClass": "toast-top-right", "closeButton": true };
          toastr.error(<?php echo json_encode($add_error_message); ?>);
          if (window.$) $('#addPublicModal').modal('show'); else if (typeof bootstrap !== "undefined") new bootstrap.Modal(document.getElementById('addPublicModal')).show();
        <?php endif; ?>

        <?php if ($openAddModal): ?>
          if (window.$) $('#addPublicModal').modal('show'); else if (typeof bootstrap !== "undefined") new bootstrap.Modal(document.getElementById('addPublicModal')).show();
        <?php endif; ?>

        <?php if (!empty($edit_success_message)): ?>
          toastr.options = { "positionClass": "toast-top-right", "closeButton": true };
          toastr.success(<?php echo json_encode($edit_success_message); ?>);
          if (window.$) $('#editPublicModal').modal('hide'); else if (typeof bootstrap !== "undefined") try { new bootstrap.Modal(document.getElementById('editPublicModal')).hide(); } catch (e) { }
        <?php endif; ?>

        <?php if (!empty($edit_error_message)): ?>
          toastr.options = { "positionClass": "toast-top-right", "closeButton": true };
          toastr.error(<?php echo json_encode($edit_error_message); ?>);
          if (window.$) $('#editPublicModal').modal('show'); else if (typeof bootstrap !== "undefined") new bootstrap.Modal(document.getElementById('editPublicModal')).show();
        <?php endif; ?>

        <?php if ($openEditModal && !empty($_POST)): ?>
            (function () {
              var id = <?php echo json_encode($_POST['edit_id'] ?? ''); ?>;
              var title = <?php echo json_encode($_POST['edit_nottitle'] ?? ''); ?>;
              var msg = <?php echo json_encode($_POST['edit_notmsg'] ?? ''); ?>;
              if (document.getElementById('edit_id_public_modal')) document.getElementById('edit_id_public_modal').value = id;
              if (document.getElementById('edit_nottitle_public_modal')) document.getElementById('edit_nottitle_public_modal').value = title;
              if (document.getElementById('edit_notmsg_public_modal')) document.getElementById('edit_notmsg_public_modal').value = msg;
              if (window.$) $('#editPublicModal').modal('show'); else if (typeof bootstrap !== "undefined") new bootstrap.Modal(document.getElementById('editPublicModal')).show();
            })();
        <?php endif; ?>

        // wire edit buttons
        var editButtons = document.querySelectorAll('.btn-edit-public');
        editButtons.forEach(function (btn) {
          btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            var title = this.getAttribute('data-title');
            var msg = this.getAttribute('data-msg');
            document.getElementById('edit_id_public_modal').value = id;
            document.getElementById('edit_nottitle_public_modal').value = title;
            try {
              var decoded = msg.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&#039;/g, "'").replace(/&amp;/g, '&');
              document.getElementById('edit_notmsg_public_modal').value = decoded;
            } catch (e) {
              document.getElementById('edit_notmsg_public_modal').value = msg;
            }
            if (window.$) $('#editPublicModal').modal('show'); else if (typeof bootstrap !== "undefined") new bootstrap.Modal(document.getElementById('editPublicModal')).show();
          });
        });
      });
    </script>
  </body>

  </html>
<?php } ?>