<?php
session_start();
include_once('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsstaffid']) == 0) {
  header('location:login.php');
  exit;
}
$staffId = $_SESSION['sturecmsstaffid'];

// CSRF token
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['id'])) {
  $id = intval($_POST['id']);
  $action = $_POST['action'];
  // CSRF check
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['ach_msg'] = 'Invalid CSRF token.';
    header('Location: validate-achievements.php');
    exit;
  }

  // check if approved_by/approved_at columns exist
  $hasApprovedBy = false;
  try {
    $chk = $dbh->prepare("SHOW COLUMNS FROM student_achievements LIKE 'approved_by'");
    $chk->execute();
    if ($chk->rowCount() > 0)
      $hasApprovedBy = true;
  } catch (Exception $e) {
    $hasApprovedBy = false;
  }

  try {
    $logFile = __DIR__ . '/achieve_actions.log';
    $log = [];
    // helper to fetch status
    $fetchStatus = function ($aid) use ($dbh) {
      try {
        $ps = $dbh->prepare("SELECT status FROM student_achievements WHERE id=:id");
        $ps->bindParam(':id', $aid, PDO::PARAM_INT);
        $ps->execute();
        $r = $ps->fetch(PDO::FETCH_ASSOC);
        return $r ? $r['status'] : null;
      } catch (Exception $e) {
        return null;
      }
    };

    if ($action === 'approve') {
      $log['pre_status'] = $fetchStatus($id);
      if ($hasApprovedBy) {
        $u = $dbh->prepare("UPDATE student_achievements SET status='approved', approved_by=:staff, approved_at=NOW() WHERE id=:id");
        $u->bindParam(':staff', $staffId);
      } else {
        $u = $dbh->prepare("UPDATE student_achievements SET status='approved' WHERE id=:id");
      }
      $u->bindParam(':id', $id, PDO::PARAM_INT);
      $u->execute();
      $affected = $u->rowCount();
      $log['affected'] = $affected;
      $log['errorInfo'] = $u->errorInfo();
      $log['post_status'] = $fetchStatus($id);
      if ($affected > 0) {
        $_SESSION['ach_msg'] = 'Achievement approved.';
        // try to insert into achievement_approvals table if it exists
        try {
          $chkApp = $dbh->prepare("SHOW TABLES LIKE 'achievement_approvals'");
          $chkApp->execute();
          if ($chkApp->rowCount() > 0) {
            $notes = null;
            $ins = $dbh->prepare("INSERT INTO achievement_approvals (achievement_id, approved_by, approved_at, notes) VALUES (:aid, :staff, NOW(), :notes)");
            $ins->bindParam(':aid', $id, PDO::PARAM_INT);
            $ins->bindParam(':staff', $staffId);
            $ins->bindParam(':notes', $notes);
            $ins->execute();
            $log['approval_inserted'] = $ins->rowCount();
          }
        } catch (Exception $e) {
          $log['approval_insert_error'] = $e->getMessage();
        }
        // Now append approved skills to tblstudent Academic/NonAcademic column
        try {
          // Fetch achievement category and student id
          $aStmt = $dbh->prepare("SELECT StuID, category FROM student_achievements WHERE id=:id LIMIT 1");
          $aStmt->bindParam(':id', $id, PDO::PARAM_INT);
          $aStmt->execute();
          $ach = $aStmt->fetch(PDO::FETCH_OBJ);
          if ($ach) {
            $stu = $ach->StuID;
            $category = isset($ach->category) ? trim($ach->category) : '';

            // load skill names for this achievement
            $skStmt = $dbh->prepare("SELECT sk.name FROM student_achievement_skills ssk JOIN skills sk ON ssk.skill_id = sk.id WHERE ssk.achievement_id = :id");
            $skStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $skStmt->execute();
            $skillRows = $skStmt->fetchAll(PDO::FETCH_COLUMN);
            if ($skillRows && count($skillRows) > 0) {
              // determine target column
              $col = (strtolower($category) === 'academic') ? 'Academic' : 'NonAcademic';

              // ensure column exists on tblstudent (add if missing)
              $colChk = $dbh->prepare("SHOW COLUMNS FROM tblstudent LIKE :col");
              $colChk->bindValue(':col', $col, PDO::PARAM_STR);
              $colChk->execute();
              if ($colChk->rowCount() === 0) {
                // add column as TEXT
                $dbh->exec("ALTER TABLE tblstudent ADD COLUMN `" . $col . "` TEXT NULL");
              }

              // fetch existing value
              $curStmt = $dbh->prepare("SELECT `$col` FROM tblstudent WHERE StuID = :stu LIMIT 1");
              $curStmt->bindParam(':stu', $stu, PDO::PARAM_STR);
              $curStmt->execute();
              $curVal = $curStmt->fetchColumn();
              $existing = [];
              if ($curVal !== false && $curVal !== null && trim($curVal) !== '') {
                $existing = array_map('trim', explode(',', $curVal));
              }

              // merge unique
              $merged = array_unique(array_filter(array_map('trim', array_merge($existing, $skillRows))));
              $newVal = implode(', ', $merged);

              $up = $dbh->prepare("UPDATE tblstudent SET `$col` = :val WHERE StuID = :stu");
              $up->bindParam(':val', $newVal, PDO::PARAM_STR);
              $up->bindParam(':stu', $stu, PDO::PARAM_STR);
              $up->execute();
              $log['student_col_updated'] = [$col => $up->rowCount()];
            }
          }
        } catch (Exception $e) {
          $log['student_col_update_error'] = $e->getMessage();
        }
      } else {
        $_SESSION['ach_msg'] = 'No rows updated when approving (id=' . $id . ').';
        if (!empty($log['errorInfo'][2]))
          $_SESSION['ach_msg'] .= ' DB error: ' . $log['errorInfo'][2];
      }
      $log['action'] = 'approve';
      $log['id'] = $id;
      $log['staff'] = $staffId;
      @file_put_contents($logFile, date('c') . ' ' . json_encode($log) . PHP_EOL, FILE_APPEND);
      $_SESSION['ach_debug'] = $log;
    } elseif ($action === 'reject') {
      $log['pre_status'] = $fetchStatus($id);
      if ($hasApprovedBy) {
        $u = $dbh->prepare("UPDATE student_achievements SET status='rejected', approved_by=:staff, approved_at=NOW() WHERE id=:id");
        $u->bindParam(':staff', $staffId);
      } else {
        $u = $dbh->prepare("UPDATE student_achievements SET status='rejected' WHERE id=:id");
      }
      $u->bindParam(':id', $id, PDO::PARAM_INT);
      $u->execute();
      $affected = $u->rowCount();
      $log['affected'] = $affected;
      $log['errorInfo'] = $u->errorInfo();
      $log['post_status'] = $fetchStatus($id);
      if ($affected > 0) {
        $_SESSION['ach_msg'] = 'Achievement rejected.';
        // try to insert into achievement_approvals table if it exists
        try {
          $chkApp = $dbh->prepare("SHOW TABLES LIKE 'achievement_approvals'");
          $chkApp->execute();
          if ($chkApp->rowCount() > 0) {
            $notes = 'rejected';
            $ins = $dbh->prepare("INSERT INTO achievement_approvals (achievement_id, approved_by, approved_at, notes) VALUES (:aid, :staff, NOW(), :notes)");
            $ins->bindParam(':aid', $id, PDO::PARAM_INT);
            $ins->bindParam(':staff', $staffId);
            $ins->bindParam(':notes', $notes);
            $ins->execute();
            $log['approval_inserted'] = $ins->rowCount();
          }
        } catch (Exception $e) {
          $log['approval_insert_error'] = $e->getMessage();
        }
      } else {
        $_SESSION['ach_msg'] = 'No rows updated when rejecting (id=' . $id . ').';
        if (!empty($log['errorInfo'][2]))
          $_SESSION['ach_msg'] .= ' DB error: ' . $log['errorInfo'][2];
      }
      $log['action'] = 'reject';
      $log['id'] = $id;
      $log['staff'] = $staffId;
      @file_put_contents($logFile, date('c') . ' ' . json_encode($log) . PHP_EOL, FILE_APPEND);
      $_SESSION['ach_debug'] = $log;
    }
  } catch (Exception $e) {
    $_SESSION['ach_msg'] = 'Action error: ' . $e->getMessage();
  }

  header('Location: validate-achievements.php');
  exit;
}

