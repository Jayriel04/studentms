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
    echo "<script>var statusMessage = '" . addslashes($statusMessage) . "';</script>";
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

  // Helper function to get initials from a name
  function getInitials($name)
  {
    $words = explode(' ', trim($name));
    $initials = '';
    if (count($words) >= 2) {
      $initials .= strtoupper(substr($words[0], 0, 1));
      $initials .= strtoupper(substr(end($words), 0, 1));
    } else if (count($words) == 1) {
      $initials .= strtoupper(substr($words[0], 0, 2));
    }
    return $initials;
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
    <link rel="stylesheet" href="./css/modal.css">
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
              <button type="button" class="add-btn" data-target="#addStaffModal">
                + Add New Staff
              </button>
            </div>
            <div class="row">
              <div class="col-md-12 grid-margin stretch-card">
                <div class="table-card">
                  <div class="table-header">
                    <h2 class="table-title">Manage Staff</h2>
                    <div class="table-actions">
                      <form method="post" class="d-flex" style="gap: 12px;">
                        <input type="text" name="searchdata" class="search-box" placeholder="Search by Name or Email"
                          value="<?php echo htmlentities($searchdata); ?>">
                        <select name="filter" class="filter-btn" onchange="this.form.submit()">
                          <option value="all" <?php if ($filter == 'all')
                            echo 'selected'; ?>>All</option>
                          <option value="active" <?php if ($filter == 'active')
                            echo 'selected'; ?>>Active</option>
                          <option value="inactive" <?php if ($filter == 'inactive')
                            echo 'selected'; ?>>Inactive</option>
                        </select>
                        <button type="submit" name="search" class="filter-btn">🔍 Search</button>
                      </form>
                    </div>
                  </div>

                  <!-- Add Staff Modal -->
                  <div class="new-modal-overlay" id="addStaffModalOverlay"> <div class="new-modal">
                      <div class="new-modal-header">
                          <h2 class="new-modal-title">Add New Staff</h2>
                          <button type="button" class="new-close-btn">&times;</button>
                      </div>
                      <form method="post" id="addStaffForm">
                          <div class="new-form-group">
                              <label for="staffname" class="new-form-label">Staff Name</label>
                              <input type="text" name="staffname" class="new-form-input" required='true'
                                  style="text-transform: capitalize;" placeholder="Enter staff name">
                          </div>
                          <div class="new-form-group">
                              <label for="username" class="new-form-label">User Name</label>
                              <input type="text" name="username" class="new-form-input" required='true' placeholder="Enter username">
                          </div>
                          <div class="new-form-group">
                              <label for="email" class="new-form-label">Email</label>
                              <input type="email" name="email" class="new-form-input" required='true' placeholder="example@email.com">
                          </div>
                          <div class="new-form-group">
                              <label for="password" class="new-form-label">Password</label>
                              <div class="new-form-input-wrapper">
                                  <input type="password" id="add_password" name="password" class="new-form-input"
                                      required='true' placeholder="Enter password">
                                  <i class="icon-eye" id="toggleAddPassword"></i>
                              </div>
                          </div>

                          <div class="new-modal-footer">
                              <button type="button" class="new-btn new-btn-cancel">Cancel</button>
                              <button type="submit" class="new-btn new-btn-submit" name="add_staff">Add Staff</button>
                          </div>
                      </form>
                  </div> </div>
                  <!-- Edit Staff Modal -->
                  <div class="new-modal-overlay" id="editStaffModalOverlay">
                    <div class="new-modal">
                        <div class="new-modal-header">
                            <h2 class="new-modal-title">Edit Staff</h2>
                            <button type="button" class="new-close-btn">&times;</button>
                        </div>
                        <form method="post" id="editStaffForm">
                          <div class="new-modal-body">
                            <input type="hidden" name="edit_id" id="edit_id">
                            <div class="new-form-group">
                              <label for="edit_name" class="new-form-label">Staff Name</label>
                              <input type="text" name="edit_name" id="edit_name" class="new-form-input" required
                                style="text-transform: capitalize;">
                            </div>
                            <div class="new-form-group">
                              <label for="edit_username" class="new-form-label">User Name</label>
                              <input type="text" name="edit_username" id="edit_username" class="new-form-input" required>
                            </div>
                            <div class="new-form-group">
                              <label for="edit_email" class="new-form-label">Email</label>
                              <input type="email" name="edit_email" id="edit_email" class="new-form-input" required>
                            </div>
                            <div class="new-form-group">
                              <label for="edit_password" class="new-form-label">Change Password</label>
                              <div class="new-form-input-wrapper">
                              <input type="password" id="edit_password" name="edit_password" class="new-form-input"
                                placeholder="Leave blank to keep unchanged">
                                <i class="icon-eye" id="toggleEditPassword"></i>
                              </div>
                            </div>
                            <div class="new-form-group">
                              <label for="edit_regdate" class="new-form-label">Staff Regdate</label>
                              <input type="text" id="edit_regdate" class="new-form-input" readonly>
                            </div>
                          </div>
                          <div class="new-modal-footer">
                            <button type="button" class="new-btn new-btn-cancel">Cancel</button>
                            <button type="submit" class="new-btn new-btn-submit" name="edit_staff">Save Changes</button>
                          </div>
                        </form>
                    </div>
                  </div>

                  <div class="table-wrapper">
                    <table class="table">
                      <thead>
                        <tr>
                          <th>Staff Member</th>
                          <th>Username</th>
                          <th>Registration Date</th>
                          <th>Status</th>
                          <th>Action</th>
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
                              <td>
                                <div class="user-info">
                                  <div class="user-avatar"
                                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <?php echo getInitials($row->StaffName); ?>
                                  </div>
                                  <div class="user-details">
                                    <span class="user-name"><?php echo htmlentities($row->StaffName); ?></span>
                                    <span class="user-email"><?php echo htmlentities($row->Email); ?></span>
                                  </div>
                                </div>
                              </td>
                              <td><?php echo htmlentities($row->UserName); ?></td>
                              <td><?php echo date('M d, Y', strtotime($row->StaffRegdate)); ?></td>
                              <td>
                                <span class="status-badge <?php echo $row->Status == 1 ? 'active' : 'inactive'; ?>">
                                  <?php echo $row->Status == 1 ? 'Active' : 'Inactive'; ?>
                                </span>
                              </td>
                              <td>
                                <div class="action-buttons">
                                  <button class="action-btn edit btn-edit" title="Edit"
                                    data-id="<?php echo htmlentities($row->ID); ?>"
                                    data-name="<?php echo htmlentities($row->StaffName); ?>"
                                    data-username="<?php echo htmlentities($row->UserName); ?>"
                                    data-email="<?php echo htmlentities($row->Email); ?>"
                                    data-regdate="<?php echo htmlentities($row->StaffRegdate); ?>">✏️</button>
                                  <a href="manage-staff.php?statusid=<?php echo htmlentities($row->ID); ?>&status=<?php echo htmlentities($row->Status); ?>"
                                    class="action-btn toggle <?php echo $row->Status == 1 ? 'deactivate' : ''; ?>"
                                    title="<?php echo $row->Status == 1 ? 'Deactivate' : 'Activate'; ?>">
                                    <?php echo $row->Status == 1 ? '🔒' : '🔑'; ?>
                                  </a>
                                </div>
                              </td>
                            </tr>
                            <?php $cnt++;
                          }
                        } else { ?>
                          <tr>
                            <td colspan="5" style="text-align: center; color: red;">No Record Found</td>
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
    <script src="js/script.js"></script>
    <script src="js/manage-staff.js"></script>
    <script src="js/toast.js"></script>

    <!-- Inline initialization data for external script -->
    <script>
      window.msData = {
        statusMessage: <?php echo isset($statusMessage) ? json_encode($statusMessage) : 'null'; ?>,
        add_success_message: <?php echo json_encode($add_success_message); ?>,
        add_error_message: <?php echo json_encode($add_error_message); ?>,
        edit_success_message: <?php echo json_encode($edit_success_message); ?>,
        edit_error_message: <?php echo json_encode($edit_error_message); ?>,
        openAddModal: <?php echo $openAddModal ? 'true' : 'false'; ?>,
        openEditModal: <?php echo $openEditModal ? 'true' : 'false'; ?>,
        editPost: {
          id: <?php echo json_encode($_POST['edit_id'] ?? ''); ?>,
          name: <?php echo json_encode($_POST['edit_name'] ?? ''); ?>,
          username: <?php echo json_encode($_POST['edit_username'] ?? ''); ?>,
          email: <?php echo json_encode($_POST['edit_email'] ?? ''); ?>,
          regdate: <?php echo json_encode($_POST['edit_regdate'] ?? ''); ?>
        }
      };
    </script>

  </body>

  </html>
<?php } ?>