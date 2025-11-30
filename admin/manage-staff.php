<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid'] == 0)) {
  header('location:logout.php');
} else {
  // Code to toggle status
  if (isset($_GET['statusid'])) {
    $sid = intval($_GET['statusid']);
    $status = intval($_GET['status']);
    $newStatus = $status == 1 ? 0 : 1; // Toggle status
    $sql = "UPDATE tblstaff SET Status=:newStatus WHERE ID=:sid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':newStatus', $newStatus, PDO::PARAM_INT);
    $query->bindParam(':sid', $sid, PDO::PARAM_INT);
    $query->execute();
    $statusMessage = $newStatus == 1 ? 'Staff activated successfully.' : 'Staff deactivated successfully.';
    echo "<script>var statusMessage = '".addslashes($statusMessage)."';</script>";
  }

  // Add Staff handling (moved from add-staff.php)
  $add_success_message = '';
  $add_error_message = '';
  $openAddModal = false;
  if (isset($_POST['add_staff'])) {
    $staffname = trim($_POST['staffname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = md5($_POST['password']); // kept same hashing as original add-staff.php
    $regdate = date('Y-m-d H:i:s');

    // Check username uniqueness
    $ret = "SELECT UserName FROM tblstaff WHERE UserName = :username";
    $query = $dbh->prepare($ret);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->execute();
    if ($query->rowCount() == 0) {
      $sql = "INSERT INTO tblstaff (StaffName, UserName, Email, Password, StaffRegdate) VALUES (:staffname, :username, :email, :password, :regdate)";
      $query = $dbh->prepare($sql);
      $query->bindParam(':staffname', $staffname, PDO::PARAM_STR);
      $query->bindParam(':username', $username, PDO::PARAM_STR);
      $query->bindParam(':email', $email, PDO::PARAM_STR);
      $query->bindParam(':password', $password, PDO::PARAM_STR);
      $query->bindParam(':regdate', $regdate, PDO::PARAM_STR);
      $query->execute();
      $LastInsertId = $dbh->lastInsertId();
      if ($LastInsertId > 0) {
        $add_success_message = "Staff has been added.";
      } else {
        $add_error_message = "Something went wrong. Please try again.";
        $openAddModal = true;
      }
    } else {
      $add_error_message = "Username already exists. Please try again.";
      $openAddModal = true;
    }
  }

  // Edit Staff handling (now handled here for modal)
  $edit_success_message = '';
  $edit_error_message = '';
  $openEditModal = false;
  if (isset($_POST['edit_staff'])) {
    $edit_id = intval($_POST['edit_id']);
    $edit_name = trim($_POST['edit_name']);
    $edit_username = trim($_POST['edit_username']);
    $edit_email = trim($_POST['edit_email']);
    $edit_password = trim($_POST['edit_password']);

    // Check duplicate username excluding current record
    $checkSql = "SELECT ID FROM tblstaff WHERE UserName = :username AND ID != :id";
    $checkQuery = $dbh->prepare($checkSql);
    $checkQuery->bindParam(':username', $edit_username, PDO::PARAM_STR);
    $checkQuery->bindParam(':id', $edit_id, PDO::PARAM_INT);
    $checkQuery->execute();

    if ($checkQuery->rowCount() > 0) {
      $edit_error_message = "Username already exists. Please choose a different one.";
      $openEditModal = true;
    } else {
      if (!empty($edit_password)) {
        // use password_hash for updates (keeps behaviour of edit-staff-detail.php)
        $hashed_password = password_hash($edit_password, PASSWORD_DEFAULT);
        $sql = "UPDATE tblstaff SET StaffName=:name, UserName=:username, Email=:email, Password=:password WHERE ID=:id";
      } else {
        $sql = "UPDATE tblstaff SET StaffName=:name, UserName=:username, Email=:email WHERE ID=:id";
      }
      $query = $dbh->prepare($sql);
      $query->bindParam(':name', $edit_name, PDO::PARAM_STR);
      $query->bindParam(':username', $edit_username, PDO::PARAM_STR);
      $query->bindParam(':email', $edit_email, PDO::PARAM_STR);
      $query->bindParam(':id', $edit_id, PDO::PARAM_INT);
      if (!empty($edit_password)) {
        $query->bindParam(':password', $hashed_password, PDO::PARAM_STR);
      }
      $query->execute();
      $edit_success_message = "Staff record has been updated successfully.";
    }
  }

  // Search and filter functionality
  $searchdata = '';
  $filter = 'all';
  if (isset($_POST['search'])) {
    $searchdata = $_POST['searchdata'];
  }
  if (isset($_POST['filter'])) {
    $filter = $_POST['filter'];
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <title>Student Profiling System || Manage Staff</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="./css/style.css">
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
              <h3 class="page-title">Manage Staff</h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Manage Staff</li>
                </ol>
              </nav>
            </div>
            <div class="row">
              <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="d-sm-flex align-items-center mb-4 responsive-search-form">
                      <h4 class="card-title mb-sm-0">Manage Staff</h4>
                      <form method="post" class="form-inline ml-auto" style="gap: 0.5rem;">
                        <input type="text" name="searchdata" class="form-control" placeholder="Search by Name or Email"
                          value="<?php echo htmlentities($searchdata); ?>">
                        <select name="filter" class="form-control">
                          <option value="all" <?php if ($filter == 'all') echo 'selected'; ?>>All</option>
                          <option value="active" <?php if ($filter == 'active') echo 'selected'; ?>>Active</option>
                          <option value="inactive" <?php if ($filter == 'inactive') echo 'selected'; ?>>Inactive</option>
                        </select>
                        <button type="submit" name="search" class="btn btn-primary">Search</button>

                        <!-- Add Staff button opens modal -->
                        <button type="button" class="btn btn-success ml-2" data-toggle="modal" data-target="#addStaffModal">
                          Add Staff
                        </button>
                      </form>
                    </div>

                    <!-- Add Staff Modal -->
                    <div class="modal fade" id="addStaffModal" tabindex="-1" role="dialog" aria-labelledby="addStaffModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <form method="post" id="addStaffForm">
                            <div class="modal-header">
                              <h5 class="modal-title" id="addStaffModalLabel">Add Staff</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <div class="form-group">
                                <label for="staffname">Staff Name</label>
                                <input type="text" name="staffname" class="form-control" required='true' style="text-transform: capitalize;">
                              </div>
                              <div class="form-group">
                                <label for="username">User Name</label>
                                <input type="text" name="username" class="form-control" required='true'>
                              </div>
                              <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" class="form-control" required='true'>
                              </div>
                              <div class="form-group" style="position: relative;">
                                <label for="password">Password</label>
                                <input type="password" id="add_password" name="password" class="form-control" required='true'>
                                <i class="icon-eye" id="toggleAddPassword"
                                  style="position: absolute; right: 15px; top: 70%; transform: translateY(-50%); cursor: pointer;"></i>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                              <button type="submit" class="btn btn-primary" name="add_staff">Add</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>

                    <!-- Edit Staff Modal -->
                    <div class="modal fade" id="editStaffModal" tabindex="-1" role="dialog" aria-labelledby="editStaffModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <form method="post" id="editStaffForm">
                            <div class="modal-header">
                              <h5 class="modal-title" id="editStaffModalLabel">Edit Staff</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <input type="hidden" name="edit_id" id="edit_id">
                              <div class="form-group">
                                <label for="edit_name">Staff Name</label>
                                <input type="text" name="edit_name" id="edit_name" class="form-control" required style="text-transform: capitalize;">
                              </div>
                              <div class="form-group">
                                <label for="edit_username">User Name</label>
                                <input type="text" name="edit_username" id="edit_username" class="form-control" required>
                              </div>
                              <div class="form-group">
                                <label for="edit_email">Email</label>
                                <input type="email" name="edit_email" id="edit_email" class="form-control" required>
                              </div>
                              <div class="form-group" style="position: relative;">
                                <label for="edit_password">Change Password</label>
                                <input type="password" id="edit_password" name="edit_password" class="form-control" placeholder="Leave blank to keep unchanged">
                                <i class="icon-eye" id="toggleEditPassword"
                                  style="position: absolute; right: 15px; top: 70%; transform: translateY(-50%); cursor: pointer;"></i>
                              </div>
                              <div class="form-group">
                                <label for="edit_regdate">Staff Regdate</label>
                                <input type="text" id="edit_regdate" class="form-control" readonly>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                              <button type="submit" class="btn btn-primary" name="edit_staff">Save changes</button>
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
                            <th class="font-weight-bold">Staff Name</th>
                            <th class="font-weight-bold">User Name</th>
                            <th class="font-weight-bold">Email</th>
                            <th class="font-weight-bold">Reg Date</th>
                            <th class="font-weight-bold">Status</th>
                            <th class="font-weight-bold">Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          $sql = "SELECT ID, StaffName, UserName, Email, StaffRegdate, Status FROM tblstaff WHERE 1=1";
                          if (!empty($searchdata)) {
                            $sql .= " AND (StaffName LIKE :searchdata OR Email LIKE :searchdata)";
                          }
                          if ($filter == 'active') {
                            $sql .= " AND Status=1";
                          } elseif ($filter == 'inactive') {
                            $sql .= " AND Status=0";
                          }
                          $sql .= " ORDER BY ID DESC";
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
                                <td data-label="Staff Name"><?php echo htmlentities($row->StaffName); ?></td>
                                <td data-label="User Name"><?php echo htmlentities($row->UserName); ?></td>
                                <td data-label="Email"><?php echo htmlentities($row->Email); ?></td>
                                <td data-label="Reg Date"><?php echo htmlentities($row->StaffRegdate); ?></td>
                                <td data-label="Status"><?php echo $row->Status == 1 ? 'Active' : 'Inactive'; ?></td>
                                <td data-label="Action">
                                  <!-- Edit button opens edit modal and passes data via data- attributes -->
                                  <button type="button"
                                    class="btn btn-xs btn-edit"
                                    data-id="<?php echo htmlentities($row->ID); ?>"
                                    data-name="<?php echo htmlentities($row->StaffName); ?>"
                                    data-username="<?php echo htmlentities($row->UserName); ?>"
                                    data-email="<?php echo htmlentities($row->Email); ?>"
                                    data-regdate="<?php echo htmlentities($row->StaffRegdate); ?>"
                                    style="background-color: #4CAF50; color: white;">Edit</button>

                                  <a href="manage-staff.php?statusid=<?php echo htmlentities($row->ID); ?>&status=<?php echo htmlentities($row->Status); ?>"
                                    class="btn btn-xs ml-2" style="background-color: #007BFF; color: white;">
                                    <?php echo $row->Status == 1 ? 'Deactivate' : 'Activate'; ?>
                                  </a>
                                </td>
                              </tr>
                              <?php $cnt++;
                            }
                          } else { ?>
                            <tr>
                              <td colspan="7" style="text-align: center; color: red;">No Record Found</td>
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
      // Display toast notification for status updates
      if (typeof statusMessage !== 'undefined' && statusMessage) {
        toastr.success(statusMessage);
      }

      // Show add staff messages
      <?php if (!empty($add_success_message)): ?>
        toastr.success("<?php echo addslashes($add_success_message); ?>");
      <?php endif; ?>
      <?php if (!empty($add_error_message)): ?>
        toastr.error("<?php echo addslashes($add_error_message); ?>");
      <?php endif; ?>

      // Show edit messages
      <?php if (!empty($edit_success_message)): ?>
        toastr.success("<?php echo addslashes($edit_success_message); ?>");
      <?php endif; ?>
      <?php if (!empty($edit_error_message)): ?>
        toastr.error("<?php echo addslashes($edit_error_message); ?>");
      <?php endif; ?>

      // If there was an error adding staff, open the add modal
      <?php if ($openAddModal): ?>
        window.addEventListener('DOMContentLoaded', function () {
          if (window.$) { $('#addStaffModal').modal('show'); }
          else if (typeof bootstrap !== "undefined") {
            var modalEl = document.getElementById('addStaffModal');
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
          }
        });
      <?php endif; ?>

      // If edit failed validation, reopen edit modal and populate with posted values
      <?php if ($openEditModal && !empty($_POST)): ?>
        window.addEventListener('DOMContentLoaded', function () {
          var modalEl = document.getElementById('editStaffModal');
          if (modalEl && typeof bootstrap !== "undefined") {
            document.getElementById('edit_id').value = "<?php echo addslashes($_POST['edit_id'] ?? ''); ?>";
            document.getElementById('edit_name').value = "<?php echo addslashes($_POST['edit_name'] ?? ''); ?>";
            document.getElementById('edit_username').value = "<?php echo addslashes($_POST['edit_username'] ?? ''); ?>";
            document.getElementById('edit_email').value = "<?php echo addslashes($_POST['edit_email'] ?? ''); ?>";
            document.getElementById('edit_regdate').value = "<?php echo addslashes($_POST['edit_regdate'] ?? ''); ?>";
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
          }
        });
      <?php endif; ?>

      // Toggle password visibility for add modal
      (function () {
        var toggle = document.getElementById('toggleAddPassword');
        var pwd = document.getElementById('add_password');
        if (!toggle || !pwd) return;
        toggle.addEventListener('click', function () {
          if (pwd.type === 'password') {
            pwd.type = 'text';
            toggle.classList.add('active');
          } else {
            pwd.type = 'password';
            toggle.classList.remove('active');
          }
        });
      })();

      // Toggle password visibility for edit modal
      (function () {
        var toggle = document.getElementById('toggleEditPassword');
        var pwd = document.getElementById('edit_password');
        if (!toggle || !pwd) return;
        toggle.addEventListener('click', function () {
          if (pwd.type === 'password') {
            pwd.type = 'text';
            toggle.classList.add('active');
          } else {
            pwd.type = 'password';
            toggle.classList.remove('active');
          }
        });
      })();

      // Populate edit modal when edit button clicked
      document.addEventListener('DOMContentLoaded', function () {
        var editButtons = document.querySelectorAll('.btn-edit');
        editButtons.forEach(function (btn) {
          btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            var name = this.getAttribute('data-name');
            var username = this.getAttribute('data-username');
            var email = this.getAttribute('data-email');
            var regdate = this.getAttribute('data-regdate');

            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_regdate').value = regdate;
            document.getElementById('edit_password').value = '';

            if (window.$) {
              $('#editStaffModal').modal('show');
            } else if (typeof bootstrap !== "undefined") {
              var modalEl = document.getElementById('editStaffModal');
              var modal = new bootstrap.Modal(modalEl);
              modal.show();
            }
          });
        });
      });
    </script>
  </body>

  </html>
<?php } ?>