// Fetch pending achievements with skills and student name
$sql = "SELECT a.id, a.StuID, CONCAT(s.FamilyName, ' ', s.FirstName) AS StudentName, a.category, a.level, a.points, a.proof_image, a.created_at, a.approved_by, a.approved_at, st.StaffName AS ApproverName, GROUP_CONCAT(sk.name SEPARATOR ', ') AS skills
FROM student_achievements a
LEFT JOIN student_achievement_skills sas ON a.id = sas.achievement_id
LEFT JOIN skills sk ON sas.skill_id = sk.id
JOIN tblstudent s ON a.StuID = s.StuID
LEFT JOIN tblstaff st ON a.approved_by = st.ID
WHERE a.status = 'pending'
GROUP BY a.id
ORDER BY a.created_at DESC";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_OBJ);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Student Profiling System || Validate Achievements</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/style(v2).css">
</head>

<body>
  <div class="container-scroller">
    <?php include_once('includes/header.php'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php include_once('includes/sidebar.php'); ?>
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="page-header">
            <h3 class="page-title"> Pending Achievements </h3>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page"> Validate Achievements</li>
              </ol>
            </nav>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-body">
                  <?php if (isset($_SESSION['ach_msg'])): ?>
                    <div class="alert alert-info">
                      <?php echo htmlentities($_SESSION['ach_msg']);
                      unset($_SESSION['ach_msg']); ?></div>
                  <?php endif; ?>

                  <?php if (empty($rows)): ?>
                    <div class="alert alert-info">No pending achievements found.</div>
                  <?php else: ?>
                    <div class="table-responsive">
                      <table class="table table-hover">
                        <thead>
                          <tr>
                            <th>Student</th>
                            <th>Skills</th>
                            <th>Category</th>
                            <th>Level</th>
                            <th>Points</th>
                            <th>Approver</th>
                            <th>Approved At</th>
                            <th>Proof</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($rows as $r): ?>
                            <tr>
                              <td><?php echo htmlentities($r->StudentName); ?>
                                <br><small><?php echo htmlentities($r->StuID); ?></small></td>
                              <td><?php echo htmlentities($r->skills); ?></td>
                              <td><?php echo htmlentities($r->category); ?></td>
                              <td><?php echo htmlentities($r->level); ?></td>
                              <td><?php echo htmlentities($r->points); ?></td>
                              <td><?php echo htmlentities($r->ApproverName ?? ''); ?></td>
                              <td><?php echo htmlentities($r->approved_at ?? ''); ?></td>
                              <td>
                                <?php if (!empty($r->proof_image)): ?>
                                  <a href="../admin/images/achievements/<?php echo urlencode($r->proof_image); ?>"
                                    target="_blank">View</a>
                                <?php else: ?>
                                  <span>No proof</span>
                                <?php endif; ?>
                              </td>
                              <td><?php echo htmlentities($r->created_at); ?></td>
                              <td>
                                <form method="post" style="display:inline-block;">
                                  <input type="hidden" name="id" value="<?php echo $r->id; ?>">
                                  <input type="hidden" name="action" value="approve">
                                  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                  <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                </form>
                                <form method="post" style="display:inline-block;margin-left:6px;">
                                  <input type="hidden" name="id" value="<?php echo $r->id; ?>">
                                  <input type="hidden" name="action" value="reject">
                                  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                  <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php endif; ?>
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
  <script src="js/off-canvas.js"></script>
  <script src="js/misc.js"></script>
</body>

</html>