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
      $ach_error = 'Proof image upload failed. Please try again.';
      $proof_name = '';
    }
  }

  if (empty($ach_error)) {
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
      $ach_success = true;
    } catch (Exception $e) {
      if ($dbh->inTransaction())
        $dbh->rollBack();
      $ach_error = 'Error saving achievement: ' . $e->getMessage();
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Add Achievement</title>
  <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="./css/style.css">
  <link rel="stylesheet" href="./css/style(v2).css">
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
                      <p class="card-description">Select one tag. Click a suggestion to choose it or add a custom tag.
                      </p>
                      <div style="margin-bottom:8px;">
                        <div class="input-group mb-2">
                          <input id="skillSearch" type="search" class="form-control form-control-sm"
                            placeholder="Search tags (type to filter)">
                          <div class="input-group-append">
                            <button type="button" id="clearSkillSearch"
                              class="btn btn-sm btn-outline-secondary">Clear</button>
                          </div>
                        </div>
                        <div id="skillSuggestionsList" class="d-flex flex-wrap" style="gap:6px;min-height:40px;">
                          <?php if (!empty($skillSuggestions)): ?>
                            <?php foreach ($skillSuggestions as $sugg): ?>
                              <div class="skill-item" data-name="<?php echo htmlentities($sugg['name']); ?>"
                                data-id="<?php echo htmlentities($sugg['id']); ?>"
                                data-category="<?php echo htmlentities($sugg['category']); ?>">
                                <button type="button" class="btn btn-sm btn-outline-success skill-sugg">
                                  <?php echo htmlentities($sugg['name']); ?> <small
                                    class="text-muted">(<?php echo htmlentities($sugg['category']); ?>)</small>
                                </button>
                              </div>
                            <?php endforeach; ?>
                          <?php else: ?>
                            <div class="text-muted">No suggestions available.</div>
                          <?php endif; ?>
                        </div>
                        <div class="mt-2">
                          <button type="button" id="loadMoreTags" class="btn btn-sm btn-outline-info">Load more</button>
                          <button type="button" id="addCustomTag" class="btn btn-sm btn-outline-primary"
                            data-toggle="modal" data-target="#addTagModal">Add Tag</button>
                        </div>
                      </div>
                      <div id="skillsContainer" style="margin-top:8px;"></div>
                      <input type="hidden" name="skills" id="skillsHidden">
                      <input type="hidden" name="skills_id" id="skillsIdHidden">
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
                      <small class="form-text text-muted">Upload an image as proof (jpg/png). Staff/Admin will
                        validate.</small>
                    </div>
                    <div class="mt-3">
                      <button type="submit" name="add_achievement" class="btn btn-success"
                        onclick="prepareSkills()">Submit Achievement</button>
                      <a href="update-profile.php" class="btn btn-light">Back</a>
                    </div>
                  </form>

                  <!-- Add Tag Modal -->
                  <div class="modal fade" id="addTagModal" tabindex="-1" role="dialog"
                    aria-labelledby="addTagModalLabel" aria-hidden="true">
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
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>

                </div> <!-- card-body -->
              </div> <!-- card -->
            </div> <!-- col -->
          </div> <!-- row -->
        </div> <!-- content-wrapper -->
        <?php include_once('includes/footer.php'); ?>

      </div> <!-- main-panel -->
    </div> <!-- page-body-wrapper -->
  </div> <!-- container-scroller -->


  <script src="../js/jquery-1.11.0.min.js"></script>
  <script src="../js/bootstrap.js"></script>
  <script>
    (function ($) {
      function debounce(fn, wait) { var t; return function () { var ctx = this, args = arguments; clearTimeout(t); t = setTimeout(function () { fn.apply(ctx, args); }, wait); }; }
      function escapeHtml(text) { return $('<div>').text(text).html(); }

      var allSuggestions = [];
      $('#skillSuggestionsList .skill-item').each(function () {
        allSuggestions.push({
          id: $(this).data('id'),
          name: $(this).data('name'),
          category: $(this).data('category')
        });
      });

      var pageSize = 10, currentPage = 1, currentQuery = '';

      function renderSuggestions(resetPage) {
        if (resetPage) currentPage = 1;
        var filtered = allSuggestions.filter(function (s) {
          if (!currentQuery) return true;
          return s.name.toLowerCase().indexOf(currentQuery) !== -1;
        });
        var start = (currentPage - 1) * pageSize;
        var page = filtered.slice(start, start + pageSize);
        var $list = $('#skillSuggestionsList').empty();
        if (page.length === 0) {
          $list.append('<div class="text-muted">No suggestions.</div>');
        } else {
          page.forEach(function (s) {
            var $item = $('<div class="skill-item" data-name="' + escapeHtml(s.name) + '" data-id="' + s.id + '" data-category="' + escapeHtml(s.category) + '"></div>');
            var $btn = $('<button type="button" class="btn btn-sm btn-outline-success skill-sugg">' + escapeHtml(s.name) + ' <small class="text-muted">(' + escapeHtml(s.category) + ')</small></button>');
            $item.append($btn);
            $list.append($item);
          });
        }
        $('#loadMoreTags').toggle(filtered.length > start + pageSize);
      }

      $('#skillSearch').on('input', debounce(function () {
        currentQuery = $(this).val().toLowerCase().trim();
        renderSuggestions(true);
      }, 250));

      $('#clearSkillSearch').on('click', function () { $('#skillSearch').val(''); currentQuery = ''; renderSuggestions(true); });

      $('#loadMoreTags').on('click', function () { currentPage++; renderSuggestions(false); });

      $('#skillSuggestionsList').on('click', '.skill-sugg', function () {
        var $item = $(this).closest('.skill-item');
        var name = $item.data('name');
        var id = $item.data('id');
        selectTag(name, id);
      });

      function selectTag(name, id) {
        $('#skillsContainer').empty();
        var $chip = $(
          '<span class="badge badge-pill badge-success mr-2">' + escapeHtml(name) +
          ' <a href="#" class="text-white ml-1 remove-skill" style="text-decoration:none;">&times;</a></span>'
        );
        $('#skillsContainer').append($chip);
        $('#skillsHidden').val(name);
        if (typeof id !== 'undefined') $('#skillsIdHidden').val(id); else $('#skillsIdHidden').val('');
      }

      $('#skillsContainer').on('click', '.remove-skill', function (e) { e.preventDefault(); $('#skillsContainer').empty(); $('#skillsHidden').val(''); });

      function prepareSkills() { /* hidden input already set by selectTag */ }
      window.prepareSkills = prepareSkills;

      // Make sure the Add Tag button triggers the modal even if data-toggle isn't available
      $('#addCustomTag').on('click', function () {
        // Try Bootstrap modal (v4/v3) first
        if (typeof $().modal === 'function') {
          $('#addTagModal').modal('show');
        } else {
          // fallback: show by changing display
          $('#addTagModal').show();
        }
      });

      // Add tag via AJAX
      $('#saveTagBtn').on('click', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var name = $('#tagName').val().trim();
        var category = $('#tagCategory').val();
        if (!name) { alert('Please enter a tag name'); return; }
        $btn.prop('disabled', true);
        $.post(window.location.href, { add_tag_ajax: 1, tag_name: name, tag_category: category }, function (res) {
          $btn.prop('disabled', false);
          if (res && res.success) {
            // Add to front of suggestions and re-render
            // push suggestion using tblskills id so paging/search works with server-side data
            allSuggestions.unshift({ id: res.tblskill_id || res.skill_id || res.id, name: res.name, category: res.category });
            // Hide modal properly
            if (typeof $().modal === 'function') $('#addTagModal').modal('hide'); else $('#addTagModal').hide();
            $('#tagName').val('');
            $('#tagCategory').val('Non-Academic');
            renderSuggestions(true);
            selectTag(res.name, res.tblskill_id || res.skill_id || res.id);
          } else {
            alert((res && res.msg) ? res.msg : 'Error adding tag');
          }
        }, 'json').fail(function () { $btn.prop('disabled', false); alert('Request failed'); });
      });

      // Initial render
      renderSuggestions(true);
    })(jQuery);
  </script>
</body>

</html>