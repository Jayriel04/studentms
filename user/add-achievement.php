<?php
session_start();
include('../includes/dbconnection.php');
if (!isset($_SESSION['sturecmsstuid']) || strlen($_SESSION['sturecmsstuid']) == 0) {
  header('location:logout.php');
  exit;
}
$sid = $_SESSION['sturecmsstuid'];
$success_message = '';
$error_message = '';

// Fetch recent skill suggestions to present as clickable tags
$skillSuggestions = array();
try {
  // Use tblskills as the primary suggestions source (this file/DB contains the larger list)
  $srg = $dbh->prepare("SELECT id, name, category FROM tblskills ORDER BY created_at DESC LIMIT 50");
  $srg->execute();
  $rows = $srg->fetchAll(PDO::FETCH_ASSOC);
  // Normalize categories to only 'Academic' or 'Non-Academic'
  foreach ($rows as $r) {
    $cat = (isset($r['category']) && trim($r['category']) === 'Academic') ? 'Academic' : 'Non-Academic';
    $skillSuggestions[] = ['id' => $r['id'], 'name' => $r['name'], 'category' => $cat];
  }
} catch (Exception $ex) {
  // ignore errors - suggestions are optional
}

// AJAX endpoint to add a new tag into tblskills
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['add_tag_ajax'])) {
  header('Content-Type: application/json');
  $resp = ['success' => false, 'msg' => 'Invalid request'];
  $name = isset($_POST['tag_name']) ? trim($_POST['tag_name']) : '';
  $category = isset($_POST['tag_category']) ? trim($_POST['tag_category']) : '';
  if ($name === '') {
    $resp['msg'] = 'Tag name required';
    echo json_encode($resp);
    exit;
  }
  // Normalize category to Academic / Non-Academic
  $category = ($category === 'Academic') ? 'Academic' : 'Non-Academic';
  try {
    // Ensure the tag exists in the canonical `skills` table (used by foreign key)
    $chk = $dbh->prepare("SELECT id FROM skills WHERE name = :name LIMIT 1");
    $chk->bindParam(':name', $name, PDO::PARAM_STR);
    $chk->execute();
    if ($chk->rowCount() > 0) {
      $row = $chk->fetch(PDO::FETCH_ASSOC);
      $skillId = $row['id'];
    } else {
      $ins = $dbh->prepare("INSERT INTO skills (name, category, created_at) VALUES (:name, :category, NOW())");
      $ins->bindParam(':name', $name, PDO::PARAM_STR);
      $ins->bindParam(':category', $category, PDO::PARAM_STR);
      $ins->execute();
      $skillId = $dbh->lastInsertId();
    }

    // Also mirror into tblskills (so suggestions use the larger tblskills table)
    $chk2 = $dbh->prepare("SELECT id FROM tblskills WHERE name = :name LIMIT 1");
    $chk2->bindParam(':name', $name, PDO::PARAM_STR);
    $chk2->execute();
    if ($chk2->rowCount() === 0) {
      $ins2 = $dbh->prepare("INSERT INTO tblskills (name, category, created_at) VALUES (:name, :category, NOW())");
      $ins2->bindParam(':name', $name, PDO::PARAM_STR);
      $ins2->bindParam(':category', $category, PDO::PARAM_STR);
      $ins2->execute();
      $tblSkillId = $dbh->lastInsertId();
    } else {
      $r2 = $chk2->fetch(PDO::FETCH_ASSOC);
      $tblSkillId = $r2['id'];
    }

    $resp = ['success' => true, 'skill_id' => $skillId, 'tblskill_id' => $tblSkillId, 'name' => $name, 'category' => $category];
  } catch (Exception $e) {
    $resp = ['success' => false, 'msg' => 'DB error: ' . $e->getMessage()];
  }
  echo json_encode($resp);
  exit;
}

