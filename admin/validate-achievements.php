<?php
session_start();
include_once('includes/dbconnection.php');
// Ensure PHPMailer and mail config are loaded so send_rejection_email works
// Vendor autoload is in the project root's `vendor` directory
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
  require_once __DIR__ . '/../vendor/autoload.php';
}
include_once __DIR__ . '/../includes/mail_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
if (strlen($_SESSION['sturecmsaid']) == 0) {
  header('location:login.php');
  exit;
}
$adminId = $_SESSION['sturecmsaid'];

// CSRF token
if (empty($_SESSION['csrf_token_admin'])) {
  $_SESSION['csrf_token_admin'] = bin2hex(random_bytes(16));
}

// Search and filter functionality
$searchdata = isset($_REQUEST['searchdata']) ? trim($_REQUEST['searchdata']) : '';
$category_filter = isset($_REQUEST['category_filter']) ? $_REQUEST['category_filter'] : 'all';

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

/**
 * Sends a formatted email notification for a rejected achievement.
 *
 * @param string $recipientEmail The student's email address.
 * @param string $studentName The student's name.
 * @param string $skills A comma-separated string of skills for the achievement.
 * @param string $level The level of the achievement (e.g., School, City).
 * @param string $rejectionReason The reason for the rejection.
 * @return bool True on success, false on failure.
 */
function send_rejection_email($recipientEmail, $studentName, $skills, $level, $rejectionReason)
{
  global $MAIL_HOST, $MAIL_USERNAME, $MAIL_PASSWORD, $MAIL_PORT, $MAIL_ENCRYPTION, $MAIL_FROM, $MAIL_FROM_NAME;

  $currentYear = date('Y');
  $subject = "Update on your achievement submission";

  $bodyHtml = <<<EOT
<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
    <div style="background-color: #f4f4f4; padding: 20px; text-align: center; border-bottom: 1px solid #ddd;">
        <h2 style="color: #333; margin: 0;">Achievement Submission Update</h2>
    </div>
    <div style="padding: 30px;">
        <p>Hi {$studentName},</p>
        <p>Thank you for your recent achievement submission. After review, your submission for "<strong>{$skills}</strong>" at the <strong>{$level}</strong> level has been declined.</p>
        <div style="background-color: #fffbe6; border-left: 5px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <h4 style="margin-top: 0; color: #b45309;">Reason for Rejection:</h4>
            <p style="white-space: pre-wrap;">{$rejectionReason}</p>
        </div>
        <p>If you believe this was a mistake or have additional information to provide, please resubmit your achievement with the necessary corrections.</p>
        <p>Thank you,<br>The Student Profiling System Team</p>
    </div>
    <div style="background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #ddd;">
        <p>&copy; {$currentYear} Student Profiling System. All rights reserved.</p>
    </div>
</div>
EOT;

  $bodyText = "Hi {$studentName},\n\nYour achievement submission for \"{$skills}\" ({$level} level) has been declined.\n\nReason: {$rejectionReason}\n\nPlease resubmit with the necessary corrections if applicable.\n\nThank you,\nThe Student Profiling System Team";

  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host = $MAIL_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = $MAIL_USERNAME;
    $mail->Password = $MAIL_PASSWORD;
    $mail->SMTPSecure = !empty($MAIL_ENCRYPTION) ? $MAIL_ENCRYPTION : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int)$MAIL_PORT;
    $mail->setFrom(!empty($MAIL_FROM) ? $MAIL_FROM : $MAIL_USERNAME, !empty($MAIL_FROM_NAME) ? $MAIL_FROM_NAME : 'Student Profiling System');
    $mail->addAddress($recipientEmail);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $bodyHtml;
    $mail->AltBody = $bodyText;
    return $mail->send();
  } catch (\Exception $e) {
    $err = isset($mail) ? $mail->ErrorInfo : '';
    error_log("Rejection email failed: " . $e->getMessage() . ' | PHPMailer: ' . $err);
    return false;
  }
}

