<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid'] == 0)) {
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

  // Add Public Notice (moved from add-public-notice.php)
  $add_success_message = $add_error_message = '';
  if (isset($_POST['add_public_notice'])) {
    $nottitle = trim($_POST['nottitle'] ?? '');
    $notmsg = trim($_POST['notmsg'] ?? '');

    if ($nottitle === '' || $notmsg === '') {
      $add_error_message = "Please provide both title and message.";
    } else {
      $sql = "INSERT INTO tblpublicnotice (NoticeTitle, NoticeMessage) VALUES (:nottitle, :notmsg)";
      $query = $dbh->prepare($sql);
      $query->bindParam(':nottitle', $nottitle, PDO::PARAM_STR);
      $query->bindParam(':notmsg', $notmsg, PDO::PARAM_STR);
      $query->execute();
      $LastInsertId = $dbh->lastInsertId();
      if ($LastInsertId > 0) {
        $add_success_message = "Public notice has been added successfully.";
      } else {
        $add_error_message = "Something went wrong. Please try again.";
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
      $edit_error_message = "Please provide title and message.";
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

  // Search functionality
  $searchdata = '';
  if (isset($_POST['search'])) {
    $searchdata = trim($_POST['searchdata'] ?? '');
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <title>Student Profiling System || Manage Public Notice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
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
              <h3 class="page-title">Manage Public Notice</h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Manage Public Notice</li>
                </ol>
              </nav>
            </div>

            <div class="row">
              <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="d-sm-flex align-items-center mb-4 responsive-search-form">
                      <h4 class="card-title mb-sm-0">Manage Public Notice</h4>
                      <form method="post" class="form-inline ml-auto" style="gap: 0.5rem;">
                        <input type="text" name="searchdata" class="form-control" placeholder="Search by Notice Title"
                          value="<?php echo htmlentities($searchdata); ?>">
                        <button type="submit" name="search" class="btn btn-primary">Search</button>

                        <!-- Add Public Notice button opens modal -->
                        <button type="button" class="btn btn-success ml-2" data-toggle="modal" data-target="#addPublicModal">
                          Add Public Notice
                        </button>
                      </form>
                    </div>

                    <!-- Add Public Notice Modal -->
                    <div class="modal fade" id="addPublicModal" tabindex="-1" role="dialog" aria-labelledby="addPublicModalLabel" aria-hidden="true">
                      <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                          <form method="post" id="addPublicForm">
                            <div class="modal-header">
                              <h5 class="modal-title" id="addPublicModalLabel">Add Public Notice</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <?php if (!empty($add_success_message)): ?>
                                <div class="alert alert-success"><?php echo htmlentities($add_success_message); ?></div>
                              <?php endif; ?>
                              <?php if (!empty($add_error_message)): ?>
                                <div class="alert alert-danger"><?php echo htmlentities($add_error_message); ?></div>
                              <?php endif; ?>

                              <div class="form-group">
                                <label for="nottitle_public_modal">Notice Title</label>
                                <input type="text" name="nottitle" id="nottitle_public_modal" class="form-control" required>
                              </div>

                              <div class="form-group">
                                <label for="notmsg_public_modal">Notice Message</label>
                                <textarea name="notmsg" id="notmsg_public_modal" class="form-control" style="height: 30vh;" required></textarea>
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
                    <div class="modal fade" id="editPublicModal" tabindex="-1" role="dialog" aria-labelledby="editPublicModalLabel" aria-hidden="true">
                      <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                          <form method="post" id="editPublicForm">
                            <div class="modal-header">
                              <h5 class="modal-title" id="editPublicModalLabel">Edit Public Notice</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
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
                                <label for="edit_nottitle_public_modal">Notice Title</label>
                                <input type="text" name="edit_nottitle" id="edit_nottitle_public_modal" class="form-control" required>
                              </div>

                              <div class="form-group">
                                <label for="edit_notmsg_public_modal">Notice Message</label>
                                <textarea name="edit_notmsg" id="edit_notmsg_public_modal" class="form-control" style="height: 30vh;" required></textarea>
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

                    <div class="table-responsive border rounded p-1 card-view">
                      <table class="table">
                        <thead>
                          <tr>
                            <th class="font-weight-bold">S.No</th>
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
                          $query = $dbh->prepare($sql);
                          if (!empty($searchdata)) {
                            $query->bindValue(':searchdata', '%' . $searchdata . '%', PDO::PARAM_STR);
                          }
                          $query->execute();
                          $results = $query->fetchAll(PDO::FETCH_OBJ);
                          $cnt = 1;
                          if ($query->rowCount() > 0) {
                            foreach ($results as $row) { ?>
                              <tr>
                                <td data-label="S.No"><?php echo htmlentities($cnt); ?></td>
                                <td data-label="Notice Title"><?php echo htmlentities($row->NoticeTitle); ?></td>
                                <td data-label="Notice Date"><?php echo htmlentities($row->CreationDate); ?></td>
                                <td data-label="Action">
                                  <div>
                                    <button type="button" class="btn btn-xs btn-edit-public"
                                      data-id="<?php echo htmlentities($row->ID); ?>"
                                      data-title="<?php echo htmlentities($row->NoticeTitle); ?>"
                                      data-msg="<?php echo htmlspecialchars($row->NoticeMessage, ENT_QUOTES); ?>"
                                      style="background-color: #4CAF50; color: white;">Edit</button>

                                    <a href="manage-public-notice.php?delid=<?php echo htmlentities($row->ID); ?>"
                                      onclick="return confirm('Do you really want to Delete ?');" class="btn btn-xs ml-2"
                                      style="background-color: #FF5733; color: white;">Delete</a>
                                  </div>
                                </td>
                              </tr>
                              <?php $cnt++;
                            }
                          } else { ?>
                            <tr>
                              <td colspan="4" style="text-align: center; color: red;">No Record Found</td>
                            </tr>
                          <?php } ?>
                        </tbody>
                      </table>
                    </div>

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
      <?php if (isset($delete_message)): ?>
        toastr.options = { "positionClass": "toast-top-right", "closeButton": true };
        toastr.success(<?php echo json_encode($delete_message); ?>);
      <?php endif; ?>
      <?php if (!empty($add_success_message)): ?>
        toastr.options = { "positionClass": "toast-top-right", "closeButton": true };
        toastr.success(<?php echo json_encode($add_success_message); ?>);
      <?php endif; ?>
      <?php if (!empty($add_error_message)): ?>
        toastr.options = { "positionClass": "toast-top-right", "closeButton": true };
        toastr.error(<?php echo json_encode($add_error_message); ?>);
      <?php endif; ?>
      <?php if (!empty($edit_success_message)): ?>
        toastr.options = { "positionClass": "toast-top-right", "closeButton": true };
        toastr.success(<?php echo json_encode($edit_success_message); ?>);
      <?php endif; ?>
      <?php if (!empty($edit_error_message)): ?>
        toastr.options = { "positionClass": "toast-top-right", "closeButton": true };
        toastr.error(<?php echo json_encode($edit_error_message); ?>);
      <?php endif; ?>

      document.addEventListener('DOMContentLoaded', function () {
        // if add validation failed, open add modal
        <?php if (!empty($add_error_message)): ?>
          if (window.$) { $('#addPublicModal').modal('show'); } else if (typeof bootstrap !== "undefined") { new bootstrap.Modal(document.getElementById('addPublicModal')).show(); }
        <?php endif; ?>

        // if edit validation failed after POST, reopen edit modal with posted values
        <?php if ($openEditModal && !empty($_POST)): ?>
          if (window.$) {
            $('#editPublicModal').modal('show');
            $('#edit_id_public_modal').val(<?php echo json_encode($_POST['edit_id'] ?? ''); ?>);
            $('#edit_nottitle_public_modal').val(<?php echo json_encode($_POST['edit_nottitle'] ?? ''); ?>);
            $('#edit_notmsg_public_modal').val(<?php echo json_encode($_POST['edit_notmsg'] ?? ''); ?>);
          } else if (typeof bootstrap !== "undefined") {
            document.getElementById('edit_id_public_modal').value = <?php echo json_encode($_POST['edit_id'] ?? ''); ?>;
            document.getElementById('edit_nottitle_public_modal').value = <?php echo json_encode($_POST['edit_nottitle'] ?? ''); ?>;
            document.getElementById('edit_notmsg_public_modal').value = <?php echo json_encode($_POST['edit_notmsg'] ?? ''); ?>;
            new bootstrap.Modal(document.getElementById('editPublicModal')).show();
          }
        <?php endif; ?>

        // wire edit buttons to populate modal
        var editButtons = document.querySelectorAll('.btn-edit-public');
        editButtons.forEach(function (btn) {
          btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            var title = this.getAttribute('data-title');
            var msg = this.getAttribute('data-msg');

            document.getElementById('edit_id_public_modal').value = id;
            document.getElementById('edit_nottitle_public_modal').value = title;
            // decode HTML entities back if needed
            try {
              var decoded = msg.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&#039;/g, "'").replace(/&amp;/g, '&');
              document.getElementById('edit_notmsg_public_modal').value = decoded;
            } catch (e) {
              document.getElementById('edit_notmsg_public_modal').value = msg;
            }

            if (window.$) { $('#editPublicModal').modal('show'); } else if (typeof bootstrap !== "undefined") { new bootstrap.Modal(document.getElementById('editPublicModal')).show(); }
          });
        });
      });
    </script>
  </body>

  </html>
<?php } ?>