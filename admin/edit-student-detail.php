<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid'] == 0)) {
  header('location:logout.php');
} else {
  $success_message = '';
  $error_message = '';
  if (isset($_POST['submit'])) {
    $stuid = $_POST['stuid'];

    // Server-side validation for student ID format
    if (!preg_match('/^\d{3} - \d{5}$/', $stuid)) {
      $error_message = 'Invalid Student ID format. Please use the format: 222 - 08410.';
    } else {
      $familyname = $_POST['familyname'];
      $firstname = $_POST['firstname'];
      $middlename = $_POST['middlename'];
      $program = $_POST['program'];
      $major = $_POST['major'];
      $lrn = $_POST['lrn'];
      $dob = $_POST['dob'];
      $placeofbirth = $_POST['placeofbirth'];
      $gender = $_POST['gender'];
      if ($gender === "Other") {
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
      $password = $_POST['password'];
      $eid = $_GET['editid'];

      // Check if the new Student ID already exists
      $checkSql = "SELECT COUNT(*) FROM tblstudent WHERE StuID = :stuid AND ID != :eid";
      $checkQuery = $dbh->prepare($checkSql);
      $checkQuery->bindParam(':stuid', $stuid, PDO::PARAM_STR);
      $checkQuery->bindParam(':eid', $eid, PDO::PARAM_STR);
      $checkQuery->execute();
      $isDuplicate = $checkQuery->fetchColumn();

      if ($isDuplicate > 0) {
        $error_message = "This Student ID already exists. Please choose a different one.";
      } else {
        $sql = "UPDATE tblstudent SET 
         StuID=:stuid, 
        FamilyName=:familyname, 
        FirstName=:firstname, 
        MiddleName=:middlename, 
        Program=:program, 
        Major=:major, 
        LearnersReferenceNo=:lrn, 
        DOB=:dob, 
        PlaceOfBirth=:placeofbirth, 
        Gender=:gender, 
        CivilStatus=:civilstatus, 
        Religion=:religion, 
        Height=:height, 
        Weight=:weight, 
        Citizenship=:citizenship, 
        FathersName=:fathersname, 
        MothersMaidenName=:mothersmaidenname, 
        BuildingHouseNumber=:buildinghouse, 
        StreetName=:streetname, 
        Barangay=:barangay, 
        CityMunicipality=:citymunicipality, 
        Province=:province, 
        PostalCode=:postalcode, 
        ContactNumber=:contactnumber, 
        EmailAddress=:emailaddress, 
        EmergencyContactPerson=:emergencycontactperson, 
        EmergencyRelationship=:emergencyrelationship, 
        EmergencyContactNumber=:emergencycontactnumber, 
        EmergencyAddress=:emergencyaddress, 
        Category=:category, 
        YearLevel=:yearlevel";
      if (isset($password) && $password != '') {
        $sql .= ", Password=:password";
      }
      $sql .= " WHERE ID=:eid";


      $query = $dbh->prepare($sql);
      if (isset($password) && $password != '') {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query->bindParam(':password', $hashed_password, PDO::PARAM_STR);
      }
      $query->bindParam(':stuid', $stuid, PDO::PARAM_STR);
      $query->bindParam(':familyname', $familyname, PDO::PARAM_STR);
      $query->bindParam(':firstname', $firstname, PDO::PARAM_STR);
      $query->bindParam(':middlename', $middlename, PDO::PARAM_STR);
      $query->bindParam(':program', $program, PDO::PARAM_STR);
      $query->bindParam(':major', $major, PDO::PARAM_STR);
      $query->bindParam(':lrn', $lrn, PDO::PARAM_STR);
      $query->bindParam(':dob', $dob, PDO::PARAM_STR);
      $query->bindParam(':placeofbirth', $placeofbirth, PDO::PARAM_STR);
      $query->bindParam(':gender', $gender, PDO::PARAM_STR);
      $query->bindParam(':civilstatus', $civilstatus, PDO::PARAM_STR);
      $query->bindParam(':religion', $religion, PDO::PARAM_STR);
      $query->bindParam(':height', $height, PDO::PARAM_STR);
      $query->bindParam(':weight', $weight, PDO::PARAM_STR);
      $query->bindParam(':citizenship', $citizenship, PDO::PARAM_STR);
      $query->bindParam(':fathersname', $fathersname, PDO::PARAM_STR);
      $query->bindParam(':mothersmaidenname', $mothersmaidenname, PDO::PARAM_STR);
      $query->bindParam(':buildinghouse', $buildinghouse, PDO::PARAM_STR);
      $query->bindParam(':streetname', $streetname, PDO::PARAM_STR); // New binding
      $query->bindParam(':barangay', $barangay, PDO::PARAM_STR);
      $query->bindParam(':citymunicipality', $citymunicipality, PDO::PARAM_STR);
      $query->bindParam(':province', $province, PDO::PARAM_STR);
      $query->bindParam(':postalcode', $postalcode, PDO::PARAM_STR);
      $query->bindParam(':contactnumber', $contactnumber, PDO::PARAM_STR);
      $query->bindParam(':emailaddress', $emailaddress, PDO::PARAM_STR);
      $query->bindParam(':emergencycontactperson', $emergencycontactperson, PDO::PARAM_STR);
      $query->bindParam(':emergencyrelationship', $emergencyrelationship, PDO::PARAM_STR);
      $query->bindParam(':emergencycontactnumber', $emergencycontactnumber, PDO::PARAM_STR);
      $query->bindParam(':emergencyaddress', $emergencyaddress, PDO::PARAM_STR);
      $query->bindParam(':category', $category, PDO::PARAM_STR);
      $query->bindParam(':yearlevel', $yearlevel, PDO::PARAM_STR);
      $query->bindParam(':eid', $eid, PDO::PARAM_STR);
      $query->execute();

        $_SESSION['flash_message'] = "Student details updated successfully.";
        header('Location: manage-students.php');
        exit;
      }
    }
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <title>Student Profiling System || Update Students</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css" />
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
              <h3 class="page-title"> Update Students </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page"> Update Students Details</li>
                </ol>
              </nav>
            </div>
            <div class="row">
              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title" style="text-align: center;">Update Students Details</h4>
                    <hr />
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success"><?php echo htmlentities($success_message); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlentities($error_message); ?>
                        </div>
                    <?php endif; ?>
                    <form class="forms-sample" method="post">
                      <?php
                      $eid = $_GET['editid'];
                      $sql = "SELECT * FROM tblstudent WHERE ID=:eid";
                      $query = $dbh->prepare($sql);
                      $query->bindParam(':eid', $eid, PDO::PARAM_STR);
                      $query->execute();
                      $results = $query->fetchAll(PDO::FETCH_OBJ);
                      if ($query->rowCount() > 0) {
                        foreach ($results as $row) { ?>
                          <div class="row">
                            <div class="col-6">
                              <h4>Student Details</h4>
                              <hr />
                              <div class="form-group">
                                <label>Student ID</label> <input type="text" name="stuid"
                                  value="<?php echo htmlentities($row->StuID); ?>" class="form-control" required style="text-transform: capitalize;"
                                  placeholder="e.g., 222 - 08410" pattern="\d{3} - \d{5}"
                                  title="The format must be: 222 - 08410">
                              </div>
                              <div class="form-group">
                                <label>Family Name</label>
                                <input type="text" name="familyname" value="<?php echo htmlentities($row->FamilyName); ?>"
                                  class="form-control" required style="text-transform: capitalize;">
                              </div>
                              <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="firstname" value="<?php echo htmlentities($row->FirstName); ?>"
                                  class="form-control" required style="text-transform: capitalize;">
                              </div>
                              <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" name="middlename" value="<?php echo htmlentities($row->MiddleName); ?>"
                                  class="form-control" style="text-transform: capitalize;">
                              </div>
                              <div class="form-group">
                                <label>Program</label>
                                <select name="program" id="program" class="form-control" required onchange="updateMajors()">
                                  <option value="">Select Program</option>
                                  <option value="Bachelor of Elementary Education (BEEd)" <?php if ($row->Program == 'Bachelor of Elementary Education (BEEd)') echo 'selected'; ?>>Bachelor of Elementary Education (BEEd)</option>
                                  <option value="Bachelor of Secondary Education (BSEd)" <?php if ($row->Program == 'Bachelor of Secondary Education (BSEd)') echo 'selected'; ?>>Bachelor of Secondary Education (BSEd)</option>
                                  <option value="Bachelor of Science in Business Administration (BSBA)" <?php if ($row->Program == 'Bachelor of Science in Business Administration (BSBA)') echo 'selected'; ?>>Bachelor of Science in Business Administration (BSBA)</option>
                                  <option value="Bachelor of Industrial Technology (BindTech)" <?php if ($row->Program == 'Bachelor of Industrial Technology (BindTech)') echo 'selected'; ?>>Bachelor of Industrial Technology (BindTech)</option>
                                  <option value="Bachelor of Science in Information Technology (BSIT)" <?php if ($row->Program == 'Bachelor of Science in Information Technology (BSIT)') echo 'selected'; ?>>Bachelor of Science in Information Technology (BSIT)</option>
                                  <?php if (!in_array($row->Program, [
                                      'Bachelor of Elementary Education (BEEd)',
                                      'Bachelor of Secondary Education (BSEd)',
                                      'Bachelor of Science in Business Administration (BSBA)',
                                      'Bachelor of Industrial Technology (BindTech)',
                                      'Bachelor of Science in Information Technology (BSIT)'
                                  ]) && !empty($row->Program)): ?>
                                    <option value="<?php echo htmlentities($row->Program); ?>" selected><?php echo htmlentities($row->Program); ?></option>
                                  <?php endif; ?>
                                </select>
                              </div>
                              <div class="form-group">
                                <label>Major</label>
                                <select name="major" id="major" class="form-control" style="text-transform: capitalize;">
                                     <option value="">Select Major</option>
                                 </select>
                              </div>
                              <div class="form-group">
                                <label>Learner's Reference No.</label>
                                <input type="text" name="lrn" value="<?php echo htmlentities($row->LearnersReferenceNo); ?>"
                                  class="form-control" style="text-transform: capitalize;">
                              </div>
                              <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" name="dob" value="<?php echo htmlentities($row->DOB); ?>"
                                  class="form-control" required>
                              </div>
                              <div class="form-group">
                                <label>Place of Birth</label>
                                <input type="text" name="placeofbirth" value="<?php echo htmlentities($row->PlaceOfBirth); ?>"
                                  class="form-control" style="text-transform: capitalize;">
                              </div>
                              <div class="form-group">
                                <label>Gender</label>
                                <select name="gender" id="gender" class="form-control" required
                                  onchange="toggleOtherGenderInput()">
                                  <option value="Male" <?php if ($row->Gender == 'Male')
                                    echo 'selected'; ?>>Male</option>
                                  <option value="Female" <?php if ($row->Gender == 'Female')
                                    echo 'selected'; ?>>Female</option>
                                  <option value="Other" <?php if (!in_array($row->Gender, ['Male', 'Female']))
                                    echo 'selected'; ?>>Other</option>
                                </select>
                              </div>
                              <div class="form-group" id="otherGenderInput"
                                style="display: <?php echo (!in_array($row->Gender, ['Male', 'Female']) && $row->Gender != '') ? 'block' : 'none'; ?>;">
                                <label>Please Specify</label>
                                <input type="text" name="otherGender" id="otherGender" class="form-control" value="<?php if (!in_array($row->Gender, ['Male', 'Female']))
                                  echo htmlentities($row->Gender); ?>" style="text-transform: capitalize;">
                              </div>
                              <div class="form-group">
                                <label>Civil Status</label>
                                <select name="civilstatus" class="form-control">
                                  <option value="">Select Status</option>
                                  <option value="Single" <?php if ($row->CivilStatus == 'Single') echo 'selected'; ?>>Single</option>
                                  <option value="Married" <?php if ($row->CivilStatus == 'Married') echo 'selected'; ?>>Married</option>
                                  <option value="Divorced" <?php if ($row->CivilStatus == 'Divorced') echo 'selected'; ?>>Divorced</option>
                                  <option value="Widowed" <?php if ($row->CivilStatus == 'Widowed') echo 'selected'; ?>>Widowed</option>
                                  <option value="Separated" <?php if ($row->CivilStatus == 'Separated') echo 'selected'; ?>>Separated</option>
                                  <?php
                                  $standard_statuses = ['Single', 'Married', 'Divorced', 'Widowed', 'Separated'];
                                  if (!in_array($row->CivilStatus, $standard_statuses) && !empty($row->CivilStatus)): ?>
                                    <option value="<?php echo htmlentities($row->CivilStatus); ?>" selected><?php echo htmlentities($row->CivilStatus); ?> (Custom)</option>
                                  <?php endif; ?>
                                </select>
                              </div>
                              <div class="form-group">
                                <label>Religion</label>
                                <input type="text" name="religion" value="<?php echo htmlentities($row->Religion); ?>"
                                  class="form-control" style="text-transform: capitalize;">
                              </div>
                              <div class="form-group">
                                <label>Height (cm)</label>
                                <input type="text" name="height" value="<?php echo htmlentities($row->Height); ?>"
                                  class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Weight (kg)</label>
                                <input type="text" name="weight" value="<?php echo htmlentities($row->Weight); ?>"
                                  class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Citizenship</label>
                                <input type="text" name="citizenship" value="<?php echo htmlentities($row->Citizenship); ?>"
                                  class="form-control" style="text-transform: capitalize;">
                              </div>
                              <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" value="" class="form-control">
                              </div>
                            </div>
                            <div class="col-6">
                              <h4>Contact Details</h4>
                              <hr />
                              <div class="form-group">
                                <label>Father's Name</label>
                                <input type="text" name="fathersname" value="<?php echo htmlentities($row->FathersName); ?>"
                                  class="form-control" style="text-transform: capitalize;">
                              </div>
                              <div class="form-group">
                                <label>Mother's Maiden Name</label>
                                <input type="text" name="mothersmaidenname"
                                  value="<?php echo htmlentities($row->MothersMaidenName); ?>" class="form-control" style="text-transform: capitalize;">
                              </div>
                              <div class="form-group">
                                <label>Building/House Number</label>
                                <input type="text" name="buildinghouse"
                                  value="<?php echo htmlentities($row->BuildingHouseNumber); ?>" class="form-control" style="text-transform: capitalize;">
                              </div>
                              <div class="form-group">
                                <label>Street Name</label>
                                <input type="text" name="streetname" value="<?php echo htmlentities($row->StreetName); ?>"
                                  class="form-control" style="text-transform: capitalize;">
                              </div>
                              <div class="form-group">
                                <label>Barangay</label>
                                <input type="text" name="barangay" value="<?php echo htmlentities($row->Barangay); ?>"
                                  class="form-control" style="text-transform: capitalize;">
                              </div>
                              <div class="form-group">
                                <label>City/Municipality</label>
                                <div id="city-municipality-container">
                                   <input type="text" name="citymunicipality" id="citymunicipality-text" value="<?php echo htmlentities($row->CityMunicipality); ?>" class="form-control" style="text-transform: capitalize;">
                                </div>
                              </div>
                              <div class="form-group">
                                <label>Province</label>
                                <select name="province" class="form-control province-select" style="text-transform: capitalize;">
                                  <option value="">Select Province</option>
                                  <?php
                                  $provincesJson = file_get_contents('../data/provinces.json');
                                  $provinces = json_decode($provincesJson, true);
                                  if (is_array($provinces)) {
                                    $current_province = htmlentities($row->Province);
                                    foreach ($provinces as $province) {
                                        $selected = ($current_province == $province) ? 'selected' : '';
                                        echo "<option value=\"" . htmlspecialchars($province) . "\" $selected>" . htmlspecialchars($province) . "</option>";
                                    }
                                    // Add the current value as an option if it's not in the standard list
                                    if (!in_array($current_province, $provinces) && !empty($current_province)) {
                                        echo "<option value=\"$current_province\" selected>$current_province (Custom)</option>";
                                    }
                                  }
                                  ?>
                                </select>
                              </div>
                              <div class="form-group">
                                <label>Postal Code</label>
                                <input type="text" name="postalcode" value="<?php echo htmlentities($row->PostalCode); ?>"
                                  class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Contact Number</label>
                                <input type="text" name="contactnumber"
                                  value="<?php echo htmlentities($row->ContactNumber); ?>" class="form-control" required>
                              </div>
                              <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="emailaddress"
                                  value="<?php echo htmlentities($row->EmailAddress); ?>" class="form-control" required>
                              </div>
                              <div class="form-group">
                                <label>Emergency Contact Person</label>
                                <input type="text" name="emergencycontactperson"
                                  value="<?php echo htmlentities($row->EmergencyContactPerson); ?>" class="form-control" style="text-transform: capitalize;">
                              </div>
                              <div class="form-group">
                                <label>Emergency Relationship</label>
                                <input type="text" name="emergencyrelationship"
                                  value="<?php echo htmlentities($row->EmergencyRelationship); ?>" class="form-control" style="text-transform: capitalize;">
                              </div>
                              <div class="form-group">
                                <label>Emergency Contact Number</label>
                                <input type="text" name="emergencycontactnumber"
                                  value="<?php echo htmlentities($row->EmergencyContactNumber); ?>" class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Emergency Address</label>
                                <textarea name="emergencyaddress"
                                  class="form-control" style="text-transform: capitalize;"><?php echo htmlentities($row->EmergencyAddress); ?></textarea>
                              </div>
                              <div class="form-group">
                                <label>Category</label>
                                <select name="category" class="form-control" required>
                                  <option value="<?php echo htmlentities($row->Category); ?>">
                                    <?php echo htmlentities($row->Category); ?>
                                  </option>
                                  <option value="New Freshman">New Freshman</option>
                                  <option value="Continuing/Returnee">Continuing/Returnee</option>
                                  <option value="Shiftee">Shiftee</option>
                                  <option value="Second Degree">Second Degree</option>
                                  <option value="Regular">Regular</option>
                                  <option value="Irregular">Irregular</option>
                                </select>
                              </div>
                              <div class="form-group">
                                <label>Year Level</label>
                                <select name="yearlevel" class="form-control" required>
                                  <option value="<?php echo htmlentities($row->YearLevel); ?>">
                                    <?php echo htmlentities($row->YearLevel); ?>
                                  </option>
                                  <option value="1">1</option>
                                  <option value="2">2</option>
                                  <option value="3">3</option>
                                  <option value="4">4</option>
                                </select>
                              </div>
                            </div>
                          </div>
                          <button type="submit" class="btn btn-primary" name="submit">Update</button>
                          <a href="manage-students.php" class="btn btn-light">Back</a>
                        <?php }
                      } ?>
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
    <script src="vendors/select2/select2.min.js"></script>
    <script src="vendors/typeahead.js/typeahead.bundle.min.js"></script>
    <script src="js/toast.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
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

      function updateMajors(currentMajor) {
        const programSelect = document.getElementById('program');
        const majorSelect = document.getElementById('major');
        const selectedProgram = programSelect.value;

        // Clear existing options
        majorSelect.innerHTML = '<option value="">Select Major</option>';

        const majors = {
            "Bachelor of Elementary Education (BEEd)": [
                "Major in General Content"
            ],
            "Bachelor of Secondary Education (BSEd)": [
                "Major in English",
                "Major in Filipino",
                "Major in Mathematics"
            ],
            "Bachelor of Science in Business Administration (BSBA)": [
                "Major in Human Resource Management",
                "Major in Marketing Management"
            ],
            "Bachelor of Industrial Technology (BindTech)": [
                "Major in Computer Technology",
                "Major in Electronics Technology"
            ],
            "Bachelor of Science in Information Technology (BSIT)": [
                "Major in information technology"
            ]
        };

        if (majors[selectedProgram]) {
            majors[selectedProgram].forEach(function(major) {
                const option = document.createElement('option');
                option.value = major;
                option.textContent = major;
                if (major === currentMajor) {
                    option.selected = true;
                }
                majorSelect.appendChild(option);
            });
        }
      }

      // Trigger on page load to set initial state
      window.addEventListener('DOMContentLoaded', function () {
        toggleOtherGenderInput();
        // Pass the current major to pre-select it
        var currentMajor = "<?php echo htmlentities($row->Major, ENT_QUOTES); ?>";
        updateMajors(currentMajor);

        var currentCity = "<?php echo htmlentities($row->CityMunicipality, ENT_QUOTES); ?>";
        fetch('../data/cities.json')
          .then(response => response.json())
          .then(data => {
              citiesData = data;
              updateCities(currentCity);
          })
          .catch(error => console.error('Error loading cities:', error));
      });

      var citiesData = {};
      function updateCities(selectedCity) {
        var province = $('.province-select').val();
        var container = $('#city-municipality-container');
        container.empty();

        if (citiesData[province]) {
          var select = $('<select name="citymunicipality" id="citymunicipality-select" class="form-control" style="text-transform: capitalize;"></select>');
          select.append('<option value="">Select City/Municipality</option>');
          citiesData[province].forEach(function(city) {
            var option = $('<option></option>').val(city).text(city);
            if (city === selectedCity) {
              option.prop('selected', true);
            }
            select.append(option);
          });
          container.append(select);
          $('#citymunicipality-select').select2();
        } else {
          var input = $('<input type="text" name="citymunicipality" id="citymunicipality-text" class="form-control" style="text-transform: capitalize;">').val(selectedCity);
          container.append(input);
        }
      }
      // Initialize Select2 for province dropdown
      if (window.jQuery) {
        jQuery('.province-select').select2();
        jQuery('.province-select').on('change', function() { updateCities(''); });
      }
    </script>
  </body>

  </html>
<?php } ?>