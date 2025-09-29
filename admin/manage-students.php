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
    $sql = "UPDATE tblstudent SET Status=:newStatus WHERE ID=:sid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':newStatus', $newStatus, PDO::PARAM_INT);
    $query->bindParam(':sid', $sid, PDO::PARAM_INT);
    $query->execute();
    $statusMessage = $newStatus == 1 ? 'Student activated successfully.' : 'Student deactivated successfully.';
    echo "<script>var statusMessage = '$statusMessage';</script>";
  }

  // Search and filter functionality (support GET or POST so pagination links work)
  $searchdata = '';
  $filter = 'all';
  if (isset($_REQUEST['searchdata'])) {
    $searchdata = trim($_REQUEST['searchdata']);
  }
  if (isset($_REQUEST['filter'])) {
    $filter = $_REQUEST['filter'];
  }

  // Pagination setup
  $limit = 10; // rows per page
  $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
  $offset = ($page - 1) * $limit;
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <title>Student Management System || Manage Students</title>
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
              <h3 class="page-title">Manage Students</h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Manage Students</li>
                </ol>
              </nav>
            </div>
            <div class="row">
              <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="d-sm-flex align-items-center mb-4">
                      <h4 class="card-title mb-sm-0">Manage Students</h4>
                      <form method="post" class="ml-auto">
                        <input type="text" name="searchdata" class="form-control"
                          placeholder="Search by ID, Name, or Email" value="<?php echo htmlentities($searchdata); ?>"
                          style="display: inline-block; width: auto;">
                        <select name="filter" class="form-control" style="display: inline-block; width: auto;">
                          <option value="all" <?php if ($filter == 'all')
                            echo 'selected'; ?>>All</option>
                          <option value="active" <?php if ($filter == 'active')
                            echo 'selected'; ?>>Active</option>
                          <option value="inactive" <?php if ($filter == 'inactive')
                            echo 'selected'; ?>>Inactive</option>
                        </select>
                        <button type="submit" name="search" class="btn btn-primary">Search</button>
                        <a href="import-file.php" class="btn" style="background-color: #007BFF; color: white;">Import</a>
                      </form>
                    </div>
                    <div class="table-responsive border rounded p-1">
                      <table class="table">
                        <thead>
                          <tr>
                            <th class="font-weight-bold">S.No</th>
                            <th class="font-weight-bold">Student ID</th>
                            <th class="font-weight-bold">Family Name</th>
                            <th class="font-weight-bold">First Name</th>
                            <th class="font-weight-bold">Program</th>
                            <th class="font-weight-bold">Gender</th>
                            <th class="font-weight-bold">Contact Number</th>
                            <th class="font-weight-bold">Email Address</th>
                            <th class="font-weight-bold">Status</th>
                            <th class="font-weight-bold">Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          // Build WHERE clause and parameters
                          $where = " WHERE 1=1";
                          $params = [];
                          if (!empty($searchdata)) {
                            $where .= " AND (StuID LIKE :searchdata OR FamilyName LIKE :searchdata OR FirstName LIKE :searchdata OR EmailAddress LIKE :searchdata)";
                            $params[':searchdata'] = '%' . $searchdata . '%';
                          }
                          if ($filter == 'active') {
                            $where .= " AND Status=1";
                          } elseif ($filter == 'inactive') {
                            $where .= " AND Status=0";
                          }

                          // Get total rows for pagination
                          $countSql = "SELECT COUNT(*) FROM tblstudent" . $where;
                          $countQuery = $dbh->prepare($countSql);
                          foreach ($params as $k => $v) {
                            $countQuery->bindValue($k, $v, PDO::PARAM_STR);
                          }
                          $countQuery->execute();
                          $totalRows = (int) $countQuery->fetchColumn();
                          $totalPages = $totalRows > 0 ? ceil($totalRows / $limit) : 1;

                          // Fetch page rows with limit/offset
                          $sql = "SELECT ID as sid, StuID, FamilyName, FirstName, Program, Gender, ContactNumber, EmailAddress, Status FROM tblstudent" . $where . " ORDER BY ID DESC LIMIT :limit OFFSET :offset";
                          $query = $dbh->prepare($sql);
                          foreach ($params as $k => $v) {
                            $query->bindValue($k, $v, PDO::PARAM_STR);
                          }
                          $query->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
                          $query->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
                          $query->execute();
                          $results = $query->fetchAll(PDO::FETCH_OBJ);
                          $cnt = 1;
                          if ($query->rowCount() > 0) {
                            foreach ($results as $row) { ?>
                              <tr>
                                <td><?php echo htmlentities($cnt); ?></td>
                                <td><?php echo htmlentities($row->StuID); ?></td>
                                <td><?php echo htmlentities($row->FamilyName); ?></td>
                                <td><?php echo htmlentities($row->FirstName); ?></td>
                                <td><?php echo htmlentities($row->Program); ?></td>
                                <td><?php echo htmlentities($row->Gender); ?></td>
                                <td><?php echo htmlentities($row->ContactNumber); ?></td>
                                <td><?php echo htmlentities($row->EmailAddress); ?></td>
                                <td><?php echo $row->Status == 1 ? 'Active' : 'Inactive'; ?></td>
                                <td>
                                  <a href="edit-student-detail.php?editid=<?php echo htmlentities($row->sid); ?>"
                                    class="btn btn-xs" style="background-color: #4CAF50; color: white;">Edit</a>
                                  <a href="manage-students.php?statusid=<?php echo htmlentities($row->sid); ?>&status=<?php echo htmlentities($row->Status); ?>"
                                    class="btn btn-xs" style="background-color: #007BFF; color: white;">
                                    <?php echo $row->Status == 1 ? 'Deactivate' : 'Activate'; ?>
                                  </a>
                                  <!-- Validate Achievements moved to sidebar -->
                                </td>
                              </tr>
                              <?php $cnt++;
                            }
                          } else { ?>
                            <tr>
                              <td colspan="10" style="text-align: center; color: red;">No Record Found</td>
                            </tr>
                          <?php } ?>
                        </tbody>
                      </table>
                    </div>
                      <!-- Pagination controls -->
                      <nav aria-label="Page navigation" class="mt-3">
                        <ul class="pagination">
                          <?php
                          // Build base URL with preserved params
                          $baseParams = [];
                          if (!empty($searchdata)) $baseParams['searchdata'] = $searchdata;
                          if (!empty($filter) && $filter !== 'all') $baseParams['filter'] = $filter;

                          $buildUrl = function ($p) use ($baseParams) {
                            $params = $baseParams;
                            $params['page'] = $p;
                            return 'manage-students.php?' . http_build_query($params);
                          };

                          // First
                          $firstDisabled = $page <= 1 ? ' disabled' : '';
                          echo '<li class="page-item' . $firstDisabled . '"><a class="page-link" href="' . ($page <= 1 ? '#' : $buildUrl(1)) . '">First</a></li>';
                          // Prev
                          $prevPage = max(1, $page - 1);
                          $prevDisabled = $page <= 1 ? ' disabled' : '';
                          echo '<li class="page-item' . $prevDisabled . '"><a class="page-link" href="' . ($page <= 1 ? '#' : $buildUrl($prevPage)) . '">Prev</a></li>';
                          // Next
                          $nextPage = min($totalPages, $page + 1);
                          $nextDisabled = $page >= $totalPages ? ' disabled' : '';
                          echo '<li class="page-item' . $nextDisabled . '"><a class="page-link" href="' . ($page >= $totalPages ? '#' : $buildUrl($nextPage)) . '">Next</a></li>';
                          // Last
                          $lastDisabled = $page >= $totalPages ? ' disabled' : '';
                          echo '<li class="page-item' . $lastDisabled . '"><a class="page-link" href="' . ($page >= $totalPages ? '#' : $buildUrl($totalPages)) . '">Last</a></li>';
                          ?>
                        </ul>
                      </nav>
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