<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsstaffid']) == 0) {
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
    $searchdata = isset($_REQUEST['searchdata']) ? trim($_REQUEST['searchdata']) : '';
    $filter = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : 'all';

    // Pagination setup
    $limit = 5; // rows per page
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $limit;

    // Detect if the search term is a skill
    $isSkillSearch = false;
    $skill_id = 0;
    $skill_name = '';
    if (!empty($searchdata)) {
        $skillStmt = $dbh->prepare("SELECT id, name FROM skills WHERE LOWER(name) = LOWER(:s) LIMIT 1");
        $skillStmt->bindValue(':s', $searchdata, PDO::PARAM_STR);
        $skillStmt->execute();
        $skill = $skillStmt->fetch(PDO::FETCH_OBJ);
        if ($skill) {
            $isSkillSearch = true;
            $skill_id = $skill->id;
            $skill_name = $skill->name;
        }
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
        <title>Student Profiling System || Manage Students</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
        <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
        <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
        <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
        <link rel="stylesheet" href="./css/style.css">
        <link rel="stylesheet" href="./css/style(v2).css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <link rel="stylesheet" href="css/responsive.css">
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
                            <div class="d-flex">
                                <a href="validate-achievements.php" class="add-btn" style="text-decoration: none; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); margin-right: 10px;">✓ Validate</a>
                                <a href="add-students.php" class="add-btn" style="text-decoration: none; margin-right: 20px;">+ Add Student</a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 grid-margin stretch-card">
                                <div class="table-card">
                                    <div class="table-header">
                                        <h2 class="table-title">Student List</h2>
                                        <div class="table-actions">
                                            <form method="get" class="d-flex" style="gap: 12px;">
                                                <input type="text" name="searchdata" class="search-box"
                                                    placeholder="Search by ID, Name, or Skill"
                                                    value="<?php echo htmlentities($searchdata); ?>">
                                                <select name="filter" class="filter-btn" onchange="this.form.submit()">
                                                    <option value="all" <?php if ($filter == 'all')
                                                        echo 'selected'; ?>>All
                                                    </option>
                                                    <option value="active" <?php if ($filter == 'active')
                                                        echo 'selected'; ?>>
                                                        Active</option>
                                                    <option value="inactive" <?php if ($filter == 'inactive')
                                                      echo 'selected'; ?>>Inactive</option>
                                                </select>
                                                <button type="submit" class="filter-btn">🔍 Search</button>
                                            </form>
                                            <a href="import-file.php" class="add-btn" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); text-decoration: none;">
                                              <span style="font-size: 18px; margin-top: -2px;">📥</span> Import
                                            </a>
                                        </div>
                                    </div>
                                        <div class="table-wrapper">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Student</th>
                                                        <th>Student ID</th>
                                                        <th>Program</th>
                                                        <th>Status</th>
                                                        <?php if ($isSkillSearch): ?>
                                                            <th class="font-weight-bold">Skill Points</th>
                                                        <?php endif; ?>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $sql = "SELECT ID AS sid, StuID, FamilyName, FirstName, Program, Gender, EmailAddress, Status, Image FROM tblstudent WHERE 1=1";
                                                    $params = [];

                                                    if ($isSkillSearch) {
                                                        $countSql = "SELECT COUNT(DISTINCT t.ID) FROM tblstudent t JOIN student_achievements sa ON sa.StuID = t.StuID AND sa.status='approved' JOIN student_achievement_skills ssk ON ssk.achievement_id = sa.id WHERE ssk.skill_id = :skill_id";
                                                        $countStmt = $dbh->prepare($countSql);
                                                        $countStmt->bindValue(':skill_id', $skill_id, PDO::PARAM_INT);
                                                        $countStmt->execute();
                                                        $totalRows = $countStmt->fetchColumn();

                                                        $sql = "SELECT t.ID as sid, t.StuID, t.FamilyName, t.FirstName, t.Program, t.Gender, t.EmailAddress, t.Status, t.Image, IFNULL(SUM(sa.points),0) as totalPoints FROM tblstudent t JOIN student_achievements sa ON sa.StuID = t.StuID AND sa.status='approved' JOIN student_achievement_skills ssk ON ssk.achievement_id = sa.id WHERE ssk.skill_id = :skill_id GROUP BY t.ID ORDER BY totalPoints DESC, t.ID DESC LIMIT :limit OFFSET :offset";
                                                        $params[':skill_id'] = $skill_id;
                                                    } else {
                                                        $where = " WHERE 1=1";
                                                        if (!empty($searchdata)) {
                                                            $where .= " AND (StuID LIKE :searchdata OR FamilyName LIKE :searchdata OR FirstName LIKE :searchdata OR EmailAddress LIKE :searchdata)";
                                                            $params[':searchdata'] = '%' . $searchdata . '%';
                                                        }
                                                        if ($filter == 'active') { $where .= " AND Status=1"; } 
                                                        elseif ($filter == 'inactive') { $where .= " AND Status=0"; }

                                                        $countSql = "SELECT COUNT(*) FROM tblstudent" . $where;
                                                        $countQuery = $dbh->prepare($countSql);
                                                        foreach ($params as $k => $v) {
                                                            $countQuery->bindValue($k, $v, PDO::PARAM_STR);
                                                        }
                                                        $countQuery->execute();
                                                        $totalRows = (int) $countQuery->fetchColumn();

                                                        $sql = "SELECT ID AS sid, StuID, FamilyName, FirstName, Program, Gender, EmailAddress, Status, Image FROM tblstudent" . $where . " ORDER BY FamilyName ASC, FirstName ASC LIMIT :limit OFFSET :offset";
                                                    }

                                                    $totalPages = $totalRows > 0 ? ceil($totalRows / $limit) : 1;

                                                    $query = $dbh->prepare($sql);
                                                    foreach ($params as $k => $v) {
                                                        $query->bindValue($k, $v);
                                                    }
                                                    $query->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
                                                    $query->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
                                                    $query->execute();
                                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                    if ($query->rowCount() > 0) {
                                                        foreach ($results as $row) { ?>
                                                            <tr>
                                                                <td data-label="Student">
                                                                     <div class="user-info">
                                                                         <?php if (!empty($row->Image)): ?>
                                                                           <img src="../admin/images/<?php echo htmlentities($row->Image); ?>" alt="Student Avatar"
                                                                             class="user-avatar-img">
                                                                         <?php else: ?>
                                                                           <div class="user-avatar"
                                                                             style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                                                             <?php echo getInitials($row->FirstName . ' ' . $row->FamilyName); ?>
                                                                           </div>
                                                                         <?php endif; ?>
                                                                         <div class="user-details">
                                                                             <span class="user-name"><?php echo htmlentities($row->FamilyName . ', ' . $row->FirstName); ?></span>
                                                                             <span class="user-email"><?php echo htmlentities($row->EmailAddress); ?></span>
                                                                         </div>
                                                                     </div>
                                                                 </td>
                                                                <td data-label="Student ID"><?php echo htmlentities($row->StuID); ?></td>
                                                                <td data-label="Program">
                                                                     <?php
                                                                     $program_full = htmlentities($row->Program);
                                                                     if (preg_match('/\((\w+)\)/', $program_full, $matches)) {
                                                                         echo $matches[1];
                                                                     } else {
                                                                         echo $program_full;
                                                                     } ?>
                                                                 </td>
                                                                <td data-label="Status">
                                                                     <span class="status-badge <?php echo $row->Status == 1 ? 'active' : 'inactive'; ?>">
                                                                         <?php echo $row->Status == 1 ? 'Active' : 'Inactive'; ?>
                                                                     </span>
                                                                 </td>
                                                                 <?php if ($isSkillSearch): ?>
                                                                    <td data-label="Skill Points"><?php echo htmlentities($row->totalPoints); ?></td>
                                                                 <?php endif; ?>
                                                                <td data-label="Action">
                                                                     <div class="action-buttons">
                                                                         <a href="view-student.php?viewid=<?php echo htmlentities($row->sid); ?>" class="action-btn edit" title="View Profile" style="background: #e0e7ff; color: #4f46e5;">👁️</a>
                                                                         <a href="edit-student-detail.php?editid=<?php echo htmlentities($row->sid); ?>" class="action-btn edit" title="Edit">✏️</a>
                                                                         <a href="manage-students.php?statusid=<?php echo htmlentities($row->sid); ?>&status=<?php echo htmlentities($row->Status); ?>" class="action-btn toggle <?php echo $row->Status == 1 ? 'deactivate' : ''; ?>" title="<?php echo $row->Status == 1 ? 'Deactivate' : 'Activate'; ?>">
                                                                             <?php echo $row->Status == 1 ? '🔒' : '🔑'; ?>
                                                                         </a>
                                                                     </div>
                                                                 </td>
                                                            </tr>
                                                    <?php }
                                                    } else { ?>
                                                        <tr><td colspan="<?php echo $isSkillSearch ? '6' : '5'; ?>" style="text-align: center; color: red;">No Record Found</td></tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                            <!-- Pagination controls -->
                                            <div class="pagination">
                                                <div class="pagination-info">
                                                    Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $limit, $totalRows); ?> of <?php echo $totalRows; ?> students
                                                </div>
                                                <div class="pagination-buttons">
                                                    <?php
                                                    if ($totalPages > 1) {
                                                        $baseParams = [];
                                                        if (!empty($searchdata))
                                                            $baseParams['searchdata'] = $searchdata;
                                                        if (!empty($filter) && $filter !== 'all')
                                                            $baseParams['filter'] = $filter;

                                                        $buildUrl = function ($p) use ($baseParams) {
                                                            $params = $baseParams;
                                                            $params['page'] = $p;
                                                            return 'manage-students.php?' . http_build_query($params);
                                                        };
                                                        
                                                        $prevDisabled = $page <= 1 ? ' disabled' : '';
                                                        echo '<a href="' . ($page <= 1 ? '#' : $buildUrl($page - 1)) . '" class="pagination-btn" ' . $prevDisabled . '>Previous</a>';
                                                        
                                                        $nextDisabled = $page >= $totalPages ? ' disabled' : '';
                                                        echo '<a href="' . ($page >= $totalPages ? '#' : $buildUrl($page + 1)) . '" class="pagination-btn" ' . $nextDisabled . '>Next</a>';
                                                    } ?>
                                                </div>
                                            </div>
                                        </div>
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
        <script>
            // Display toast notification for status updates
            if (typeof statusMessage !== 'undefined' && statusMessage) {
                toastr.success(statusMessage);
            }
        </script>
    </body>

    </html>
<?php } ?>