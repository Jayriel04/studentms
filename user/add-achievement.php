<?php
session_start();
include('../includes/dbconnection.php');
if (!isset($_SESSION['sturecmsstuid']) || strlen($_SESSION['sturecmsstuid']) == 0) {
  header('location:logout.php');
  exit;
}
$sid = $_SESSION['sturecmsstuid'];
$ach_success = false;
$ach_error = '';

// Fetch recent skill suggestions to present as clickable tags
$skillSuggestions = array();
try {
  $srg = $dbh->prepare("SELECT name FROM skills ORDER BY created_at DESC LIMIT 50");
  $srg->execute();
  $skillSuggestions = $srg->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $ex) {
  // ignore errors - suggestions are optional
}

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
  );
  $points = isset($points_map[$ach_level]) ? $points_map[$ach_level] : 0;

  $proof_name = '';
  if (!empty($_FILES['proof']['name'])) {
    $proof_tmp = $_FILES['proof']['tmp_name'];
    $dest_dir = __DIR__ . '/../admin/images/achievements/';
    if (!is_dir($dest_dir)) mkdir($dest_dir, 0755, true);
    $proof_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['proof']['name']));
    $proof_path = $dest_dir . $proof_name;
    if (!move_uploaded_file($proof_tmp, $proof_path)) {
      $ach_error = 'Proof image upload failed. Please try again.';
      $proof_name = '';
    }
  }

  if (empty($ach_error)) {
    try {
      $ins = "INSERT INTO student_achievements (StuID, level, category, proof_image, status, points, created_at) VALUES (:sid, :level, :category, :proof, 'pending', :points, NOW())";
      $stmt = $dbh->prepare($ins);
      $stmt->bindParam(':sid', $sid, PDO::PARAM_STR);
      $stmt->bindParam(':level', $ach_level, PDO::PARAM_STR);
      $stmt->bindParam(':category', $ach_category, PDO::PARAM_STR);
      $stmt->bindParam(':proof', $proof_name, PDO::PARAM_STR);
      $stmt->bindParam(':points', $points, PDO::PARAM_INT);
      $stmt->execute();
      $achievement_id = $dbh->lastInsertId();

      if ($skills_raw !== '') {
        // Only allow one skill per submission — take the first non-empty tag
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
            $link = $dbh->prepare("INSERT INTO student_achievement_skills (achievement_id, skill_id) VALUES (:aid, :sid)");
            $link->bindParam(':aid', $achievement_id, PDO::PARAM_INT);
            $link->bindParam(':sid', $skill_id, PDO::PARAM_INT);
            $link->execute();
          }
        }
      }

      $ach_success = true;
    } catch (Exception $e) {
      $ach_error = 'Error saving achievement: ' . $e->getMessage();
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add Achievement</title>
  <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
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
            <h3 class="page-title"> Add Achievement </h3>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="../user/update-profile.php">My Profile</a></li>
                <li class="breadcrumb-item active" aria-current="page"> Add Achievement</li>
              </ol>
            </nav>
          </div>

          <div class="row">
            <div class="col-md-8 mx-auto">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title text-center mb-4">Add Achievement / Skill</h4>
                  <?php if ($ach_success): ?>
                    <div class="alert alert-success">Achievement submitted and pending validation.</div>
                  <?php endif; ?>
                  <?php if (!empty($ach_error)): ?>
                    <div class="alert alert-danger"><?php echo htmlentities($ach_error); ?></div>
                  <?php endif; ?>

                  <form id="addAchievementForm" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                      <label>Skill / Tag</label>
                      <p class="card-description">Select one tag. Click a suggestion to choose it or add a custom tag.</p>
                      <div id="skillSuggestions" style="margin-bottom:8px;">
                        <?php if (!empty($skillSuggestions)): ?>
                          <?php foreach ($skillSuggestions as $sugg): ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary skill-sugg" style="color: black;" data-name="<?php echo htmlentities($sugg); ?>"><?php echo htmlentities($sugg); ?></button>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <small class="text-muted">No suggestions available.</small>
                        <?php endif; ?>
                        <button type="button" id="addCustomTag" class="btn btn-sm btn-outline-primary">Add Tag</button>
                      </div>
                      <div id="skillsContainer" style="margin-top:8px;"></div>
                      <input type="hidden" name="skills" id="skillsHidden">
                    </div>
                    <div class="form-group">
                      <label>Category</label>
                      <select name="ach_category" class="form-control">
                        <option value="Non-Academic">Non-Academic</option>
                        <option value="Academic">Academic</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label>Achievement Level</label>
                      <select name="ach_level" class="form-control">
                        <option>School</option>
                        <option>City</option>
                        <option>Provincial</option>
                        <option>Regional</option>
                        <option>National</option>
                        <option>International</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label>Proof Image (optional)</label>
                      <input type="file" name="proof" class="form-control">
                      <small class="form-text text-muted">Upload an image as proof (jpg/png). Staff/Admin will validate.</small>
                    </div>
                    <div class="mt-3">
                      <button type="submit" name="add_achievement" class="btn btn-success" onclick="prepareSkills()">Submit Achievement</button>
                      <a href="update-profile.php" class="btn btn-light">Back</a>
                    </div>
                  </form>
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
  <script src="./js/off-canvas.js"></script>
  <script src="./js/misc.js"></script>
  <script>
    // Single-select skill tag UI
    (function () {
      var selectedSkill = null;
      var container = document.getElementById('skillsContainer');
      var hidden = document.getElementById('skillsHidden');

      function render() {
        container.innerHTML = '';
        if (selectedSkill) {
          var btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'btn btn-sm btn-info skill-chip';
          btn.style.marginRight = '6px';
          btn.style.padding = '6px';
          btn.textContent = selectedSkill + ' \u00A0\u2715';
          btn.onclick = function () { selectedSkill = null; render(); };
          container.appendChild(btn);
        }
        hidden.value = selectedSkill ? selectedSkill : '';
      }

      function selectTag(name) {
        if (!name) return;
        if (selectedSkill === name) {
          selectedSkill = null;
        } else {
          selectedSkill = name;
        }
        render();
      }

      var suggWrap = document.getElementById('skillSuggestions');
      if (suggWrap) {
        suggWrap.addEventListener('click', function (e) {
          var btn = e.target;
          if (!btn || !btn.classList) return;
          if (btn.classList.contains('skill-sugg')) {
            var name = btn.getAttribute('data-name');
            if (name) selectTag(name);
          } else if (btn.id === 'addCustomTag') {
            var custom = prompt('Enter custom tag:');
            if (custom) selectTag(custom.trim());
          }
        });
      }

      // expose for form submit
      window.prepareSkills = function () { hidden.value = selectedSkill ? selectedSkill : ''; return true; };

      // initial render
      render();
    })();
  </script>
</body>
</html>