// Handle achievement submission
if (isset($_POST['add_achievement'])) {
  $skills_raw = isset($_POST['skills']) ? trim($_POST['skills']) : '';
  $ach_category = isset($_POST['ach_category']) ? $_POST['ach_category'] : 'Non-Academic';
  $ach_level = isset($_POST['ach_level']) ? $_POST['ach_level'] : 'School';

  $points_map = array(
    'International' => 100,
    'National' => 75,
    'Regional' => 50,
    'Provincial' => 40,
    'City' => 30,
    'School' => 10,
    'Hobby' => 1,
  );
  $points = isset($points_map[$ach_level]) ? $points_map[$ach_level] : 0;

  // File upload handling
  $proof_name = '';
  if (!empty($_FILES['proof']['name'])) {
    $proof_tmp = $_FILES['proof']['tmp_name'];
    $dest_dir = __DIR__ . '/../admin/images/achievements/';
    if (!is_dir($dest_dir))
      mkdir($dest_dir, 0755, true);
    $proof_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['proof']['name']));
    $proof_path = $dest_dir . $proof_name;
    if (!move_uploaded_file($proof_tmp, $proof_path)) {
      $error_message = 'Proof image upload failed. Please try again.';
      $proof_name = '';
    }
  }

  if (empty($error_message)) {
    try {
      // Start transaction to ensure FK integrity
      $dbh->beginTransaction();
      $ins = "INSERT INTO student_achievements (StuID, level, category, proof_image, status, points, created_at) VALUES (:sid, :level, :category, :proof, 'pending', :points, NOW())";
      $stmt = $dbh->prepare($ins);
      $stmt->bindParam(':sid', $sid, PDO::PARAM_STR);
      $stmt->bindParam(':level', $ach_level, PDO::PARAM_STR);
      $stmt->bindParam(':category', $ach_category, PDO::PARAM_STR);
      $stmt->bindParam(':proof', $proof_name, PDO::PARAM_STR);
      $stmt->bindParam(':points', $points, PDO::PARAM_INT);
      $stmt->execute();
      $achievement_id = $dbh->lastInsertId();

      // If a suggestion id was passed from the UI, prefer it (it's from tblskills)
      $skill_id = null;
      $skills_tbl_id = isset($_POST['skills_id']) ? trim($_POST['skills_id']) : '';
      if ($skills_tbl_id !== '') {
        // Map tblskills id -> skills table id. If skills doesn't have it, insert into skills first.
        $chk = $dbh->prepare("SELECT name, category FROM tblskills WHERE id = :id LIMIT 1");
        $chk->bindParam(':id', $skills_tbl_id, PDO::PARAM_INT);
        $chk->execute();
        $row = $chk->fetch(PDO::FETCH_ASSOC);
        if ($row) {
          $skill_name = $row['name'];
          $skill_cat = ($row['category'] === 'Academic') ? 'Academic' : 'Non-Academic';
          // ensure in skills
          $s = $dbh->prepare("SELECT id FROM skills WHERE name = :name LIMIT 1");
          $s->bindParam(':name', $skill_name, PDO::PARAM_STR);
          $s->execute();
          $skill = $s->fetch(PDO::FETCH_ASSOC);
          if ($skill && isset($skill['id'])) {
            $skill_id = $skill['id'];
          } else {
            $insk = $dbh->prepare("INSERT INTO skills (name, category, created_at) VALUES (:name, :category, NOW())");
            $insk->bindParam(':name', $skill_name, PDO::PARAM_STR);
            $insk->bindParam(':category', $skill_cat, PDO::PARAM_STR);
            $insk->execute();
            $skill_id = $dbh->lastInsertId();
          }
        }
      }

      if ($skill_id === null && $skills_raw !== '') {
        // Fallback: use free-text skill name
        $parts = array_map('trim', explode(',', $skills_raw));
        $parts = array_filter($parts);
        if (count($parts) > 0) {
          $skill_name = reset($parts);
          if ($skill_name !== '') {
            $s = $dbh->prepare("SELECT id FROM skills WHERE name = :name LIMIT 1");
            $s->bindParam(':name', $skill_name, PDO::PARAM_STR);
            $s->execute();
            $skill = $s->fetch(PDO::FETCH_OBJ);
            if ($skill && isset($skill->id)) {
              $skill_id = $skill->id;
            } else {
              $insk = $dbh->prepare("INSERT INTO skills (name, category, created_at) VALUES (:name, :category, NOW())");
              $insk->bindParam(':name', $skill_name, PDO::PARAM_STR);
              $insk->bindParam(':category', $ach_category, PDO::PARAM_STR);
              $insk->execute();
              $skill_id = $dbh->lastInsertId();
            }
          }
        }
      }

      if ($skill_id !== null) {
        $link = $dbh->prepare("INSERT INTO student_achievement_skills (achievement_id, skill_id) VALUES (:aid, :sid)");
        $link->bindParam(':aid', $achievement_id, PDO::PARAM_INT);
        $link->bindParam(':sid', $skill_id, PDO::PARAM_INT);
        $link->execute();
      }

      $dbh->commit();
      $success_message = "Achievement submitted and pending validation.";
    } catch (Exception $e) {
      if ($dbh->inTransaction())
        $dbh->rollBack();
      $error_message = 'Error saving achievement: ' . $e->getMessage();
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Student Profiling System || Add Achievement</title>
  <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
  <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="./css/style.css">
  <link rel="stylesheet" href="./css/style(v2).css">
  <link rel="stylesheet" href="css/profile.css">
  <link rel="stylesheet" href="css/toaster.css">
  <style>
    /* Make suggestion buttons and selected tag text black */
    .skill-sugg {
      color: #000 !important;
    }

    .skill-sugg small {
      color: #333 !important;
    }

    .badge-success {
      color: #000 !important;
    }

    .badge-success .remove-skill {
      color: #000 !important;
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
          <div class="row">
            <div class="col-12">
                <div class="form-card">
                    <h1 class="form-title">Add Achievement / Skill</h1>
                    <form id="addAchievementForm" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label class="form-label">Skill / Tag</label>
                            <p class="form-description">Select one tag. Click a suggestion to choose it or add a custom tag.</p>
                            
                            <div class="search-wrapper">
                                <input type="text" class="search-input" placeholder="Search tags (type to filter)" id="searchInput">
                                <button type="button" class="clear-btn" id="clearSkillSearch">Clear</button>
                            </div>

                            <div class="tags-container" id="tagsContainer">
                                <?php foreach ($skillSuggestions as $sugg): ?>
                                    <div class="tag-chip" data-id="<?php echo htmlentities($sugg['id']); ?>" data-name="<?php echo htmlentities($sugg['name']); ?>" data-category="<?php echo htmlentities($sugg['category']); ?>" style="display: none;">
                                        <span><?php echo htmlentities($sugg['name']); ?></span>
                                        <span class="tag-category"><?php echo htmlentities($sugg['category']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="tag-actions">
                                <button type="button" class="load-more-btn" id="loadMoreTags">Load more</button>
                                <button type="button" class="load-more-btn" id="showLessTags" style="display: none;">Show less</button>
                                <button type="button" class="add-tag-btn" data-toggle="modal" data-target="#addTagModal">Add Tag</button>
                            </div>
                            <input type="hidden" name="skills" id="skillsHidden">
                            <input type="hidden" name="skills_id" id="skillsIdHidden">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="ach_category" id="ach_category" class="form-select" required>
                                <option value="Non-Academic">Non-Academic</option>
                                <option value="Academic">Academic</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Achievement Level</label>
                            <select name="ach_level" class="form-select" required>
                                <option value="Hobby">Hobby</option>
                                <option value="School">School</option>
                                <option value="City">City</option>
                                <option value="Provincial">Provincial</option>
                                <option value="Regional">Regional</option>
                                <option value="National">National</option>
                                <option value="International">International</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Proof Image (optional)</label>
                            <div class="file-upload-area" onclick="document.getElementById('fileInput').click()">
                                <div class="upload-icon">🖼️</div>
                                <div class="upload-text">Click to upload proof image</div>
                                <div class="upload-hint">Upload an image as proof (jpg/png). Staff/Admin will validate.</div>
                            </div>
                            <input type="file" id="fileInput" name="proof" class="file-input" accept="image/jpeg,image/png">
                            
                            <div class="file-preview" id="filePreview">
                                <div class="file-preview-icon">📷</div>
                                <div class="file-preview-details">
                                    <div class="file-preview-name" id="fileName">image.jpg</div>
                                    <div class="file-preview-size" id="fileSize">245 KB</div>
                                </div>
                                <button type="button" class="remove-file-btn" id="removeFileBtn">×</button>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="update-profile.php" class="btn btn-back">Back</a>
                            <button type="submit" name="add_achievement" class="btn btn-submit">Submit Achievement</button>
                        </div>
                    </form>
                </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Add Tag Modal -->
    <div class="modal fade" id="addTagModal" tabindex="-1" role="dialog" aria-labelledby="addTagModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="addTagForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTagModalLabel">Add Tag</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="tagName">Tag Name</label>
                            <input type="text" id="tagName" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="tagCategory">Category</label>
                            <select id="tagCategory" class="form-control">
                                <option>Non-Academic</option>
                                <option>Academic</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="saveTagBtn" class="btn btn-primary">Add Tag</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
  </div>

  <script src="vendors/js/vendor.bundle.base.js"></script>
  <script src="js/off-canvas.js"></script>
  <script src="js/misc.js"></script>
  <script src="js/toast.js"></script>
    <?php if (!empty($success_message)): ?>
      <script>toastr.success('<?php echo addslashes($success_message); ?>');</script>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
      <script>toastr.error('<?php echo addslashes($error_message); ?>');</script>
    <?php endif; ?>
  <script src="js/add-achievement.js"></script>
</body>

</html>