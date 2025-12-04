<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid']) == 0) { // Ensure admin session is checked
  header('location:logout.php');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/../includes/mail_config.php';

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

// AJAX endpoint for student mentions
if (isset($_GET['mention_suggest'])) {
  header('Content-Type: application/json');
  $term = isset($_GET['term']) ? trim($_GET['term']) : '';
  $response = [];
  if (strlen($term) > 1) {
    try {
      $stmt = $dbh->prepare("SELECT StuID, FamilyName, FirstName FROM tblstudent WHERE FamilyName LIKE :t OR FirstName LIKE :t LIMIT 10");
      $like = $term . '%';
      $stmt->bindValue(':t', $like, PDO::PARAM_STR);
      $stmt->execute();
      $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      // Log error, but return empty array
    }
  }
  echo json_encode($response);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_notice'])) {
  $nottitle = $_POST['nottitle'];
  $notmsg = $_POST['notmsg'];
  $sql = "INSERT INTO tblnotice(NoticeTitle,NoticeMsg) VALUES(:nottitle,:notmsg)";
  $query = $dbh->prepare($sql);
  $query->bindParam(':nottitle', $nottitle, PDO::PARAM_STR);
  $query->bindParam(':notmsg', $notmsg, PDO::PARAM_STR);
  $query->execute();
  $LastInsertId = $dbh->lastInsertId();
  if ($LastInsertId > 0) {
    // Handle mentions - extract mentions in format "@FirstName FamilyName "
    preg_match_all('/@([A-Za-z]+)\s+([A-Za-z]+)\s/', $notmsg, $matches, PREG_SET_ORDER);

    if (!empty($matches)) {
      foreach ($matches as $match) {
        $firstName = trim($match[1]);
        $familyName = trim($match[2]);

        // Get the student ID from database using first and last name
        $studentStmt = $dbh->prepare("SELECT StuID FROM tblstudent WHERE FirstName = :fname AND FamilyName = :lname LIMIT 1");
        $studentStmt->bindValue(':fname', $firstName, PDO::PARAM_STR);
        $studentStmt->bindValue(':lname', $familyName, PDO::PARAM_STR);
        $studentStmt->execute();
        $student = $studentStmt->fetch(PDO::FETCH_OBJ);

        if ($student) {
          $mentionedStuID = $student->StuID;
          $messageSQL = "INSERT INTO tblmessages (SenderID, SenderType, RecipientStuID, Subject, Message, IsRead, Timestamp) VALUES (:sid, :stype, :stuid, :subject, :msg, 0, NOW())";
          $messageStmt = $dbh->prepare($messageSQL);
          $messageStmt->bindValue(':sid', $_SESSION['sturecmsaid'], PDO::PARAM_INT);
          $messageStmt->bindValue(':stype', 'admin', PDO::PARAM_STR);
          $messageStmt->bindValue(':stuid', $mentionedStuID, PDO::PARAM_STR);
          $messageStmt->bindValue(':subject', "You were mentioned in a notice: " . $nottitle, PDO::PARAM_STR);
          $messageStmt->bindValue(':msg', "You were mentioned in the notice titled '{$nottitle}'.\n\nContent:\n" . $notmsg, PDO::PARAM_STR);
          $messageStmt->execute();
        }
      }
    }
    $_SESSION['flash_message'] = "Notice has been added successfully.";
  } else {
    $_SESSION['flash_message_error'] = "Something Went Wrong while adding the notice. Please try again.";
  }
  // Redirect to the same page to avoid form resubmission
  header("Location: " . $_SERVER['REQUEST_URI']);
  exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
  $toEmail = $_POST['student_email'];
  $recipientStuID = $_POST['student_stuid'];
  $subject = $_POST['subject'];
  $messageBody = $_POST['message'];

  $mail = new PHPMailer(true);
  try {
    //Server settings
    $mail->isSMTP();
    $mail->Host = $MAIL_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = $MAIL_USERNAME;
    $mail->Password = $MAIL_PASSWORD;
    $mail->SMTPSecure = !empty($MAIL_ENCRYPTION) ? $MAIL_ENCRYPTION : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $MAIL_PORT;

    //Recipients
    $fromEmail = !empty($MAIL_FROM) ? $MAIL_FROM : $MAIL_USERNAME;
    $fromName = !empty($MAIL_FROM_NAME) ? $MAIL_FROM_NAME : 'Student Profiling System';
    $mail->setFrom($fromEmail, $fromName);
    $mail->addAddress($toEmail);

    // Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = nl2br($messageBody);
    $mail->AltBody = $messageBody;

    $mail->send();
    $_SESSION['flash_message'] = 'Message has been sent successfully.';

    // Also save the message to the database
    try {
      $senderID = $_SESSION['sturecmsaid']; // Admin ID from session
      $senderType = 'admin'; // Set sender type as admin
      $isRead = 0; // 0 for unread

      $sql = "INSERT INTO tblmessages (SenderID, SenderType, RecipientStuID, Subject, Message, IsRead, Timestamp) VALUES (:sender_id, :sender_type, :recipient_stuid, :subject, :message, :is_read, NOW())";
      $stmt = $dbh->prepare($sql);
      $stmt->bindParam(':sender_id', $senderID, PDO::PARAM_INT);
      $stmt->bindParam(':sender_type', $senderType, PDO::PARAM_STR);
      $stmt->bindParam(':recipient_stuid', $recipientStuID, PDO::PARAM_STR);
      $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
      $stmt->bindParam(':message', $messageBody, PDO::PARAM_STR);
      $stmt->bindParam(':is_read', $isRead, PDO::PARAM_INT);
      $stmt->execute();
    } catch (Exception $e) {
      // Optionally, handle DB insertion error, though the email might have sent.
    }
  } catch (Exception $e) {
    $_SESSION['flash_message_error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
  }
  // Redirect to the same page to avoid form resubmission
  header("Location: " . $_SERVER['REQUEST_URI']);
  exit;
}



if (true) {
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
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/modal.css">
    <link rel="stylesheet" href="css/search.css">

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
              <button type="button" class="add-btn" data-toggle="modal" data-target="#addNoticeModal"
                style="margin-right: 20px;">
                + Add Notice
              </button>
            </div>
            <div class="row">
              <div class="col-md-12 grid-margin stretch-card">
                <div class="table-card">
                  <div class="table-header">
                    <h2 class="table-title">Student Search</h2>
                    <div class="table-actions">
                      <form method="get" id="searchForm" class="d-flex" style="gap: 12px; flex-grow: 1; align-items:center;">
                        <input id="searchdata" type="text" name="searchdata" class="search-box"
                          placeholder="Search by Student ID, Name, or Skill"
                          value="<?php echo isset($_GET['searchdata']) ? htmlentities($_GET['searchdata']) : ''; ?>">

                        <?php $selYears = isset($_GET['year']) ? (array)$_GET['year'] : [1,2,3,4]; ?>
                        <div class="year-filter-dropdown" style="margin-left:8px;">
                          <button type="button" class="year-filter-toggle">Years ▾</button>
                          <div class="year-filter-menu" id="yearFilterMenu" aria-hidden="true">
                            <label><input type="checkbox" name="year[]" value="1" <?php echo in_array(1,$selYears) ? 'checked' : ''; ?>> 1st Year</label>
                            <label><input type="checkbox" name="year[]" value="2" <?php echo in_array(2,$selYears) ? 'checked' : ''; ?>> 2nd Year</label>
                            <label><input type="checkbox" name="year[]" value="3" <?php echo in_array(3,$selYears) ? 'checked' : ''; ?>> 3rd Year</label>
                            <label><input type="checkbox" name="year[]" value="4" <?php echo in_array(4,$selYears) ? 'checked' : ''; ?>> 4th Year</label>
                            <div class="year-filter-footer">
                              <button type="button" id="yearSelectAll">All</button>
                              <button type="button" id="yearClearAll">Clear</button>
                            </div>
                          </div>
                        </div>

                        <button type="submit" class="filter-btn" id="submit">🔍 Search</button>
                      </form>
                    </div>
                  </div>

                  <div class="d-sm-flex align-items-center my-4">
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
                      <h4 align="center">Results for
                        "<?php echo htmlentities($sdata); ?>"<?php echo ($isSkillSearch) ? ' (skill search - ranked by points)' : ''; ?>
                      </h4>
                    <?php } ?>
                  </div>
                  <div class="table-responsive border rounded p-1 card-view">
                    <?php
                    $sdata = isset($_GET['searchdata']) ? trim($_GET['searchdata']) : '';
                    // Year filter handling (defaults to all years checked)
                    $selectedYears = isset($_GET['year']) ? array_map('intval', (array)$_GET['year']) : [1,2,3,4];
                    $yearFilterSql = '';
                    $yearParams = [];
                    if (count($selectedYears) > 0 && count($selectedYears) !== 4) {
                      $placeholders = [];
                      foreach ($selectedYears as $i => $yv) {
                        $ph = ':y' . $i;
                        $placeholders[] = $ph;
                        $yearParams[$ph] = $yv;
                      }
                      $yearFilterSql = ' AND t.YearLevel IN (' . implode(',', $placeholders) . ')';
                    }
                    $pageno = isset($_GET['pageno']) ? max(1, intval($_GET['pageno'])) : 1;
                    $no_of_records_per_page = 5;
                    $offset = ($pageno - 1) * $no_of_records_per_page;

                    // If this is a skill search, show leaderboard instead of table
                    if ($isSkillSearch && $skill_id) { ?>
                    
                    <!-- Leaderboard View for Skill Search -->
                    <div class="container">
                      <div class="top-performers">
                        <?php
                        // Fetch all ranked students for this skill (for top 3)
                        $rankSql = "SELECT t.ID as sid, t.StuID, t.FamilyName, t.FirstName, t.Program, t.Gender, t.EmailAddress, t.Status, t.Image, IFNULL(SUM(sa.points),0) as totalPoints
                          FROM tblstudent t
                          JOIN student_achievements sa ON sa.StuID = t.StuID AND sa.status='approved'
                          JOIN student_achievement_skills ssk ON ssk.achievement_id = sa.id
                          WHERE ssk.skill_id = :skill_id" . $yearFilterSql . "
                          GROUP BY t.ID
                          ORDER BY totalPoints DESC, t.ID DESC
                          LIMIT 100";
                        $rankStmt = $dbh->prepare($rankSql);
                        $rankStmt->bindValue(':skill_id', $skill_id, PDO::PARAM_INT);
                        foreach ($yearParams as $k => $v) {
                          $rankStmt->bindValue($k, $v, PDO::PARAM_INT);
                        }
                        $rankStmt->execute();
                        $ranked = $rankStmt->fetchAll(PDO::FETCH_OBJ);
                        $topThree = array_slice($ranked, 0, 3);
                        
                        // Use medals for top 3
                        $medals = ['first' => '🥇', 'second' => '🥈', 'third' => '🥉'];
                        foreach ($topThree as $idx => $p) {
                          $class = $idx === 0 ? 'first' : ($idx === 1 ? 'second' : 'third');
                          $badge = $medals[$class] ?? '';
                          $initials = getInitials($p->FirstName . ' ' . $p->FamilyName);
                          $imageExists = !empty($p->Image) && file_exists(__DIR__ . '/images/' . basename($p->Image));
                          ?>
                          <div class="performer-card <?php echo $class; ?>">
                            <div class="rank-badge"><?php echo $badge; ?></div>
                            <?php if ($imageExists): ?>
                              <img src="images/<?php echo htmlentities($p->Image); ?>" alt="<?php echo htmlentities($p->FirstName); ?>" class="performer-avatar" style="object-fit: cover;">
                            <?php else: ?>
                              <div class="performer-avatar"><?php echo htmlentities($initials); ?></div>
                            <?php endif; ?>
                            <div class="performer-name"><?php echo htmlentities($p->FirstName . ' ' . $p->FamilyName); ?></div>
                            <div class="performer-points"><?php echo 'Points: ' . number_format($p->totalPoints); ?></div>
                            <div class="achievement-badge"><?php echo htmlentities($p->Program); ?></div>
                          </div>
                        <?php } ?>
                      </div>

                      <div class="leaderboard-card">
                        <div class="leaderboard-header">
                          <div class="header-label">Rank</div>
                          <div class="header-label">Student Name</div>
                          <div class="header-label">Badge</div>
                          <div class="header-label">Total Points</div>
                        </div>

                        <?php
                        $rank = 1;
                        foreach ($ranked as $r) {
                          ?>
                          <?php $imageExists = !empty($r->Image) && file_exists(__DIR__ . '/images/' . basename($r->Image)); ?>
                          <div class="leaderboard-row" data-sid="<?php echo htmlentities($r->StuID); ?>" style="cursor: pointer;">
                            <div class="rank-number"><?php echo $rank; ?></div>
                            <div class="user-info">
                              <?php if ($imageExists): ?>
                                <img src="images/<?php echo htmlentities($r->Image); ?>" alt="<?php echo htmlentities($r->FirstName); ?>" class="user-avatar" style="object-fit: cover; width: 40px; height: 40px;">
                              <?php else: ?>
                                <div class="user-avatar"><?php echo htmlentities(getInitials($r->FirstName . ' ' . $r->FamilyName)); ?></div>
                              <?php endif; ?>
                              <div class="user-name"><?php echo htmlentities($r->FirstName . ' ' . $r->FamilyName); ?></div>
                            </div>
                            <div class="badge-cell"><?php echo $rank <= 3 ? '🏆' : '🎯'; ?></div>
                            <div class="points-cell"><?php echo number_format($r->totalPoints); ?></div>
                          </div>
                          <?php $rank++;
                        }
                        if (count($ranked) === 0) { ?>
                          <div class="text-center" style="color: red; padding:20px;">No record found against this search</div>
                        <?php } ?>
                      </div>
                    </div>

                    <script>
                      document.addEventListener('DOMContentLoaded', function() {
                        document.querySelectorAll('.leaderboard-row').forEach(function(row) {
                          row.addEventListener('click', function() {
                            var sid = this.getAttribute('data-sid');
                            if (sid) {
                              window.location = 'view-student-profile.php?sid=' + encodeURIComponent(sid);
                            }
                          });
                        });
                      });
                    </script>

                    <?php } else { ?>
                    
                    <!-- Default Table View -->
                    <table class="table">
                      <thead class="table-wrapper thead">
                        <tr>
                          <th class="font-weight-bold">Student</th>
                          <th class="font-weight-bold">Student ID</th>
                          <th class="font-weight-bold">Program</th>
                          <th class="font-weight-bold">Gender</th>
                          <th class="font-weight-bold">Status</th>
                          <th class="font-weight-bold">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        // Enhanced search: allow searching across many tblstudent fields:
                          // StuID, FamilyName, FirstName, Program, Major, YearLevel, Gender,
                          // Barangay, CityMunicipality, Province, Category, EmailAddress and map
                          // keywords "active"/"inactive" to Status.
                          $total_rows = 0;
                          $params = [];
                          $whereParts = [];

                          $term = trim($sdata);
                          if ($term !== '') {
                            $termLike = '%' . $term . '%';
                            $params[':term_like'] = $termLike;
                            // direct matches
                            $whereParts[] = "StuID LIKE :term_like";
                            $whereParts[] = "FamilyName LIKE :term_like";
                            $whereParts[] = "FirstName LIKE :term_like";
                            $whereParts[] = "Program LIKE :term_like";
                            $whereParts[] = "Major LIKE :term_like";
                            $whereParts[] = "Barangay LIKE :term_like";
                            $whereParts[] = "CityMunicipality LIKE :term_like";
                            $whereParts[] = "Province LIKE :term_like";
                            $whereParts[] = "Category LIKE :term_like";
                            $whereParts[] = "EmailAddress LIKE :term_like";

                            // Year level exact match if they search '1','2','3','4' or 'year 1' etc.
                            if (preg_match('/^\s*(?:year\s*)?([1-4])(?:st|nd|rd|th)?\s*(?:year)?\s*$/i', $term, $ym)) {
                              $whereParts[] = "YearLevel = :year_level";
                              $params[':year_level'] = $ym[1];
                            } else {
                              // match possible textual year terms like 'grade 11' won't be present in schema,
                              // but keep YearLevel pattern match against the term if it's numeric
                              if (preg_match('/^\d+$/', $term)) {
                                $whereParts[] = "YearLevel = :year_level_num";
                                $params[':year_level_num'] = $term;
                              }
                            }

                            // Gender match (Male/Female/Other)
                            if (preg_match('/^(male|female|m|f)$/i', $term, $gm)) {
                              $gval = (strtolower($gm[1]) === 'm') ? 'Male' : ((strtolower($gm[1]) === 'f') ? 'Female' : ucfirst(strtolower($gm[1])));
                              $whereParts[] = "Gender = :gender_val";
                              $params[':gender_val'] = $gval;
                            } else {
                              // allow fuzzy gender search too
                              $whereParts[] = "Gender LIKE :term_like";
                            }

                            // Map status keywords
                            $lower = strtolower($term);
                            if ($lower === 'active') {
                              $whereParts[] = "Status = :status_active";
                              $params[':status_active'] = 1;
                            } elseif ($lower === 'inactive' || $lower === 'graduated' || $lower === 'transferred') {
                              // there's only a Status bit in the schema; treat other keywords as Status = 0
                              $whereParts[] = "Status = :status_inactive";
                              $params[':status_inactive'] = 0;
                            }
                          } else {
                            // empty search => match all
                            $whereParts[] = "1";
                          }

                          $whereSQL = ' WHERE (' . implode(' OR ', $whereParts) . ')' . $yearFilterSql;

                          // count
                          $countSql = "SELECT COUNT(ID) FROM tblstudent " . $whereSQL;
                          $countStmt = $dbh->prepare($countSql);
                          foreach ($params as $k => $v) {
                            // guess param type
                            $countStmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
                          }
                          // bind year params if any
                          foreach ($yearParams as $k => $v) {
                            $countStmt->bindValue($k, $v, PDO::PARAM_INT);
                          }
                          $countStmt->execute();
                          $total_rows = (int) $countStmt->fetchColumn();
                          $total_pages = ($total_rows > 0) ? ceil($total_rows / $no_of_records_per_page) : 1;

                          // data query
                          $sql = "SELECT ID as sid, StuID, FamilyName, FirstName, Program, Gender, EmailAddress, Status, Image FROM tblstudent " . $whereSQL . " ORDER BY ID DESC LIMIT :offset, :limit";
                          $query = $dbh->prepare($sql);
                          foreach ($params as $k => $v) {
                            $query->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
                          }
                          // bind year params if any
                          foreach ($yearParams as $k => $v) {
                            $query->bindValue($k, $v, PDO::PARAM_INT);
                          }
                          $query->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
                          $query->bindValue(':limit', (int) $no_of_records_per_page, PDO::PARAM_INT);
                          $query->execute();
                          $results = $query->fetchAll(PDO::FETCH_OBJ);
                          $cnt = 1 + $offset;
                          if ($query->rowCount() > 0) {
                            foreach ($results as $row) { ?>
                              <tr>
                                <td data-label="Student">
                                  <div class="user-info">
                                    <?php if (!empty($row->Image)): ?>
                                      <img src="images/<?php echo htmlentities($row->Image); ?>" alt="Student Avatar"
                                        class="user-avatar-img">
                                    <?php else: ?>
                                      <div class="user-avatar"
                                        style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                        <?php echo getInitials($row->FirstName . ' ' . $row->FamilyName); ?>
                                      </div>
                                    <?php endif; ?>
                                    <div class="user-details">
                                      <span
                                        class="user-name"><?php echo htmlentities($row->FamilyName . ', ' . $row->FirstName); ?></span>
                                      <span class="user-email"><?php echo htmlentities($row->EmailAddress); ?></span>
                                    </div>
                                  </div>
                                </td>
                                <td data-label="Student ID"><?php echo htmlentities($row->StuID); ?></td>
                                <td data-label="Program"><?php echo htmlentities($row->Program); ?></td>
                                <td data-label="Gender"><?php echo htmlentities($row->Gender); ?></td>
                                <td data-label="Status"><span
                                    class="status-badge <?php echo $row->Status == 1 ? 'active' : 'inactive'; ?>"><?php echo $row->Status == 1 ? 'Active' : 'Inactive'; ?></span>
                                </td>
                                <td data-label="Action">
                                  <div class="action-buttons">
                                    <a href="view-student-profile.php?sid=<?php echo urlencode($row->StuID); ?>"
                                      class="action-btn edit" title="View Profile"
                                      style="background: #e0e7ff; color: #4f46e5;">👁️</a>
                                    <a href="edit-student-detail.php?editid=<?php echo htmlentities($row->sid); ?>"
                                      class="action-btn edit" title="Edit">✏️</a>
                                    <?php if (isset($row->Status) && $row->Status == 1): ?>
                                      <button type="button" class="action-btn toggle deactivate message-btn" title="Message"
                                        data-toggle="modal" data-target="#messageModal"
                                        data-email="<?php echo htmlentities($row->EmailAddress); ?>"
                                        data-name="<?php echo htmlentities($row->FirstName . ' ' . $row->FamilyName); ?>"
                                        data-stuid="<?php echo htmlentities($row->StuID); ?>">✉️</button>
                                    <?php else: ?>
                                      <a href="manage-students.php?statusid=<?php echo htmlentities($row->sid); ?>&status=<?php echo htmlentities($row->Status); ?>"
                                        class="action-btn toggle" title="Activate">
                                        🔑
                                      </a>
                                    <?php endif; ?>
                                  </div>
                                </td>
                              </tr>
                              <?php
                              $cnt++;
                            }
                          } else { ?>
                            <tr class="text-center">
                              <td colspan="6" style="color: red;">No record found against this search
                              </td>
                            </tr>
                          <?php }
                        } ?>
                      </tbody>
                    </table>
                    <!-- Pagination controls -->
                    <div class="pagination">
                      <div class="pagination-info">
                        <?php if (isset($total_rows) && $total_rows > 0): ?>
                          Showing
                          <?php echo $offset + 1; ?>-<?php echo min($offset + $no_of_records_per_page, $total_rows); ?> of
                          <?php echo $total_rows; ?> results
                        <?php endif; ?>
                      </div>
                      <div class="pagination-buttons">
                        <?php
                        if (isset($total_pages) && $total_pages > 1) {
                          $baseParams = [];
                          if (!empty($sdata))
                            $baseParams['searchdata'] = $sdata;
                          $buildUrl = fn($p) => 'search.php?' . http_build_query(array_merge($baseParams, ['pageno' => $p]));

                          $prevDisabled = $pageno <= 1 ? 'disabled' : '';
                          echo '<a href="' . ($pageno <= 1 ? '#' : $buildUrl($pageno - 1)) . '" class="pagination-btn" ' . $prevDisabled . '>Previous</a>';

                          $nextDisabled = $pageno >= $total_pages ? 'disabled' : '';
                          echo '<a href="' . ($pageno >= $total_pages ? '#' : $buildUrl($pageno + 1)) . '" class="pagination-btn" ' . $nextDisabled . '>Next</a>';
                        }
                        ?>
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

    <!-- Add Notice Modal -->
    <div class="new-modal-overlay" id="addNoticeModalOverlay">
      <div class="new-modal">
        <div class="new-modal-header">
          <h2 class="new-modal-title">Add New Notice</h2>
          <button type="button" class="new-close-btn">&times;</button>
        </div>
        <form method="post">
          <div class="new-form-group">
            <label for="nottitle" class="new-form-label">Notice Title</label>
            <input type="text" class="new-form-input" id="nottitle" name="nottitle" required placeholder="Enter notice title" style="text-transform: capitalize;">
          </div>
          <div class="new-form-group">
            <label for="notmsg" class="new-form-label">Notice Message</label>
            <textarea class="new-form-textarea" id="notmsg" name="notmsg" rows="5" required placeholder="Enter notice details..." style="text-transform: capitalize;"></textarea>
            <small class="text-muted">Use @FirstName LastName to mention students.</small>
          </div>
          <div class="new-modal-footer">
            <button type="button" class="new-btn new-btn-cancel">Cancel</button>
            <button type="submit" name="add_notice" class="new-btn new-btn-submit">Add Notice</button>
          </div>
        </form>
      </div>
    </div>
    <!-- Message Modal -->
    <div class="new-modal-overlay" id="messageModalOverlay">
      <div class="new-modal">
        <div class="new-modal-header">
          <h2 class="new-modal-title">Send Message to <span id="studentName"></span></h2>
          <button type="button" class="new-close-btn">&times;</button>
        </div>
          <form method="post">
            <div class="new-modal-body">
              <input type="hidden" name="student_email" id="studentEmail">
              <input type="hidden" name="student_stuid" id="studentStuID">
              <div class="new-form-group">
                <label for="subject" class="new-form-label">Subject</label>
                <input type="text" class="new-form-input" id="subject" name="subject" required placeholder="Enter subject" style="text-transform: capitalize;">
              </div>
              <div class="new-form-group">
                <label for="message" class="new-form-label">Message</label>
                <textarea class="new-form-textarea" id="message" name="message" rows="5" required placeholder="Enter your message..." style="text-transform: capitalize;"></textarea>
              </div>
            </div>
            <div class="new-modal-footer">
              <button type="button" class="new-btn new-btn-cancel">Cancel</button>
              <button type="submit" name="send_message" class="new-btn new-btn-submit">Send Message</button>
            </div>
          </form>
      </div>
    </div>
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script src="js/toast.js"></script>
    <script src="js/script.js"></script>
    <script src="js/mention.js"></script>
    <script src="js/search.js"></script>

    <script>
      (function(){
        var toggle = document.querySelector('.year-filter-toggle');
        var menu = document.getElementById('yearFilterMenu');
        var selectAllBtn = document.getElementById('yearSelectAll');
        var clearAllBtn = document.getElementById('yearClearAll');

        if (!toggle || !menu) return;

        function openMenu() {
          menu.classList.add('open');
          menu.setAttribute('aria-hidden','false');
        }
        function closeMenu() {
          menu.classList.remove('open');
          menu.setAttribute('aria-hidden','true');
        }

        toggle.addEventListener('click', function(e){
          if (menu.classList.contains('open')) closeMenu(); else openMenu();
        });

        // Close on outside click
        document.addEventListener('click', function(e){
          if (!menu.contains(e.target) && !toggle.contains(e.target)) {
            closeMenu();
          }
        });

        // Select/clear helpers
        if (selectAllBtn) selectAllBtn.addEventListener('click', function(){
          menu.querySelectorAll('input[type="checkbox"]').forEach(function(cb){ cb.checked = true; });
        });
        if (clearAllBtn) clearAllBtn.addEventListener('click', function(){
          menu.querySelectorAll('input[type="checkbox"]').forEach(function(cb){ cb.checked = false; });
        });
      })();
    </script>

    <script>
      window.srData = {
        flash_message: <?php echo isset($_SESSION['flash_message']) ? json_encode($_SESSION['flash_message']) : 'null'; ?>,
        flash_message_error: <?php echo isset($_SESSION['flash_message_error']) ? json_encode($_SESSION['flash_message_error']) : 'null'; ?>
      };
      <?php if (isset($_SESSION['flash_message']))
        unset($_SESSION['flash_message']); ?>
      <?php if (isset($_SESSION['flash_message_error']))
        unset($_SESSION['flash_message_error']); ?>
    </script>
  </body>

  </html>
<?php } ?>