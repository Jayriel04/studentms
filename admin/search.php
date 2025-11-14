<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid']) == 0) { // Ensure admin session is checked
  header('location:logout.php');
} else {
  $q = isset($_GET['q']) ? trim($_GET['q']) : '';

  // AJAX suggestions endpoint: returns JSON list of students matching term
  if (isset($_GET['suggest'])) {
    header('Content-Type: application/json');
    $term = isset($_GET['term']) ? trim($_GET['term']) : '';
    try {
      if ($term === '') {
        $stmt = $dbh->prepare("SELECT StuID, FamilyName, FirstName FROM tblstudent ORDER BY ID DESC LIMIT 10");
      } else {
        $stmt = $dbh->prepare("SELECT StuID, FamilyName, FirstName FROM tblstudent WHERE StuID LIKE :t OR FamilyName LIKE :t OR FirstName LIKE :t ORDER BY ID DESC LIMIT 10");
        $like = $term . '%';
        $stmt->bindValue(':t', $like, PDO::PARAM_STR);
      }
      $stmt->execute();
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      echo json_encode($rows);
    } catch (Exception $e) {
      echo json_encode([]);
    }
    exit;
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <title>Student Profiling System | Search Students</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="./vendors/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="./vendors/chartist/chartist.min.css">
    <link rel="stylesheet" href="./css/style.css">
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
                    <div class="responsive-search-form">
                      <form method="get" id="searchForm" class="form-inline">
                        <div class="form-group" style="flex: 1;">
                          <label for="searchdata" class="sr-only">Search</label>
                          <input id="searchdata" type="text" name="searchdata" class="form-control w-100" autocomplete="off"
                            placeholder="Search by Student ID, Name, or Skill"
                            value="<?php echo isset($_GET['searchdata']) ? htmlentities($_GET['searchdata']) : ''; ?>">
                          <div id="suggestions" class="list-group" style="position:relative; z-index:1000;"></div>
                        </div>
                        <button type="submit" class="btn btn-primary ml-2" id="submit">Search</button>
                      </form>
                    </div>
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
                    <div class="table-responsive border rounded p-1 card-view">
                      <table class="table">
                        <thead>
                          <tr>
                            <th class="font-weight-bold">S.No</th>
                            <th class="font-weight-bold">Student ID</th>
                            <th class="font-weight-bold">Family Name</th>
                            <th class="font-weight-bold">First Name</th>
                            <th class="font-weight-bold">Program</th>
                            <th class="font-weight-bold">Gender</th>
                            <th class="font-weight-bold">Email Address</th>
                            <th class="font-weight-bold">Status</th>
                            <?php if (isset($isSkillSearch) && $isSkillSearch) { ?>
                              <th class="font-weight-bold">Skill</th>
                            <?php } ?>
                            <th class="font-weight-bold">Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          $sdata = isset($_GET['searchdata']) ? trim($_GET['searchdata']) : '';
                          $pageno = isset($_GET['pageno']) ? max(1, intval($_GET['pageno'])) : 1;
                          $no_of_records_per_page = 5;
                          $offset = ($pageno - 1) * $no_of_records_per_page;

                          if ($isSkillSearch && $skill_id) {
                            $countSql = "SELECT COUNT(DISTINCT t.ID) FROM tblstudent t
                     JOIN student_achievements sa ON sa.StuID = t.StuID AND sa.status='approved'
                     JOIN student_achievement_skills ssk ON ssk.achievement_id = sa.id
                     WHERE ssk.skill_id = :skill_id";
                            $countStmt = $dbh->prepare($countSql);
                            $countStmt->bindValue(':skill_id', $skill_id, PDO::PARAM_INT);
                            $countStmt->execute();
                            $total_rows = $countStmt->fetchColumn();
                            $total_pages = ($total_rows > 0) ? ceil($total_rows / $no_of_records_per_page) : 1;
                            $dataSql = "SELECT t.ID as sid, t.StuID, t.FamilyName, t.FirstName, t.Program, t.Gender, t.EmailAddress, t.Status,
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
                                  <td data-label="S.No"><?php echo htmlentities($cnt); ?></td>
                                  <td data-label="Student ID"><?php echo htmlentities($row->StuID); ?></td>
                                  <td data-label="Family Name"><?php echo htmlentities($row->FamilyName); ?></td>
                                  <td data-label="First Name"><?php echo htmlentities($row->FirstName); ?></td>
                                  <td data-label="Program"><?php echo htmlentities($row->Program); ?></td>
                                  <td data-label="Gender"><?php echo htmlentities($row->Gender); ?></td>
                                  <td data-label="Email"><?php echo htmlentities($row->EmailAddress); ?></td>
                                  <td data-label="Status"><?php echo $row->Status == 1 ? 'Active' : 'Inactive'; ?></td>
                                  <td data-label="Skill"><?php echo isset($skill->name) ? htmlentities($skill->name) : ''; ?></td>
                                  <td data-label="Action">
                                    <div class="btn-group" role="group" aria-label="Actions" style="display: flex; gap: 0.5rem;">
                                      <a href="view-student-profile.php?sid=<?php echo urlencode($row->StuID); ?>"
                                        class="btn btn-info btn-xs">View</a>
                                      <a href="edit-student-detail.php?editid=<?php echo htmlentities($row->sid); ?>"
                                        class="btn btn-primary btn-xs">Edit</a>
                                      <a href="manage-students.php?statusid=<?php echo htmlentities($row->sid); ?>&status=<?php echo htmlentities($row->Status); ?>"
                                        class="btn btn-warning btn-xs">
                                        <?php echo (isset($row->Status) && $row->Status == 1) ? 'Deactivate' : 'Activate'; ?>
                                      </a>
                                    </div>
                                  </td>
                                </tr>
                                <?php $cnt++;
                              }
                            } else { ?>
                              <tr>
                                <td colspan="10" style="text-align: center; color: red;">No record found against this search
                                </td>
                              </tr>
                            <?php }
                          } else {
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

                            $sql = "SELECT ID as sid, StuID, FamilyName, FirstName, Program, Gender, EmailAddress, Status FROM tblstudent";
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
                                  <td data-label="S.No"><?php echo htmlentities($cnt); ?></td>
                                  <td data-label="Student ID"><?php echo htmlentities($row->StuID); ?></td>
                                  <td data-label="Family Name"><?php echo htmlentities($row->FamilyName); ?></td>
                                  <td data-label="First Name"><?php echo htmlentities($row->FirstName); ?></td>
                                  <td data-label="Program"><?php echo htmlentities($row->Program); ?></td>
                                  <td data-label="Gender"><?php echo htmlentities($row->Gender); ?></td>
                                  <td data-label="Email"><?php echo htmlentities($row->EmailAddress); ?></td>
                                  <td data-label="Status"><?php echo $row->Status == 1 ? 'Active' : 'Inactive'; ?></td>
                                  <td data-label="Action">
                                    <div class="btn-group" role="group" aria-label="Actions" style="display: flex; gap: 0.5rem;">
                                      <a href="view-student-profile.php?sid=<?php echo urlencode($row->StuID); ?>"
                                        class="btn btn-info btn-xs">View</a>
                                      <a href="edit-student-detail.php?editid=<?php echo htmlentities($row->sid); ?>"
                                        class="btn btn-primary btn-xs">Edit</a>
                                      <a href="manage-students.php?statusid=<?php echo htmlentities($row->sid); ?>&status=<?php echo htmlentities($row->Status); ?>"
                                        class="btn btn-warning btn-xs">
                                        <?php echo (isset($row->Status) && $row->Status == 1) ? 'Deactivate' : 'Activate'; ?>
                                      </a>
                                    </div>
                                  </td>
                                </tr>
                                <?php
                                $cnt++;
                              }
                            } else { ?>
                              <tr>
                                <td colspan="9" style="text-align: center; color: red;">No record found against this search
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
                              <a href="<?php if ($pageno <= 1) {
                                echo '#';
                              } else {
                                echo '?pageno=' . ($pageno - 1) . (($sdata !== '') ? '&searchdata=' . urlencode($sdata) : '');
                              } ?>"><strong style="padding-left: 10px">Prev</strong></a>
                            </li>
                            <li class="<?php if ($pageno >= $total_pages) {
                              echo 'disabled';
                            } ?>">
                              <a href="<?php if ($pageno >= $total_pages) {
                                echo '#';
                              } else {
                                echo '?pageno=' . ($pageno + 1) . (($sdata !== '') ? '&searchdata=' . urlencode($sdata) : '');
                              } ?>"><strong style="padding-left: 10px">Next</strong></a>
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
          <?php include_once('includes/footer.php'); ?>
        </div>
      </div>
    </div>
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="./vendors/chart.js/Chart.min.js"></script>
    <script src="./vendors/moment/moment.min.js"></script>
    <script src="./vendors/daterangepicker/daterangepicker.js"></script>
    <script src="./vendors/chartist/chartist.min.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script src="./js/dashboard.js"></script>
    <script>
      (function () {
        function debounce(fn, delay) { var t; return function () { var ctx = this, args = arguments; clearTimeout(t); t = setTimeout(function () { fn.apply(ctx, args); }, delay); }; }
        var $input = document.getElementById('searchdata');
        var $suggest = document.getElementById('suggestions');

        function render(rows) {
          $suggest.innerHTML = '';
          if (!rows || rows.length === 0) return;
          rows.forEach(function (r) {
            var item = document.createElement('a');
            item.href = '#';
            item.className = 'list-group-item list-group-item-action';
            item.textContent = r.StuID + ' — ' + r.FamilyName + ', ' + r.FirstName;
            item.dataset.stuid = r.StuID;
            item.addEventListener('click', function (e) { e.preventDefault(); $input.value = this.dataset.stuid; document.getElementById('searchForm').submit(); });
            $suggest.appendChild(item);
          });
        }

        var fetchSuggestions = debounce(function () {
          var q = $input.value.trim();
          var url = window.location.pathname + '?suggest=1&term=' + encodeURIComponent(q);
          fetch(url, { credentials: 'same-origin' }).then(function (res) { return res.json(); }).then(function (json) { render(json); }).catch(function () { render([]); });
        }, 200);

        $input.addEventListener('input', function () { fetchSuggestions(); });

        // On focus show default list
        $input.addEventListener('focus', function () { fetchSuggestions(); });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function (e) { if (!document.getElementById('suggestions').contains(e.target) && e.target !== $input) { $suggest.innerHTML = ''; } });
      })();
    </script>
  </body>

  </html>
<?php } ?>