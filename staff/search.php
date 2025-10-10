<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsstaffid']) == 0) { // Ensure staff session is checked
  header('location:logout.php');
} else {
  $q = isset($_GET['q']) ? trim($_GET['q']) : '';
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <title>Staff Profiling System || Search Students</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="./vendors/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="./vendors/chartist/chartist.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="./css/style.css">
    <!-- End layout styles -->
  </head>

  <body>
    <div class="container-scroller">
      <!-- partial:partials/_navbar.html -->
      <?php include_once('includes/header.php'); ?>
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        <?php include_once('includes/sidebar.php'); ?>
        <!-- partial -->
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="page-header">
              <h3 class="page-title">Search Students</h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Search Students</li>
                </ol>
              </nav>
            </div>
            <div class="row">
              <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <form method="get">
                      <div class="form-group">
                        <label><strong>Search</strong></label>
                        <input id="searchdata" type="text" name="searchdata" class="form-control"
                          placeholder="Search by Student ID, Family Name, or First Name"
                          value="<?php echo isset($_GET['searchdata']) ? htmlentities($_GET['searchdata']) : ''; ?>">
                      </div>
                      <button type="submit" class="btn btn-primary" id="submit">Search</button>
                    </form>
                    <div class="d-sm-flex align-items-center mb-4">
                      <?php
                      $isSkillSearch = false;
                      $skill_id = 0;
                      if (isset($_GET['searchdata'])) {
                        $sdata = trim($_GET['searchdata']);
                        // Detect if the search term matches an existing skill (case-insensitive exact match)
                        $skillStmt = $dbh->prepare("SELECT id, name FROM skills WHERE LOWER(name) = LOWER(:s) LIMIT 1");
                        $skillStmt->bindValue(':s', $sdata, PDO::PARAM_STR);
                        $skillStmt->execute();
                        $skill = $skillStmt->fetch(PDO::FETCH_OBJ);
                        if ($skill) {
                          $isSkillSearch = true;
                          $skill_id = $skill->id;
                        }
                        ?>
                        <hr />
                        <h4 align="center">Results for
                          "<?php echo htmlentities($sdata); ?>"<?php echo ($isSkillSearch) ? ' (skill search - ranked by points)' : ''; ?>
                        </h4>
                      <?php } ?>
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
                            <?php if (isset($isSkillSearch) && $isSkillSearch) { ?>
                              <th class="font-weight-bold">Total Points</th>
                            <?php } ?>
                            <th class="font-weight-bold">Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          // Admin-style search logic (preserving search via GET), 5 results per page
                          $sdata = isset($_GET['searchdata']) ? trim($_GET['searchdata']) : '';
                          $pageno = isset($_GET['pageno']) ? max(1, intval($_GET['pageno'])) : 1;
                          $no_of_records_per_page = 5;
                          $offset = ($pageno - 1) * $no_of_records_per_page;

                          if ($isSkillSearch && $skill_id) {
                            // Skill-based ranked search: sum approved points per student for the given skill
                            $countSql = "SELECT COUNT(DISTINCT t.ID) FROM tblstudent t
                     JOIN student_achievements sa ON sa.StuID = t.StuID AND sa.status='approved'
                     JOIN student_achievement_skills ssk ON ssk.achievement_id = sa.id
                     WHERE ssk.skill_id = :skill_id";
                            $countStmt = $dbh->prepare($countSql);
                            $countStmt->bindValue(':skill_id', $skill_id, PDO::PARAM_INT);
                            $countStmt->execute();
                            $total_rows = $countStmt->fetchColumn();
                            $total_pages = ($total_rows > 0) ? ceil($total_rows / $no_of_records_per_page) : 1;

                            $dataSql = "SELECT t.ID as sid, t.StuID, t.FamilyName, t.FirstName, t.Program, t.Gender, t.ContactNumber, t.EmailAddress, t.Status,
                    IFNULL(SUM(sa.points),0) as totalPoints
                    FROM tblstudent t
                    JOIN student_achievements sa ON sa.StuID = t.StuID AND sa.status='approved'
                    JOIN student_achievement_skills ssk ON ssk.achievement_id = sa.id
                    WHERE ssk.skill_id = :skill_id
                    GROUP BY t.ID
                    ORDER BY totalPoints DESC, t.ID DESC
                    LIMIT :offset, :limit";
                            $query = $dbh->prepare($dataSql);
                            $query->bindValue(':skill_id', $skill_id, PDO::PARAM_INT);
                            $query->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
                            $query->bindValue(':limit', (int) $no_of_records_per_page, PDO::PARAM_INT);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                            $cnt = 1 + $offset;
                            if (count($results) > 0) {
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
                                  <td><?php echo isset($row->totalPoints) ? htmlentities($row->totalPoints) : '0'; ?></td>
                                  <td>
                                    <div>
                                      <a href="view-student.php?viewid=<?php echo htmlentities($row->sid); ?>"
                                        class="btn btn-success btn-xs">View</a>
                                      <a href="edit-student-detail.php?editid=<?php echo htmlentities($row->sid); ?>"
                                        class="btn btn-info btn-xs">Edit</a>
                                      <a href="manage-students.php?statusid=<?php echo htmlentities($row->sid); ?>&status=<?php echo htmlentities($row->Status); ?>"
                                        class="btn btn-warning btn-xs"><?php echo $row->Status == 1 ? 'Deactivate' : 'Activate'; ?></a>
                                      <!-- Validate Achievements moved to sidebar -->
                                    </div>
                                  </td>
                                </tr>
                                <?php $cnt++;
                              }
                            } else { ?>
                              <tr>
                                <td colspan="8" style="text-align: center; color: red;">No record found against this search
                                </td>
                              </tr>
                            <?php }
                          } else {
                            // Regular student search (as before)
                            // Total rows
                            $countSql = "SELECT COUNT(ID) FROM tblstudent";
                            if ($sdata !== '') {
                              $countSql .= " WHERE StuID LIKE :sdata OR FamilyName LIKE :sdata OR FirstName LIKE :sdata";
                            }
                            $countStmt = $dbh->prepare($countSql);
                            if ($sdata !== '') {
                              $countStmt->bindValue(':sdata', '%' . $sdata . '%', PDO::PARAM_STR);
                            }
                            $countStmt->execute();
                            $total_rows = $countStmt->fetchColumn();
                            $total_pages = ($total_rows > 0) ? ceil($total_rows / $no_of_records_per_page) : 1;

                            // Fetch results for current page
                            $sql = "SELECT ID as sid, StuID, FamilyName, FirstName, Program, Gender, ContactNumber, EmailAddress, Status FROM tblstudent";
                            if ($sdata !== '') {
                              $sql .= " WHERE StuID LIKE :sdata OR FamilyName LIKE :sdata OR FirstName LIKE :sdata";
                            }
                            $sql .= " ORDER BY ID DESC LIMIT :offset, :limit";
                            $query = $dbh->prepare($sql);
                            if ($sdata !== '') {
                              $query->bindValue(':sdata', '%' . $sdata . '%', PDO::PARAM_STR);
                            }
                            $query->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
                            $query->bindValue(':limit', (int) $no_of_records_per_page, PDO::PARAM_INT);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                            $cnt = 1 + $offset;
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
                                    <div>
                                      <a href="view-student.php?viewid=<?php echo htmlentities($row->sid); ?>"
                                        class="btn btn-success btn-xs">View</a>
                                      <a href="edit-student-detail.php?editid=<?php echo htmlentities($row->sid); ?>"
                                        class="btn btn-info btn-xs">Edit</a>
                                      <a href="manage-students.php?statusid=<?php echo htmlentities($row->sid); ?>&status=<?php echo htmlentities($row->Status); ?>"
                                        class="btn btn-warning btn-xs"><?php echo $row->Status == 1 ? 'Deactivate' : 'Activate'; ?></a>
                                      <!-- Validate Achievements moved to sidebar -->
                                    </div>
                                  </td>
                                </tr>
                                <?php
                                $cnt++;
                              }
                            } else { ?>
                              <tr>
                                <td colspan="8" style="text-align: center; color: red;">No record found against this search
                                </td>
                              </tr>
                            <?php }
                          } ?>
                        </tbody>
                      </table>
                      <!-- Pagination controls -->
                      <?php if (isset($total_pages) && $total_pages > 1) { ?>
                        <div align="left" class="mt-4">
                          <ul class="pagination">
                            <li><a
                                href="?pageno=1<?php echo ($sdata !== '') ? '&searchdata=' . urlencode($sdata) : ''; ?>"><strong>First</strong></a>
                            </li>
                            <li class="<?php if ($pageno <= 1) {
                              echo 'disabled';
                            } ?>">
                              <a
                                href="<?php if ($pageno <= 1) {
                                  echo '#';
                                } else {
                                  echo '?pageno=' . ($pageno - 1) . (($sdata !== '') ? '&searchdata=' . urlencode($sdata) : '');
                                } ?>"><strong
                                  style="padding-left: 10px">Prev</strong></a>
                            </li>
                            <li class="<?php if ($pageno >= $total_pages) {
                              echo 'disabled';
                            } ?>">
                              <a
                                href="<?php if ($pageno >= $total_pages) {
                                  echo '#';
                                } else {
                                  echo '?pageno=' . ($pageno + 1) . (($sdata !== '') ? '&searchdata=' . urlencode($sdata) : '');
                                } ?>"><strong
                                  style="padding-left: 10px">Next</strong></a>
                            </li>
                            <li><a
                                href="?pageno=<?php echo $total_pages; ?><?php echo ($sdata !== '') ? '&searchdata=' . urlencode($sdata) : ''; ?>"><strong
                                  style="padding-left: 10px">Last</strong></a></li>
                          </ul>
                        </div>
                      <?php } ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- content-wrapper ends -->
          <!-- partial:partials/_footer.html -->
          <?php include_once('includes/footer.php'); ?>
          <!-- partial -->
        </div>
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="./vendors/chart.js/Chart.min.js"></script>
    <script src="./vendors/moment/moment.min.js"></script>
    <script src="./vendors/daterangepicker/daterangepicker.js"></script>
    <script src="./vendors/chartist/chartist.min.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <script src="./js/dashboard.js"></script>
    <!-- End custom js for this page -->
  </body>

  </html>
<?php } ?>