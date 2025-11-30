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
                      <form method="get" id="searchForm" class="d-flex" style="gap: 12px; flex-grow: 1;">
                        <input id="searchdata" type="text" name="searchdata" class="search-box"
                          placeholder="Search by Student ID, Name, or Skill"
                          value="<?php echo isset($_GET['searchdata']) ? htmlentities($_GET['searchdata']) : ''; ?>">
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
                    <table class="table">
                      <thead class="table-wrapper thead">
                        <tr>
                          <th class="font-weight-bold">Student</th>
                          <th class="font-weight-bold">Student ID</th>
                          <th class="font-weight-bold">Program</th>
                          <th class="font-weight-bold">Gender</th>
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
                                <td>
                                  <div class="user-info">
                                    <div class="user-avatar"
                                      style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                      <?php echo getInitials($row->FirstName . ' ' . $row->FamilyName); ?>
                                    </div>
                                    <div class="user-details">
                                      <span
                                        class="user-name"><?php echo htmlentities($row->FamilyName . ', ' . $row->FirstName); ?></span>
                                      <span class="user-email"><?php echo htmlentities($row->EmailAddress); ?></span>
                                    </div>
                                  </div>
                                </td>
                                <td><?php echo htmlentities($row->StuID); ?></td>
                                <td>
                                  <?php
                                  $program_full = htmlentities($row->Program);
                                  if (preg_match('/\((\w+)\)/', $program_full, $matches)) {
                                    echo $matches[1];
                                  } else {
                                    echo $program_full; // Fallback to full name if no acronym
                                  } ?>
                                </td>
                                <td><?php echo htmlentities($row->Gender); ?></td>
                                <td>
                                  <span class="status-badge <?php echo $row->Status == 1 ? 'active' : 'inactive'; ?>">
                                    <?php echo $row->Status == 1 ? 'Active' : 'Inactive'; ?>
                                  </span>
                                </td>
                                <?php if ($isSkillSearch): ?>
                                  <td><?php echo htmlentities($row->totalPoints); ?></td>
                                <?php endif; ?>
                                <td>
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
                              <?php $cnt++;
                            }
                          } else { ?>
                            <tr class="text-center">
                              <td colspan="<?php echo $isSkillSearch ? '7' : '6'; ?>" style="color: red;">No record found
                                against this search
                              </td>
                            </tr>
                          <?php }
                        } else {
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

                          $whereSQL = ' WHERE ' . implode(' OR ', $whereParts);

                          // count
                          $countSql = "SELECT COUNT(ID) FROM tblstudent " . $whereSQL;
                          $countStmt = $dbh->prepare($countSql);
                          foreach ($params as $k => $v) {
                            // guess param type
                            $countStmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
                          }
                          $countStmt->execute();
                          $total_rows = (int) $countStmt->fetchColumn();
                          $total_pages = ($total_rows > 0) ? ceil($total_rows / $no_of_records_per_page) : 1;

                          // data query
                          $sql = "SELECT ID as sid, StuID, FamilyName, FirstName, Program, Gender, EmailAddress, Status FROM tblstudent " . $whereSQL . " ORDER BY ID DESC LIMIT :offset, :limit";
                          $query = $dbh->prepare($sql);
                          foreach ($params as $k => $v) {
                            $query->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
                          }
                          $query->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
                          $query->bindValue(':limit', (int) $no_of_records_per_page, PDO::PARAM_INT);
                          $query->execute();
                          $results = $query->fetchAll(PDO::FETCH_OBJ);
                          $cnt = 1 + $offset;
                          if ($query->rowCount() > 0) {
                            foreach ($results as $row) { ?>
                              <tr>
                                <td>
                                  <div class="user-info">
                                    <div class="user-avatar"
                                      style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                      <?php echo getInitials($row->FirstName . ' ' . $row->FamilyName); ?>
                                    </div>
                                    <div class="user-details">
                                      <span
                                        class="user-name"><?php echo htmlentities($row->FamilyName . ', ' . $row->FirstName); ?></span>
                                      <span class="user-email"><?php echo htmlentities($row->EmailAddress); ?></span>
                                    </div>
                                  </div>
                                </td>
                                <td><?php echo htmlentities($row->StuID); ?></td>
                                <td><?php echo htmlentities($row->Program); ?></td>
                                <td><?php echo htmlentities($row->Gender); ?></td>
                                <td><span
                                    class="status-badge <?php echo $row->Status == 1 ? 'active' : 'inactive'; ?>"><?php echo $row->Status == 1 ? 'Active' : 'Inactive'; ?></span>
                                </td>
                                <td>
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
        <?php include_once('includes/footer.php'); ?>
      </div>
    </div>
    </div>

    <!-- Add Notice Modal -->
    <div class="modal fade" id="addNoticeModal" tabindex="-1" role="dialog" aria-labelledby="addNoticeModalLabel"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addNoticeModalLabel">Add New Notice</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form method="post">
            <div class="modal-body">
              <div class="form-group">
                <label for="nottitle">Notice Title</label>
                <input type="text" class="form-control" id="nottitle" name="nottitle" required>
              </div>
              <div class="form-group">
                <label for="notmsg">Notice Message</label>
                <textarea class="form-control" id="notmsg" name="notmsg" rows="5" required></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="submit" name="add_notice" class="btn btn-primary">Add Notice</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Message Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="messageModalLabel">Send Message to <span id="studentName"></span></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form method="post">
            <div class="modal-body">
              <input type="hidden" name="student_email" id="studentEmail">
              <input type="hidden" name="student_stuid" id="studentStuID">
              <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
              </div>
              <div class="form-group">
                <label for="message">Message</label>
                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
            </div>
          </form>
        </div>
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