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
    echo "<script>var statusMessage = '$statusMessage';</script>";
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
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="./css/style.css">
    <!-- Toastr CSS -->
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
                    <div class="d-sm-flex align-items-center mb-4">
                      <h4 class="card-title mb-sm-0">Manage Staff</h4>
                      <form method="post" class="ml-auto">
                        <input type="text" name="searchdata" class="form-control" placeholder="Search by Name or Email"
                          value="<?php echo htmlentities($searchdata); ?>" style="display: inline-block; width: auto;">
                        <select name="filter" class="form-control" style="display: inline-block; width: auto;">
                          <option value="all" <?php if ($filter == 'all')
                            echo 'selected'; ?>>All</option>
                          <option value="active" <?php if ($filter == 'active')
                            echo 'selected'; ?>>Active</option>
                          <option value="inactive" <?php if ($filter == 'inactive')
                            echo 'selected'; ?>>Inactive</option>
                        </select>
                        <button type="submit" name="search" class="btn btn-primary">Search</button>
                      </form>
                    </div>
                    <div class="table-responsive border rounded p-1">
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
                                <td><?php echo htmlentities($cnt); ?></td>
                                <td><?php echo htmlentities($row->StaffName); ?></td>
                                <td><?php echo htmlentities($row->UserName); ?></td>
                                <td><?php echo htmlentities($row->Email); ?></td>
                                <td><?php echo htmlentities($row->StaffRegdate); ?></td>
                                <td><?php echo $row->Status == 1 ? 'Active' : 'Inactive'; ?></td>
                                <td>
                                  <a href="edit-staff-detail.php?editid=<?php echo htmlentities($row->ID); ?>"
                                    class="btn btn-xs" style="background-color: #4CAF50; color: white;">Edit</a>
                                  <a href="manage-staff.php?statusid=<?php echo htmlentities($row->ID); ?>&status=<?php echo htmlentities($row->Status); ?>"
                                    class="btn btn-xs" style="background-color: #007BFF; color: white;">
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
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
      // Display toast notification for status updates
      if (typeof statusMessage !== 'undefined' && statusMessage) {
        toastr.success(statusMessage);
      }
    </script>
  </body>

  </html>
<?php } ?>