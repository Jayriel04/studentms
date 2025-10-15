<?php
session_start();
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsstuid'] == 0)) {
  header('location:logout.php');
} else {
  $sid = $_SESSION['sturecmsstuid'];

  $success = false;

  // Handle form submission
  if (isset($_POST['update'])) {
    $familyname = $_POST['familyname'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $program = $_POST['program'];
    $major = $_POST['major'];
    $lrn = $_POST['lrn'];
    $dob = $_POST['dob'];
    $pob = $_POST['pob'];
    $gender = $_POST['gender'];
    if ($gender === 'Other') {
      $gender = $_POST['otherGender'];
    }
    $civilstatus = $_POST['civilstatus'];
    $religion = $_POST['religion'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $citizenship = $_POST['citizenship'];
    $fathersname = $_POST['fathersname'];
    $mothersmaidenname = $_POST['mothersmaidenname'];
    $buildinghouse = $_POST['buildinghouse'];
    $streetname = $_POST['streetname'];
    $barangay = $_POST['barangay'];
    $citymunicipality = $_POST['citymunicipality'];
    $province = $_POST['province'];
    $postalcode = $_POST['postalcode'];
    $contactnumber = $_POST['contactnumber'];
    $emailaddress = $_POST['emailaddress'];
    $emergencycontactperson = $_POST['emergencycontactperson'];
    $emergencyrelationship = $_POST['emergencyrelationship'];
    $emergencycontactnumber = $_POST['emergencycontactnumber'];
    $emergencyaddress = $_POST['emergencyaddress'];
    $category = $_POST['category'];
    $yearlevel = $_POST['yearlevel'];

    $sql_parts = [];
    $params = [];

    // Prepare SQL parts and parameters
    $sql_parts[] = "FamilyName=:familyname";
    $params[':familyname'] = $familyname;
    $sql_parts[] = "FirstName=:firstname";
    $params[':firstname'] = $firstname;
    $sql_parts[] = "MiddleName=:middlename";
    $params[':middlename'] = $middlename;
    $sql_parts[] = "Program=:program";
    $params[':program'] = $program;
    $sql_parts[] = "Major=:major";
    $params[':major'] = $major;
    $sql_parts[] = "LearnersReferenceNo=:lrn";
    $params[':lrn'] = $lrn;
    $sql_parts[] = "DOB=:dob";
    $params[':dob'] = $dob;
    $sql_parts[] = "PlaceOfBirth=:pob";
    $params[':pob'] = $pob;
    $sql_parts[] = "Gender=:gender";
    $params[':gender'] = $gender;
    $sql_parts[] = "CivilStatus=:civilstatus";
    $params[':civilstatus'] = $civilstatus;
    $sql_parts[] = "Religion=:religion";
    $params[':religion'] = $religion;
    $sql_parts[] = "Height=:height";
    $params[':height'] = $height;
    $sql_parts[] = "Weight=:weight";
    $params[':weight'] = $weight;
    $sql_parts[] = "Citizenship=:citizenship";
    $params[':citizenship'] = $citizenship;
    $sql_parts[] = "FathersName=:fathersname";
    $params[':fathersname'] = $fathersname;
    $sql_parts[] = "MothersMaidenName=:mothersmaidenname";
    $params[':mothersmaidenname'] = $mothersmaidenname;
    $sql_parts[] = "BuildingHouseNumber=:buildinghouse";
    $params[':buildinghouse'] = $buildinghouse;
    $sql_parts[] = "StreetName=:streetname";
    $params[':streetname'] = $streetname;
    $sql_parts[] = "Barangay=:barangay";
    $params[':barangay'] = $barangay;
    $sql_parts[] = "CityMunicipality=:citymunicipality";
    $params[':citymunicipality'] = $citymunicipality;
    $sql_parts[] = "Province=:province";
    $params[':province'] = $province;
    $sql_parts[] = "PostalCode=:postalcode";
    $params[':postalcode'] = $postalcode;
    $sql_parts[] = "ContactNumber=:contactnumber";
    $params[':contactnumber'] = $contactnumber;
    $sql_parts[] = "EmailAddress=:emailaddress";
    $params[':emailaddress'] = $emailaddress;
    $sql_parts[] = "EmergencyContactPerson=:emergencycontactperson";
    $params[':emergencycontactperson'] = $emergencycontactperson;
    $sql_parts[] = "EmergencyRelationship=:emergencyrelationship";
    $params[':emergencyrelationship'] = $emergencyrelationship;
    $sql_parts[] = "EmergencyContactNumber=:emergencycontactnumber";
    $params[':emergencycontactnumber'] = $emergencycontactnumber;
    $sql_parts[] = "EmergencyAddress=:emergencyaddress";
    $params[':emergencyaddress'] = $emergencyaddress;
    $sql_parts[] = "Category=:category";
    $params[':category'] = $category;
    $sql_parts[] = "YearLevel=:yearlevel";
    $params[':yearlevel'] = $yearlevel;

    // Handle profile image upload
    $profilepic = $_FILES['profilepic']['name'];
    if (!empty($profilepic)) {
      $profilepic_tmp = $_FILES['profilepic']['tmp_name'];
      $profilepic_folder = "../admin/images/" . $profilepic;
      if (move_uploaded_file($profilepic_tmp, $profilepic_folder)) {
        $sql_parts[] = "Image=:image";
        $params[':image'] = $profilepic;
      } else {
        echo "<script>if(window.showToast) showToast('Profile picture upload failed. Please try again','danger');</script>";
      }
    }

    // Handle password update
    if (!empty($_POST['password'])) {
      $sql_parts[] = "Password=:password";
      // Note: It is highly recommended to hash passwords.
      // $params[':password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
      $params[':password'] = $_POST['password'];
    }

    $sql = "UPDATE tblstudent SET " . implode(', ', $sql_parts) . " WHERE StuID=:sid";
    $params[':sid'] = $sid;

    $query = $dbh->prepare($sql);
    if ($query->execute($params)) {
      $success = true;
    } else {
      echo "<script>if(window.showToast) showToast('Something went wrong. Please try again','danger');</script>";
    }
  }

  // Handle adding achievements (skills tags + proof upload)
  if (isset($_POST['add_achievement'])) {
    $skills_raw = isset($_POST['skills']) ? trim($_POST['skills']) : '';
    $ach_category = isset($_POST['ach_category']) ? $_POST['ach_category'] : 'Non-Academic';
    $ach_level = isset($_POST['ach_level']) ? $_POST['ach_level'] : 'School';

    // Points mapping - adjust values as desired
    $points_map = array(
      'International' => 100,
      'National' => 75,
      'Regional' => 50,
      'Provincial' => 40,
      'City' => 30,
      'School' => 10,
    );
    $points = isset($points_map[$ach_level]) ? $points_map[$ach_level] : 0;

    // Handle proof image upload
    $proof_name = '';
    if (!empty($_FILES['proof']['name'])) {
      $proof_tmp = $_FILES['proof']['tmp_name'];
      $dest_dir = __DIR__ . '/../admin/images/achievements/';
      if (!is_dir($dest_dir)) {
        mkdir($dest_dir, 0755, true);
      }
      $proof_name = time() . '_' . basename($_FILES['proof']['name']);
      $proof_path = $dest_dir . $proof_name;
      if (!move_uploaded_file($proof_tmp, $proof_path)) {
        echo "<script>if(window.showToast) showToast('Proof image upload failed. Please try again','danger');</script>";
        $proof_name = '';
      }
    }

    try {
      // Insert achievement
      $ins = "INSERT INTO student_achievements (StuID, level, category, proof_image, status, points, created_at) VALUES (:sid, :level, :category, :proof, 'pending', :points, NOW())";
      $stmt = $dbh->prepare($ins);
      $stmt->bindParam(':sid', $sid, PDO::PARAM_STR);
      $stmt->bindParam(':level', $ach_level, PDO::PARAM_STR);
      $stmt->bindParam(':category', $ach_category, PDO::PARAM_STR);
      $stmt->bindParam(':proof', $proof_name, PDO::PARAM_STR);
      $stmt->bindParam(':points', $points, PDO::PARAM_INT);
      $stmt->execute();
      $achievement_id = $dbh->lastInsertId();

      // Process skills (custom tags). Expect comma-separated names.
      if ($skills_raw !== '') {
        $skills = array_filter(array_map('trim', explode(',', $skills_raw)));
        $skills = array_unique($skills);
        foreach ($skills as $skill_name) {
          if ($skill_name === '')
            continue;
          // check if exists
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
          // link
          $link = $dbh->prepare("INSERT INTO student_achievement_skills (achievement_id, skill_id) VALUES (:aid, :sid)");
          $link->bindParam(':aid', $achievement_id, PDO::PARAM_INT);
          $link->bindParam(':sid', $skill_id, PDO::PARAM_INT);
          $link->execute();
        }
      }

      $ach_success = true;
    } catch (Exception $e) {
      echo "<script>if(window.showToast) showToast('Error saving achievement: " . addslashes($e->getMessage()) . "','danger');</script>";
    }
  }

  // Fetch profile data
  $sql = "SELECT * FROM tblstudent WHERE StuID = :sid";
  $query = $dbh->prepare($sql);
  $query->bindParam(':sid', $sid, PDO::PARAM_STR);
  $query->execute();
  $row = $query->fetch(PDO::FETCH_OBJ);
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <title>Student Management System | Update Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="./css/style.css">
  </head>

  <body>
    <div class="container-scroller">
      <?php include_once('includes/header.php'); ?>
      <div class="container-fluid page-body-wrapper">
        <?php include_once('includes/sidebar.php'); ?>
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-12 stretch-card grid-margin">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Update My Profile</h4>
                    <?php if ($success) { ?>
                      <div class="alert alert-success" role="alert">
                        Profile updated successfully!
                      </div>
                    <?php } ?>
                    <form method="post" enctype="multipart/form-data">
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Family Name</label>
                            <input type="text" name="familyname" class="form-control"
                              value="<?php echo isset($row->FamilyName) ? htmlentities($row->FamilyName) : ''; ?>"
                              required>
                          </div>
                          <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="firstname" class="form-control"
                              value="<?php echo isset($row->FirstName) ? htmlentities($row->FirstName) : ''; ?>" required>
                          </div>
                          <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" name="middlename" class="form-control"
                              value="<?php echo isset($row->MiddleName) ? htmlentities($row->MiddleName) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Program</label>
                            <input type="text" name="program" class="form-control"
                              value="<?php echo isset($row->Program) ? htmlentities($row->Program) : ''; ?>" required>
                          </div>
                          <div class="form-group">
                            <label>Major</label>
                            <input type="text" name="major" class="form-control"
                              value="<?php echo isset($row->Major) ? htmlentities($row->Major) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Learners Reference No</label>
                            <input type="text" name="lrn" class="form-control"
                              value="<?php echo isset($row->LearnersReferenceNo) ? htmlentities($row->LearnersReferenceNo) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="dob" class="form-control"
                              value="<?php echo isset($row->DOB) ? htmlentities($row->DOB) : ''; ?>" required>
                          </div>
                          <div class="form-group">
                            <label>Place of Birth</label>
                            <input type="text" name="pob" class="form-control"
                              value="<?php echo isset($row->PlaceOfBirth) ? htmlentities($row->PlaceOfBirth) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Gender</label>
                            <select name="gender" id="gender" class="form-control" required
                              onchange="toggleOtherGenderInput()">
                              <option value="Male" <?php if (isset($row->Gender) && $row->Gender == 'Male')
                                echo 'selected'; ?>>Male</option>
                              <option value="Female" <?php if (isset($row->Gender) && $row->Gender == 'Female')
                                echo 'selected'; ?>>Female</option>
                              <option value="Other" <?php if (isset($row->Gender) && !in_array($row->Gender, ['Male', 'Female']))
                                echo 'selected'; ?>>Other</option>
                            </select>
                          </div>
                          <div class="form-group" id="otherGenderInput" style="display: none;">
                            <label>Please Specify</label>
                            <input type="text" name="otherGender" id="otherGender" class="form-control" value="<?php if (isset($row->Gender) && !in_array($row->Gender, ['Male', 'Female']))
                              echo htmlentities($row->Gender); ?>">
                          </div>
                          <div class="form-group">
                            <label>Civil Status</label>
                            <input type="text" name="civilstatus" class="form-control"
                              value="<?php echo isset($row->CivilStatus) ? htmlentities($row->CivilStatus) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Religion</label>
                            <input type="text" name="religion" class="form-control"
                              value="<?php echo isset($row->Religion) ? htmlentities($row->Religion) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Height</label>
                            <input type="text" name="height" class="form-control"
                              value="<?php echo isset($row->Height) ? htmlentities($row->Height) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Weight</label>
                            <input type="text" name="weight" class="form-control"
                              value="<?php echo isset($row->Weight) ? htmlentities($row->Weight) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Citizenship</label>
                            <input type="text" name="citizenship" class="form-control"
                              value="<?php echo isset($row->Citizenship) ? htmlentities($row->Citizenship) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Profile Image</label>
                            <br>
                            <?php if (isset($row->Image) && $row->Image != ''): ?>
                              <img src="../admin/images/<?php echo $row->Image; ?>" width="100" height="100">
                            <?php else: ?>
                              <p>No image available</p>
                            <?php endif; ?>
                          </div>
                          <div class="form-group">
                            <label>Update Profile Image</label>
                            <input type="file" name="profilepic" class="form-control">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Father's Name</label>
                            <input type="text" name="fathersname" class="form-control"
                              value="<?php echo isset($row->FathersName) ? htmlentities($row->FathersName) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Mother's Maiden Name</label>
                            <input type="text" name="mothersmaidenname" class="form-control"
                              value="<?php echo isset($row->MothersMaidenName) ? htmlentities($row->MothersMaidenName) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Building/House Number</label>
                            <input type="text" name="buildinghouse" class="form-control"
                              value="<?php echo isset($row->BuildingHouseNumber) ? htmlentities($row->BuildingHouseNumber) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Street Name</label>
                            <input type="text" name="streetname" class="form-control"
                              value="<?php echo isset($row->StreetName) ? htmlentities($row->StreetName) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Barangay</label>
                            <input type="text" name="barangay" class="form-control"
                              value="<?php echo isset($row->Barangay) ? htmlentities($row->Barangay) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>City/Municipality</label>
                            <input type="text" name="citymunicipality" class="form-control"
                              value="<?php echo isset($row->CityMunicipality) ? htmlentities($row->CityMunicipality) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Province</label>
                            <input type="text" name="province" class="form-control"
                              value="<?php echo isset($row->Province) ? htmlentities($row->Province) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Postal Code</label>
                            <input type="text" name="postalcode" class="form-control"
                              value="<?php echo isset($row->PostalCode) ? htmlentities($row->PostalCode) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="contactnumber" class="form-control"
                              value="<?php echo isset($row->ContactNumber) ? htmlentities($row->ContactNumber) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="emailaddress" class="form-control"
                              value="<?php echo isset($row->EmailAddress) ? htmlentities($row->EmailAddress) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Emergency Contact Person</label>
                            <input type="text" name="emergencycontactperson" class="form-control"
                              value="<?php echo isset($row->EmergencyContactPerson) ? htmlentities($row->EmergencyContactPerson) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Emergency Relationship</label>
                            <input type="text" name="emergencyrelationship" class="form-control"
                              value="<?php echo isset($row->EmergencyRelationship) ? htmlentities($row->EmergencyRelationship) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Emergency Contact Number</label>
                            <input type="text" name="emergencycontactnumber" class="form-control"
                              value="<?php echo isset($row->EmergencyContactNumber) ? htmlentities($row->EmergencyContactNumber) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label>Emergency Address</label>
                            <input type="text" name="emergencyaddress" class="form-control"
                              value="<?php echo isset($row->EmergencyAddress) ? htmlentities($row->EmergencyAddress) : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="category">Category</label>
                            <select name="category" class="form-control" required>
                              <option value="">Select Category</option>
                              <option value="New Freshman">New Freshman</option>
                              <option value="Continuing/Returnee">Continuing/Returnee</option>
                              <option value="Shiftee">Shiftee</option>
                              <option value="Second Degree">Second Degree</option>
                              <option value="Regular">Regular</option>
                              <option value="Irregular">Irregular</option>
                            </select>
                          </div>
                          <div class="form-group">
                            <label for="yearlevel">Year Level</label>
                            <select name="yearlevel" class="form-control" required>
                              <option value="">Select Year Level</option>
                              <option value="1">1</option>
                              <option value="2">2</option>
                              <option value="3">3</option>
                              <option value="4">4</option>
                            </select>
                          </div>
                          <div class="form-group" style="position: relative;">
                            <label>New Password</label>
                            <input type="password" name="password" id="password" class="form-control">
                            <i class="icon-eye" id="togglePassword"
                              style="position: absolute; right: 15px; top: 70%; transform: translateY(-50%); cursor: pointer;"></i>
                          </div>
                        </div>
                      </div>
                      <button type="submit" name="update" class="btn btn-primary">Update Profile</button>
                      <a href="student-profile.php" class="btn btn-light">Back</a>
                      <!-- Link to Add Achievement page -->
                      <a href="add-achievement.php" class="btn btn-info">Add Achievement / Skill</a>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Achievement is now a separate page: user/add-achievement.php -->
          <?php include_once('includes/footer.php'); ?>
        </div>
      </div>
    </div>
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script src="js/script.js"></script>
    <script>
      function toggleOtherGenderInput() {
        var genderSelect = document.getElementById("gender");
        var otherGenderInput = document.getElementById("otherGenderInput");
        if (genderSelect.value === "Other") {
          otherGenderInput.style.display = "block";
        } else {
          otherGenderInput.style.display = "none";
        }
      }
      // Trigger on page load to set initial state
      document.addEventListener('DOMContentLoaded', function () {
        toggleOtherGenderInput();
      });
    </script>
    <script>
      // Tag input management
      (function () {
        var skills = [];
        var input = document.getElementById('skillInput');
        var container = document.getElementById('skillsContainer');
        var hidden = document.getElementById('skillsHidden');

        function render() {
          container.innerHTML = '';
          skills.forEach(function (s, idx) {
            var span = document.createElement('span');
            span.className = 'badge badge-info';
            span.style.marginRight = '6px';
            span.style.padding = '6px';
            span.textContent = s;
            var rm = document.createElement('a');
            rm.href = '#';
            rm.style.marginLeft = '6px';
            rm.style.color = '#fff';
            rm.innerHTML = '&times;';
            rm.onclick = function (e) {
              e.preventDefault();
              skills.splice(idx, 1);
              render();
            };
            span.appendChild(rm);
            container.appendChild(span);
          });
          hidden.value = skills.join(',');
        }

        function addFromInput(val) {
          if (!val) return;
          val.split(',').forEach(function (part) {
            var t = part.trim();
            if (t && skills.indexOf(t) === -1) skills.push(t);
          });
          render();
        }

        if (input) {
          input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
              e.preventDefault();
              addFromInput(input.value);
              input.value = '';
            }
            if (e.key === ',') {
              e.preventDefault();
              addFromInput(input.value);
              input.value = '';
            }
          });
          input.addEventListener('blur', function () {
            addFromInput(input.value);
            input.value = '';
          });
          input.addEventListener('paste', function (e) {
            var pasted = (e.clipboardData || window.clipboardData).getData('text');
            addFromInput(pasted);
            e.preventDefault();
          });
        }

        window.prepareSkills = function () {
          // ensure hidden is up-to-date before form submit
          hidden.value = skills.join(',');
          return true;
        };
      })();
    </script>
    <script>
      // Fallback: ensure modal opens when button clicked (for projects using older jQuery/bootstrap bindings)
      (function () {
        var btn = document.getElementById('openAchModalBtn');
        if (btn && window.jQuery) {
          btn.addEventListener('click', function (e) {
            e.preventDefault();
            jQuery('#achModal').modal('show');
          });
        }
      })();
    </script>
    <script>
      // Ensure achievement modal form submits reliably
      (function () {
        var form = document.getElementById('achForm');
        var submit = document.getElementById('achSubmitBtn');
        if (form && submit) {
          submit.addEventListener('click', function (e) {
            // call prepareSkills to populate hidden input
            try { window.prepareSkills(); } catch (ex) { /* ignore */ }
            // force submit to bypass any interfering handlers
            // allow default for normal submission, but if default prevented, submit programmatically
            setTimeout(function () {
              if (document.activeElement && document.activeElement === submit) {
                // nothing
              }
              try {
                form.submit();
              } catch (err) {
                // fallback: trigger native click on submit
                submit.click();
              }
            }, 10);
          }, false);
        }
      })();
    </script>
  </body>

  </html>
<?php } ?>