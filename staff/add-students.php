<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsstaffid']) == 0) {
  header('location:logout.php');
} else {
  $success_message = $error_message = '';
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
      $password = md5($_POST['password']);

      $ret = "SELECT StuID FROM tblstudent WHERE StuID=:stuid";
      $query = $dbh->prepare($ret);
      $query->bindParam(':stuid', $stuid, PDO::PARAM_STR);
      $query->execute();

      if ($query->rowCount() == 0) {
        $sql = "INSERT INTO tblstudent (StuID, FamilyName, FirstName, MiddleName, Program, Major, LearnersReferenceNo, DOB, PlaceOfBirth, Gender, CivilStatus, Religion, Height, Weight, Citizenship, FathersName, MothersMaidenName, BuildingHouseNumber, StreetName, Barangay, CityMunicipality, Province, PostalCode, ContactNumber, EmailAddress, EmergencyContactPerson, EmergencyRelationship, EmergencyContactNumber, EmergencyAddress, Category, YearLevel, Password) 
                VALUES (:stuid, :familyname, :firstname, :middlename, :program, :major, :lrn, :dob, :placeofbirth, :gender, :civilstatus, :religion, :height, :weight, :citizenship, :fathersname, :mothersmaidenname, :buildinghouse, :streetname, :barangay, :citymunicipality, :province, :postalcode, :contactnumber, :emailaddress, :emergencycontactperson, :emergencyrelationship, :emergencycontactnumber, :emergencyaddress, :category, :yearlevel, :password)";

        $query = $dbh->prepare($sql);
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
        $query->bindParam(':streetname', $streetname, PDO::PARAM_STR);
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
        $query->bindParam(':password', $password, PDO::PARAM_STR);

        $query->execute();
        $LastInsertId = $dbh->lastInsertId();
        if ($LastInsertId > 0) {
          $success_message = "Student has been added.";
        } else {
          $error_message = "Something went wrong. Please try again.";
        }
      } else {
        $error_message = 'Student ID already exists. Please try again.';
      }
    }
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <title>Student Profiling System || Add Students</title>
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
              <h3 class="page-title"> Add Students </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page"> Add Students</li>
                </ol>
              </nav>
            </div>
            <div class="row">
              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                      <h4 class="card-title">Add Students Details</h4>
                      <a href="import-file.php" class="btn btn-primary">Import</a>
                    </div>
                    <hr />
                    <?php if ($success_message): ?>
                      <div aria-live="polite" aria-atomic="true" style="position: relative; min-height: 40px;">
                        <div class="toast" id="successToast"
                          style="position: absolute; top: 0; right: 0; min-width: 250px; z-index: 1050;" data-delay="3000"
                          data-autohide="true">
                          <div class="toast-header bg-success text-white">
                            <strong class="mr-auto">Success</strong>
                            <small>Now</small>
                            <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast"
                              aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="toast-body"><?php echo $success_message; ?></div>
                        </div>
                      </div>
                      <script>
                        window.addEventListener('DOMContentLoaded', function () {
                          var toastEl = document.getElementById('successToast');
                          if (toastEl && window.$) {
                            $(toastEl).toast('show');
                          } else if (toastEl && typeof bootstrap !== "undefined") {
                            var toast = new bootstrap.Toast(toastEl);
                            toast.show();
                          }
                        });
                      </script>
                    <?php elseif ($error_message): ?>
                      <div aria-live="polite" aria-atomic="true" style="position: relative; min-height: 40px;">
                        <div class="toast" id="errorToast"
                          style="position: absolute; top: 0; right: 0; min-width: 250px; z-index: 1050;" data-delay="4000"
                          data-autohide="true">
                          <div class="toast-header bg-danger text-white">
                            <strong class="mr-auto">Error</strong>
                            <small>Now</small>
                            <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast"
                              aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="toast-body"><?php echo $error_message; ?></div>
                        </div>
                      </div>
                      <script>
                        window.addEventListener('DOMContentLoaded', function () {
                          var toastEl = document.getElementById('errorToast');
                          if (toastEl && window.$) {
                            $(toastEl).toast('show');
                          } else if (toastEl && typeof bootstrap !== "undefined") {
                            var toast = new bootstrap.Toast(toastEl);
                            toast.show();
                          }
                        });
                      </script>
                    <?php endif; ?>
                    <form class="forms-sample" method="post">
                      <div class="row">
                        <div class="col-6">
                          <h4>Student Details</h4>
                          <hr />
                          <div class="form-group">
                            <label for="stuid">Student ID</label>
                            <input type="text" name="stuid" class="form-control" required placeholder="e.g., 222 - 08410"
                              pattern="\d{3} - \d{5}" title="The format must be: 222 - 08410"
                              style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label for="familyname">Family Name</label>
                            <input type="text" name="familyname" class="form-control" required
                              style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label for="firstname">First Name</label>
                            <input type="text" name="firstname" class="form-control" required
                              style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label for="middlename">Middle Name</label>
                            <input type="text" name="middlename" class="form-control" style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label for="program">Program</label>
                            <select name="program" id="program" class="form-control" required onchange="updateMajors()">
                              <option value="">Select Program</option>
                              <option value="Bachelor of Elementary Education (BEEd)">Bachelor of Elementary Education
                                (BEEd)</option>
                              <option value="Bachelor of Secondary Education (BSEd)">Bachelor of Secondary Education
                                (BSEd)</option>
                              <option value="Bachelor of Science in Business Administration (BSBA)">Bachelor of Science in
                                Business Administration (BSBA)</option>
                              <option value="Bachelor of Industrial Technology (BindTech)">Bachelor of Industrial
                                Technology (BindTech)</option>
                              <option value="Bachelor of Science in Information Technology (BSIT)">Bachelor of Science in
                                Information Technology (BSIT)</option>
                            </select>
                          </div>
                          <div class="form-group">
                            <label for="major">Major</label>
                            <select name="major" id="major" class="form-control" style="text-transform: capitalize;">
                              <option value="">Select Major</option>
                            </select>
                          </div>
                          <div class="form-group">
                            <label for="lrn">Learner's Reference No.</label>
                            <input type="text" name="lrn" class="form-control">
                          </div>
                          <div class="form-group">
                            <label for="dob">Date of Birth</label>
                            <input type="date" name="dob" class="form-control" required>
                          </div>
                          <div class="form-group">
                            <label for="placeofbirth">Place of Birth</label>
                            <input type="text" name="placeofbirth" class="form-control"
                              style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label for="gender">Gender</label>
                            <select name="gender" id="gender" class="form-control" required
                              onchange="toggleOtherGenderInput()">
                              <option value="">Select Gender</option>
                              <option value="Male">Male</option>
                              <option value="Female">Female</option>
                              <option value="Other">Other</option>
                            </select>
                          </div>
                          <div class="form-group" id="otherGenderInput" style="display: none;">
                            <label>Please Specify</label>
                            <input type="text" name="otherGender" id="otherGender" class="form-control"
                              style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label for="civilstatus">Civil Status</label>
                            <select name="civilstatus" class="form-control">
                              <option value="">Select Status</option>
                              <option value="Single">Single</option>
                              <option value="Married">Married</option>
                              <option value="Divorced">Divorced</option>
                              <option value="Widowed">Widowed</option>
                              <option value="Separated">Separated</option>
                            </select>
                          </div>
                          <div class="form-group">
                            <label for="religion">Religion</label>
                            <input type="text" name="religion" class="form-control" style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label for="height">Height</label>
                            <input type="number" name="height" class="form-control">
                          </div>
                          <div class="form-group">
                            <label for="weight">Weight (kg)</label>
                            <input type="text" name="weight" class="form-control">
                          </div>
                          <div class="form-group">
                            <label for="citizenship">Citizenship</label>
                            <input type="text" name="citizenship" class="form-control">
                          </div>
                        </div>
                        <div class="col-6">
                          <h4>Contact Details</h4>
                          <hr />
                          <div class="form-group">
                            <label for="fathersname">Father's Name</label>
                            <input type="text" name="fathersname" class="form-control"
                              style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label for="mothersmaidenname">Mother's Maiden Name</label>
                            <input type="text" name="mothersmaidenname" class="form-control"
                              style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label for="buildinghouse">Building/House Number</label>
                            <input type="text" name="buildinghouse" class="form-control"
                              style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label for="streetname">Street Name</label>
                            <input type="text" name="streetname" class="form-control" style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label for="barangay">Barangay</label>
                            <input type="text" name="barangay" class="form-control" style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label for="citymunicipality">City/Municipality</label>
                            <div id="city-municipality-container">
                              <input type="text" name="citymunicipality" id="citymunicipality-text" class="form-control"
                                style="text-transform: capitalize;">
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="province">Province</label>
                            <select name="province" class="form-control province-select"
                              style="text-transform: capitalize;">
                              <option value="">Select Province</option>
                              <?php
                              $provincesJson = file_get_contents('../data/provinces.json');
                              $provinces = json_decode($provincesJson, true);
                              if (is_array($provinces)) {
                                foreach ($provinces as $province) {
                                  echo "<option value=\"" . htmlspecialchars($province) . "\">" . htmlspecialchars($province) . "</option>";
                                }
                              }
                              ?>
                            </select>
                          </div>
                          <div class="form-group">
                            <label for="postalcode">Postal Code</label>
                            <input type="text" name="postalcode" class="form-control">
                          </div>
                          <div class="form-group">
                            <label for="contactnumber">Contact Number</label>
                            <input type="text" name="contactnumber" class="form-control" required>
                          </div>
                          <div class="form-group">
                            <label for="emailaddress">Email Address</label>
                            <input type="email" name="emailaddress" class="form-control" required>
                          </div>
                          <div class="form-group">
                            <label for="emergencycontactperson">Emergency Contact Person</label>
                            <input type="text" name="emergencycontactperson" class="form-control"
                              style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label for="emergencyrelationship">Emergency Relationship</label>
                            <input type="text" name="emergencyrelationship" class="form-control"
                              style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label for="emergencycontactnumber">Emergency Contact Number</label>
                            <input type="text" name="emergencycontactnumber" class="form-control"
                              style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label for="emergencyaddress">Emergency Address</label>
                            <textarea name="emergencyaddress" class="form-control"
                              style="text-transform: capitalize;"></textarea>
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
                          <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" class="form-control" required>
                          </div>
                        </div>
                      </div>
                      <button type="submit" class="btn btn-primary" name="submit">Add Student</button>
                      <a href="manage-students.php" class="btn btn-light">Back</a>
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
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script src="js/toast.js"></script>
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

      function updateMajors() {
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
          majors[selectedProgram].forEach(function (major) {
            const option = document.createElement('option');
            option.value = major;
            option.textContent = major;
            majorSelect.appendChild(option);
          });
        }
      }

      var citiesData = {};
      fetch('../data/cities.json')
        .then(response => response.json())
        .then(data => {
          citiesData = data;
          updateCities(''); // Initial call
        })
        .catch(error => console.error('Error loading cities:', error));

      function updateCities(selectedCity) {
        var province = $('.province-select').val();
        var container = $('#city-municipality-container');
        container.empty();

        if (citiesData[province]) {
          var select = $('<select name="citymunicipality" id="citymunicipality-select" class="form-control" style="text-transform: capitalize;"></select>');
          select.append('<option value="">Select City/Municipality</option>');
          citiesData[province].forEach(function (city) {
            var option = $('<option></option>').val(city).text(city);
            if (city === selectedCity) {
              option.prop('selected', true);
            }
            select.append(option);
          });
          container.append(select);
          $('#citymunicipality-select').select2();
        } else {
          var input = $('<input type="text" name="citymunicipality" id="citymunicipality-text" class="form-control" style="text-transform: capitalize;">');
          if (selectedCity) {
            input.val(selectedCity);
          }
          container.append(input);
        }
      }
      // Initialize Select2 for province dropdown
      if (window.jQuery) {
        jQuery('.province-select').select2();
        jQuery('.province-select').on('change', function () { updateCities(''); });
      }
    </script>
  </body>

  </html>
<?php } ?>