<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsstaffid']) == 0) {
  header('location:logout.php');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer and mail config
require_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/../includes/mail_config.php';

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
  $sql = "insert into tblnotice(NoticeTitle,NoticeMsg)values(:nottitle,:notmsg)";
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
          $messageStmt->bindValue(':sid', $_SESSION['sturecmsstaffid'], PDO::PARAM_INT);
          $messageStmt->bindValue(':stype', 'staff', PDO::PARAM_STR);
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
        $mail->Host       = $MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = $MAIL_USERNAME;
        $mail->Password   = $MAIL_PASSWORD;
        $mail->SMTPSecure = !empty($MAIL_ENCRYPTION) ? $MAIL_ENCRYPTION : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $MAIL_PORT;

        //Recipients
        $fromEmail = !empty($MAIL_FROM) ? $MAIL_FROM : $MAIL_USERNAME;
        $fromName = !empty($MAIL_FROM_NAME) ? $MAIL_FROM_NAME : 'Student Profiling System';
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = nl2br($messageBody);
        $mail->AltBody = $messageBody;

        $mail->send();
        $_SESSION['flash_message'] = 'Message has been sent successfully.';

        // Also save the message to the database
        try {
            $senderID = $_SESSION['sturecmsstaffid'];
            $senderType = 'staff';
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
?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <title>Staff Profiling System || Search Students</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="./vendors/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="./vendors/chartist/chartist.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="./css/style(v2).css">
    <style>
      #addNoticeModal .modal-dialog {
        /* Position the modal on the right side of the screen */
        position: fixed;
        top: 20px; /* Adjust top position freely */
        right: 20px; /* Adjust right position freely */
        margin: 0;
        width: 500px; /* Or any width you prefer */
        max-width: calc(100% - 40px);
      }
      #addNoticeModal .modal-content { height: calc(70vh - 40px); }
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
                      <form method="get" class="form-inline" style="gap: 0.5rem;">
                        <input id="searchdata" type="text" name="searchdata" class="form-control"  style="width: 85%;"
                          placeholder="Search by Student ID, Name, or Skill"
                          value="<?php echo isset($_GET['searchdata']) ? htmlentities($_GET['searchdata']) : ''; ?>">
                        <button type="submit" class="btn btn-primary" id="submit">Search</button>
                      </form>
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
                            <th class="font-weight-bold">Status</th>
                            <?php if (isset($isSkillSearch) && $isSkillSearch) { ?>
                              <th class="font-weight-bold">Skill</th>
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
                                  <td data-label="Program"><?php
                                  $program_full = htmlentities($row->Program);
                                  // Use regex to find acronym in parentheses
                                  if (preg_match('/\((\w+)\)/', $program_full, $matches)) {
                                    echo $matches[1];
                                  } else {
                                    echo $program_full; // Fallback to full name if no acronym
                                  }
                                  ?></td>
                                  <td data-label="Gender"><?php echo htmlentities($row->Gender); ?></td>
                                  <td data-label="Status"><?php echo $row->Status == 1 ? 'Active' : 'Inactive'; ?></td>
                                  <td data-label="Skill"><?php echo isset($skill->name) ? htmlentities($skill->name) : ''; ?></td>
                                  <td data-label="Action">
                                    <div style="display: flex; gap: 0.5rem;">
                                      <a href="view-student.php?viewid=<?php echo htmlentities($row->sid); ?>"
                                        class="btn btn-success btn-xs">View</a>
                                      <a href="edit-student-detail.php?editid=<?php echo htmlentities($row->sid); ?>"
                                        class="btn btn-info btn-xs">Edit</a>
                                      <?php if (isset($row->Status) && $row->Status == 1): ?>
                                        <button type="button" class="btn btn-warning btn-xs message-btn" data-toggle="modal" data-target="#messageModal" data-email="<?php echo htmlentities($row->EmailAddress); ?>" data-name="<?php echo htmlentities($row->FirstName . ' ' . $row->FamilyName); ?>" data-stuid="<?php echo htmlentities($row->StuID); ?>">Message</button>
                                      <?php else: ?>
                                        <a href="manage-students.php?statusid=<?php echo htmlentities($row->sid); ?>&status=<?php echo htmlentities($row->Status); ?>" class="btn btn-secondary btn-xs">
                                          Activate
                                        </a>
                                      <?php endif; ?>
                                    </div>
                                  </td>
                                </tr>
                                <?php $cnt++;
                              }
                            } else { ?>
                              <tr class="text-center">
                                <td colspan="10" style="text-align: center; color: red;">No record found against this search
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
                                  <td data-label="Program"><?php
                                  $program_full = htmlentities($row->Program);
                                  // Use regex to find acronym in parentheses
                                  if (preg_match('/\((\w+)\)/', $program_full, $matches)) {
                                    echo $matches[1];
                                  } else {
                                    echo $program_full; // Fallback to full name if no acronym
                                  }
                                  ?></td>
                                  <td data-label="Gender"><?php echo htmlentities($row->Gender); ?></td>
                                  <td data-label="Email"><?php echo htmlentities($row->EmailAddress); ?></td>
                                  <td data-label="Action">
                                    <div style="display: flex; gap: 0.5rem;">
                                      <a href="view-student.php?viewid=<?php echo htmlentities($row->sid); ?>"
                                        class="btn btn-success btn-xs">View</a>
                                      <a href="edit-student-detail.php?editid=<?php echo htmlentities($row->sid); ?>" class="btn btn-info btn-xs">Edit</a> 
                                      <?php if (isset($row->Status) && $row->Status == 1): ?>
                                        <button type="button" class="btn btn-warning btn-xs message-btn" data-toggle="modal" data-target="#messageModal" data-email="<?php echo htmlentities($row->EmailAddress); ?>" data-name="<?php echo htmlentities($row->FirstName . ' ' . $row->FamilyName); ?>" data-stuid="<?php echo htmlentities($row->StuID); ?>">Message</button>
                                      <?php else: ?>
                                        <a href="manage-students.php?statusid=<?php echo htmlentities($row->sid); ?>&status=<?php echo htmlentities($row->Status); ?>" class="btn btn-secondary btn-xs">
                                          Activate
                                        </a>
                                      <?php endif; ?>
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
            <div style="position: fixed; bottom: 90px; right: 80px; z-index: 1030;">
              <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addNoticeModal" title="Add Notice"
                style="font-size: 2rem; line-height: 1; padding: 0.1rem 0.75rem; border-radius: 50%; width: 60px; height: 60px;">
                +
              </button>
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
    <div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="js/script.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script src="js/mention.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
          var messageButtons = document.querySelectorAll('.message-btn');
          messageButtons.forEach(function(button) {
              button.addEventListener('click', function() {
                  document.getElementById('studentEmail').value = this.getAttribute('data-email');
                  document.getElementById('studentName').innerText = this.getAttribute('data-name');
                  document.getElementById('studentStuID').value = this.getAttribute('data-stuid');
              });
          });

          // Initialize mention functionality on the notice message textarea
          const notemsgTextarea = document.getElementById('notmsg');
          if(notemsgTextarea) {
            initializeMention(notemsgTextarea, 'search.php?mention_suggest=1');
          }

          <?php if(isset($_SESSION['flash_message'])): ?> toastr.success('<?php echo $_SESSION['flash_message']; ?>'); <?php unset($_SESSION['flash_message']); endif; ?>
          <?php if(isset($_SESSION['flash_message_error'])): ?> toastr.error('<?php echo $_SESSION['flash_message_error']; ?>'); <?php unset($_SESSION['flash_message_error']); endif; ?>
      });
    </script>
  </body>

  </html>