// Handle actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['id'])) {
  $id = intval($_POST['id']);
  $action = $_POST['action'];
  $notes = isset($_POST['rejection_reason']) ? trim($_POST['rejection_reason']) : 'rejected';
  // CSRF check
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token_admin']) {
    $_SESSION['ach_msg_admin'] = 'Invalid CSRF token.';
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
      $pre = $fetchStatus($id);
      if ($hasApprovedBy) {
        $u = $dbh->prepare("UPDATE student_achievements SET status='approved', approved_by=:staff, approved_at=NOW() WHERE id=:id");
        $u->bindParam(':staff', $adminId);
      } else {
        $u = $dbh->prepare("UPDATE student_achievements SET status='approved' WHERE id=:id");
      }
      $u->bindParam(':id', $id, PDO::PARAM_INT);
      $u->execute();
      $affected = $u->rowCount();
      if ($affected > 0) {
        // insert into achievement_approvals if exists
        try {
          $chkApp = $dbh->prepare("SHOW TABLES LIKE 'achievement_approvals'");
          $chkApp->execute();
          if ($chkApp->rowCount() > 0) {
            $ins = $dbh->prepare("INSERT INTO achievement_approvals (achievement_id, approved_by, approved_at, notes) VALUES (:aid, :staff, NOW(), NULL)");
            $ins->bindParam(':aid', $id, PDO::PARAM_INT);
            $ins->bindParam(':staff', $adminId);
            $ins->execute();
          }
        } catch (Exception $e) {
        }
        $_SESSION['ach_msg_admin'] = 'Achievement approved.';
      } else {
        $_SESSION['ach_msg_admin'] = 'No rows updated when approving.';
      }
    } elseif ($action === 'reject') {
      $pre = $fetchStatus($id);
      if ($hasApprovedBy) {
        $u = $dbh->prepare("UPDATE student_achievements SET status='rejected', approved_by=:staff, approved_at=NOW() WHERE id=:id");
        $u->bindParam(':staff', $adminId);
      } else {
        $u = $dbh->prepare("UPDATE student_achievements SET status='rejected' WHERE id=:id");
      }
      $u->bindParam(':id', $id, PDO::PARAM_INT);
      $u->execute();
      $affected = $u->rowCount();
      if ($affected > 0) {
        $uRejection = $dbh->prepare("UPDATE student_achievements SET rejection_reason=:reason WHERE id=:id");
        $uRejection->bindParam(':reason', $notes, PDO::PARAM_STR);
        $uRejection->bindParam(':id', $id, PDO::PARAM_INT);
        $uRejection->execute();
        try {
          $chkApp = $dbh->prepare("SHOW TABLES LIKE 'achievement_approvals'");
          $chkApp->execute();
          if ($chkApp->rowCount() > 0) {
            $ins = $dbh->prepare("INSERT INTO achievement_approvals (achievement_id, approved_by, approved_at, notes) VALUES (:aid, :staff, NOW(), :notes)");
            $ins->bindParam(':aid', $id, PDO::PARAM_INT);
            $ins->bindParam(':staff', $adminId);
            $ins->bindParam(':notes', $notes);
            $ins->execute();
          }
        } catch (Exception $e) {
        }

        // Fetch details to send rejection email
        $stmt = $dbh->prepare("
            SELECT s.FirstName, s.EmailAddress, a.level, GROUP_CONCAT(sk.name SEPARATOR ', ') AS skills
            FROM student_achievements a
            JOIN tblstudent s ON a.StuID = s.StuID
            LEFT JOIN student_achievement_skills sas ON a.id = sas.achievement_id
            LEFT JOIN skills sk ON sas.skill_id = sk.id
            WHERE a.id = :id
            GROUP BY a.id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $details = $stmt->fetch(PDO::FETCH_OBJ);

        if ($details && !empty($details->EmailAddress)) {
            send_rejection_email($details->EmailAddress, $details->FirstName, $details->skills, $details->level, $notes);
        }
        $_SESSION['ach_msg_admin'] = 'Achievement rejected.';
      } else {
        $_SESSION['ach_msg_admin'] = 'No rows updated when rejecting.';
      }
    }
  } catch (Exception $e) {
    $_SESSION['ach_msg_admin'] = 'Action error: ' . $e->getMessage();
  }

  // After processing, if action was approve, append skills to student record similarly to staff
  try {
    if (isset($action) && $action === 'approve' && isset($id) && $id > 0) {
      $aStmt = $dbh->prepare("SELECT StuID, category FROM student_achievements WHERE id=:id LIMIT 1");
      $aStmt->bindParam(':id', $id, PDO::PARAM_INT);
      $aStmt->execute();
      $ach = $aStmt->fetch(PDO::FETCH_OBJ);
      if ($ach) {
        $stu = $ach->StuID;
        $category = isset($ach->category) ? trim($ach->category) : '';

        $skStmt = $dbh->prepare("SELECT sk.name FROM student_achievement_skills ssk JOIN skills sk ON ssk.skill_id = sk.id WHERE ssk.achievement_id = :id");
        $skStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $skStmt->execute();
        $skillRows = $skStmt->fetchAll(PDO::FETCH_COLUMN);
        if ($skillRows && count($skillRows) > 0) {
          $col = (strtolower($category) === 'academic') ? 'Academic' : 'NonAcademic';
          $colChk = $dbh->prepare("SHOW COLUMNS FROM tblstudent LIKE :col");
          $colChk->bindValue(':col', $col, PDO::PARAM_STR);
          $colChk->execute();
          if ($colChk->rowCount() === 0) {
            $dbh->exec("ALTER TABLE tblstudent ADD COLUMN `" . $col . "` TEXT NULL");
          }

          $curStmt = $dbh->prepare("SELECT `$col` FROM tblstudent WHERE StuID = :stu LIMIT 1");
          $curStmt->bindParam(':stu', $stu, PDO::PARAM_STR);
          $curStmt->execute();
          $curVal = $curStmt->fetchColumn();
          $existing = [];
          if ($curVal !== false && $curVal !== null && trim($curVal) !== '') {
            $existing = array_map('trim', explode(',', $curVal));
          }
          $merged = array_unique(array_filter(array_map('trim', array_merge($existing, $skillRows))));
          $newVal = implode(', ', $merged);
          $up = $dbh->prepare("UPDATE tblstudent SET `$col` = :val WHERE StuID = :stu");
          $up->bindParam(':val', $newVal, PDO::PARAM_STR);
          $up->bindParam(':stu', $stu, PDO::PARAM_STR);
          $up->execute();
        }
      }
    }
  } catch (Exception $e) {
    // ignore errors but could log
  }

  header('Location: validate-achievements.php' . (isset($_POST['stu']) ? '?stu=' . urlencode($_POST['stu']) : ''));
  exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Student Profiling System || Validate Achievements</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
  <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/style(v2).css">
  <link rel="stylesheet" href="css/responsive.css">
  <style>
    .modal {
      display: none;
      position: fixed;
      z-index: 1050;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
      background-color: #fefefe;
      margin: 10% auto;
      padding: 20px;
      border: 1px solid #888;
      width: 80%;
      max-width: 700px;
      position: relative;
      border-radius: 8px;
    }

    .modal-content img {
      max-width: 100%;
      height: auto;
      display: block;
      margin: 0 auto;
    }

    .close-btn {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
      position: absolute;
      top: 10px;
      right: 20px;
    }

    .modal-header .close {
      padding: 1rem;
      margin: -1rem -1rem -1rem auto;
      position: absolute;
      top: 10px;
      right: 20px;
    }

    .close-btn:hover,
    .close-btn:focus {
      color: black;
      text-decoration: none;
      cursor: pointer;
    }
  </style>
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
            <a href="manage-students.php" class="add-btn" style="text-decoration: none; margin-right: 20px;">↩ Back</a>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="table-card" style="width: 150vh;">
                <div class="table-header">
                  <h2 class="table-title">Validate Achievements</h2>
                  <div class="table-actions">
                    <form method="get" class="d-flex" style="gap: 12px;">
                      <input type="text" name="searchdata" class="form-control" placeholder="Search by Student or Skill"
                        value="<?php echo htmlentities($searchdata); ?>">
                      <select name="category_filter" class="form-control">
                        <option value="all" <?php if ($category_filter == 'all')
                          echo 'selected'; ?>>All Categories
                        </option>
                        <option value="Academic" <?php if ($category_filter == 'Academic')
                          echo 'selected'; ?>>
                          Academic</option>
                        <option value="Non-Academic" <?php if ($category_filter == 'Non-Academic')
                          echo 'selected'; ?>>
                          Non-Academic</option>
                      </select>
                      <button type="submit" class="filter-btn" style="width: 40vh;">🔍 Search</button>
                    </form>
                  </div>
                </div>
                <?php if (isset($_SESSION['ach_msg_admin'])): ?>
                  <div class="alert alert-info">
                    <?php echo htmlentities($_SESSION['ach_msg_admin']);
                    unset($_SESSION['ach_msg_admin']); ?>
                  </div>
                <?php endif; ?>

                <?php
                // Fetch pending achievements, optional filter by student
                $stuFilter = null;
                if (isset($_GET['stu']))
                  $stuFilter = $_GET['stu'];

                $sql = "SELECT a.id, a.StuID, CONCAT(s.FamilyName, ' ', s.FirstName) AS StudentName, a.category, a.level, a.points, a.proof_image, a.created_at, GROUP_CONCAT(sk.name SEPARATOR ', ') AS skills
                  FROM student_achievements a
                  LEFT JOIN student_achievement_skills sas ON a.id = sas.achievement_id
                  LEFT JOIN skills sk ON sas.skill_id = sk.id
                  JOIN tblstudent s ON a.StuID = s.StuID
                  WHERE a.status = 'pending'";

                $params = [];
                if ($stuFilter) {
                  $sql .= " AND a.StuID = :stu";
                  $params[':stu'] = $stuFilter;
                }
                if (!empty($searchdata)) {
                  $sql .= " AND (s.FirstName LIKE :searchdata OR s.FamilyName LIKE :searchdata OR sk.name LIKE :searchdata)";
                  $params[':searchdata'] = '%' . $searchdata . '%';
                }
                if ($category_filter !== 'all') {
                  $sql .= " AND a.category = :category";
                  $params[':category'] = $category_filter;
                }

                $sql .= " GROUP BY a.id ORDER BY a.created_at DESC";
                $stmt = $dbh->prepare($sql);
                $stmt->execute($params);
                $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
                ?>
                <?php if (empty($rows)): ?>
                  <div class="alert alert-info">No pending achievements found.</div>
                <?php else: ?>
                  <div class="table-wrapper">
                    <table class="table">
                      <thead>
                        <tr>
                          <th>Student</th>
                          <th>Skills</th>
                          <th>Category</th>
                          <th>Level</th>
                          <th>Points</th>
                          <th>Proof</th>
                          <th>Submitted</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($rows as $r): ?>
                          <tr>
                            <td data-label="Student">
                              <div class="user-info">
                                <div class="user-avatar"
                                  style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                  <?php echo getInitials($r->StudentName); ?>
                                </div>
                                <div class="user-details">
                                  <span class="user-name"><?php echo htmlentities($r->StudentName); ?></span>
                                  <span class="user-email"><?php echo htmlentities($r->StuID); ?></span>
                                </div>
                              </div>
                            </td>
                            <td data-label="Skills"><?php echo htmlentities($r->skills); ?></td>
                            <td data-label="Category"><?php echo htmlentities($r->category); ?></td>
                            <td data-label="Level"><?php echo htmlentities($r->level); ?></td>
                            <td data-label="Points"><?php echo htmlentities($r->points); ?></td>
                            <td data-label="Proof">
                              <?php if (!empty($r->proof_image)): ?>
                                <button class="action-btn edit" style="background: #e0e7ff; color: #4f46e5;"
                                  onclick="showProofModal('../admin/images/achievements/<?php echo urlencode($r->proof_image); ?>')">View</button>
                              <?php else: ?>
                                <span>No proof</span>
                              <?php endif; ?>
                            </td>
                            <td data-label="Submitted"><?php echo date('M d, Y', strtotime($r->created_at)); ?></td>
                            <td data-label="Actions">
                              <div class="action-buttons">
                                <form method="post" style="display:inline-block;">
                                  <input type="hidden" name="id" value="<?php echo $r->id; ?>">
                                  <input type="hidden" name="action" value="approve">
                                  <input type="hidden" name="csrf_token"
                                    value="<?php echo $_SESSION['csrf_token_admin']; ?>">
                                  <?php if ($stuFilter) { ?><input type="hidden" name="stu"
                                      value="<?php echo htmlentities($stuFilter); ?>"><?php } ?>
                                  <button type="submit" class="action-btn toggle" title="Approve">✔️</button>
                                </form>
                                <button type="button" class="action-btn toggle deactivate" title="Reject"
                                  onclick="openRejectModal(<?php echo $r->id; ?>)">❌</button>
                              </div>
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
        <!-- Proof Modal -->
        <div id="proofModal" class="modal">
          <div class="modal-content">
            <span class="close-btn" onclick="closeProofModal()">&times;</span>
            <img id="proofImage" src="" alt="Proof Image" style="width:100%">
          </div>
        </div>
        <!-- Reject Modal -->
        <div id="rejectModal" class="modal">
          <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
              <h5 class="modal-title">Reason for Rejection</h5>
              <button type="button" class="close" onclick="closeRejectModal()">&times;</button>
            </div>
            <form id="rejectForm" method="post">
              <div class="modal-body">
                <input type="hidden" name="id" id="rejectId">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token_admin']; ?>">
                <?php if (isset($stuFilter) && $stuFilter) { ?><input type="hidden" name="stu"
                    value="<?php echo htmlentities($stuFilter); ?>"><?php } ?>
                <div class="form-group">
                  <label for="rejection_reason">Please provide a reason for rejecting this achievement:</label>
                  <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="4"
                    required></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Submit Rejection</button>
              </div>
            </form>
          </div>
        </div>
        <?php include_once('includes/footer.php'); ?>
      </div>
    </div>
  </div>
  <script src="vendors/js/vendor.bundle.base.js"></script>
  <script src="js/toast.js"></script>
  <script src="js/off-canvas.js"></script>
  <script src="js/misc.js"></script>
  <script>
    function showProofModal(imageUrl) {
      document.getElementById('proofImage').src = imageUrl;
      document.getElementById('proofModal').style.display = 'block';
    }
    function closeProofModal() {
      document.getElementById('proofModal').style.display = 'none';
    }
    window.onclick = function (event) {
      if (event.target == document.getElementById('proofModal')) {
        closeProofModal();
      }
      if (event.target == document.getElementById('rejectModal')) {
        closeRejectModal();
      }
    }

    function openRejectModal(id) {
      document.getElementById('rejectId').value = id;
      document.getElementById('rejectModal').style.display = 'block';
    }

    function closeRejectModal() {
      document.getElementById('rejectModal').style.display = 'none';
      document.getElementById('rejection_reason').value = '';
    }
  </script>
</body>

</html>