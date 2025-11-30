<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid']) == 0) {
  header('location:logout.php');
} else {
  if (isset($_POST['submit'])) {
    $stuid = $_POST['stuid'];
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
    // Server-side validation for student ID format
    if (!preg_match('/^\d{3} - \d{5}$/', $stuid)) {
      echo '<script>if(window.showToast) showToast("Invalid Student ID format. Please use the format: 222 - 08410.","warning");</script>';
    } else {
      
    $sql = "SELECT StuID FROM tblstudent WHERE StuID=:stuid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':stuid', $stuid, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    if ($query->rowCount() > 0) {
      echo '<script>if(window.showToast) showToast("Student ID already exists. Please try again","warning");</script>';
    } else {
      $sql = "INSERT INTO tblstudent(StuID, Password, FamilyName, FirstName, MiddleName, Program, Major, LearnersReferenceNo, DOB, PlaceOfBirth, Gender, CivilStatus, Religion, Height, Weight, Citizenship, FathersName, MothersMaidenName, BuildingHouseNumber, StreetName, Barangay, CityMunicipality, Province, PostalCode, ContactNumber, EmailAddress, EmergencyContactPerson, EmergencyRelationship, EmergencyContactNumber, EmergencyAddress, Category, YearLevel) VALUES(:stuid, :password, :familyname, :firstname, :middlename, :program, :major, :lrn, :dob, :placeofbirth, :gender, :civilstatus, :religion, :height, :weight, :citizenship, :fathersname, :mothersmaidenname, :buildinghouse, :streetname, :barangay, :citymunicipality, :province, :postalcode, :contactnumber, :emailaddress, :emergencycontactperson, :emergencyrelationship, :emergencycontactnumber, :emergencyaddress, :category, :yearlevel)";
      $query = $dbh->prepare($sql);
      $query->bindParam(':stuid', $stuid, PDO::PARAM_STR);
      $query->bindParam(':password', $password, PDO::PARAM_STR);
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
      $query->execute();
      $LastInsertId = $dbh->lastInsertId();
      if ($LastInsertId > 0) {
        echo '<script>if(window.showToast) showToast("Student has been added.","success");</script>';
        echo "<script>window.location.href ='manage-students.php'</script>";
      } else {
        echo '<script>if(window.showToast) showToast("Something Went Wrong. Please try again","danger");</script>';
      }
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
                    <form class="forms-sample" method="post">
                      <div class="row">
                        <div class="col-6">
                          <h4>Student Details</h4>
                          <hr />
                          <div class="form-group">
                            <label>Student ID</label>
                            <input type="text" name="stuid" value="" class="form-control" required placeholder="e.g., 222 - 08410" pattern="\d{3} - \d{5}" title="The format must be: 222 - 08410" style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label>Family Name</label>
                            <input type="text" name="familyname" value="" class="form-control" required style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="firstname" value="" class="form-control" required style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" name="middlename" value="" class="form-control" style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label>Program</label>
                            <select name="program" id="program" class="form-control" required onchange="updateMajors()">
                              <option value="">Select Program</option>
                              <option value="Bachelor of Elementary Education (BEEd)">Bachelor of Elementary Education (BEEd)</option>
                              <option value="Bachelor of Secondary Education (BSEd)">Bachelor of Secondary Education (BSEd)</option>
                              <option value="Bachelor of Science in Business Administration (BSBA)">Bachelor of Science in Business Administration (BSBA)</option>
                              <option value="Bachelor of Industrial Technology (BindTech)">Bachelor of Industrial Technology (BindTech)</option>
                              <option value="Bachelor of Science in Information Technology (BSIT)">Bachelor of Science in Information Technology (BSIT)</option>
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
                            <input type="text" name="lrn" value="" class="form-control" style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="dob" value="" class="form-control" required>
                          </div>
                          <div class="form-group">
                            <label>Place of Birth</label>
                            <input type="text" name="placeofbirth" value="" class="form-control" style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label>Gender</label>
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
                            <input type="text" name="otherGender" id="otherGender" class="form-control" style="text-transform: capitalize;">
                          </div>

                          <div class="form-group">
                            <label>Civil Status</label>
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
                            <label>Religion</label>
                            <input type="text" name="religion" value="" class="form-control" style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label>Height (cm)</label>
                            <input type="text" name="height" value="" class="form-control">
                          </div>
                          <div class="form-group">
                            <label>Weight (kg)</label>
                            <input type="text" name="weight" value="" class="form-control">
                          </div>
                          <div class="form-group">
                            <label>Citizenship</label>
                            <input type="text" name="citizenship" value="" class="form-control" style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" value="" class="form-control" required>
                          </div>
                        </div>
                        <div class="col-6">
                          <h4>Contact Details</h4>
                          <hr />
                          <div class="form-group">
                            <label>Father's Name</label>
                            <input type="text" name="fathersname" value="" class="form-control" style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label>Mother's Maiden Name</label>
                            <input type="text" name="mothersmaidenname" value="" class="form-control" style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label>Building/House Number</label>
                            <input type="text" name="buildinghouse" value="" class="form-control" style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label>Street Name</label>
                            <input type="text" name="streetname" value="" class="form-control" style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label>Barangay</label>
                            <input type="text" name="barangay" value="" class="form-control" style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label>City/Municipality</label>
                            <div id="city-municipality-container">
                              <input type="text" name="citymunicipality" id="citymunicipality-text" class="form-control" style="text-transform: capitalize;">
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
                                foreach ($provinces as $province) {
                                    echo "<option value=\"" . htmlspecialchars($province) . "\">" . htmlspecialchars($province) . "</option>";
                                }
                              }
                              ?>
                            </select>
                          </div>
                          <div class="form-group">
                            <label>Postal Code</label>
                            <input type="text" name="postalcode" value="" class="form-control">
                          </div>
                          <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="contactnumber" value="" class="form-control" required>
                          </div>
                          <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="emailaddress" value="" class="form-control" required>
                          </div>
                          <div class="form-group">
                            <label>Emergency Contact Person</label>
                            <input type="text" name="emergencycontactperson" value="" class="form-control" style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label>Emergency Relationship</label>
                            <input type="text" name="emergencyrelationship" value="" class="form-control" style="text-transform: capitalize;">
                          </div>
                          <div class="form-group">
                            <label>Emergency Contact Number</label>
                            <input type="text" name="emergencycontactnumber" value="" class="form-control">
                          </div>
                          <div class="form-group">
                            <label>Emergency Address</label>
                            <textarea name="emergencyaddress" class="form-control" style="text-transform: capitalize;"></textarea>
                          </div>
                          <div class="form-group">
                            <label>Category</label>
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
                            <label>Year Level</label>
                            <select name="yearlevel" class="form-control" required>
                              <option value="">Select Year Level</option>
                              <option value="1">1</option>
                              <option value="2">2</option>
                              <option value="3">3</option>
                              <option value="4">4</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <button type="submit" class="btn btn-primary" name="submit">Add</button>
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
    <script>
      var provincesWithCities = { "Cebu": ["Bogo City", "Carcar City", "Danao City", "Naga City", "Talisay City", "Toledo City", "Cebu City", "Lapu-Lapu City", "Mandaue City", "Alcantara", "Alcoy", "Alegria", "Aloguinsan", "Argao", "Asturias", "Badian", "Balamban", "Bantayan", "Barili", "Boljoon", "Borbon", "Carmen", "Catmon", "Compostela", "Consolacion", "Cordoba", "Daanbantayan", "Dalaguete", "Dumanjug", "Ginatilan", "Liloan", "Madridejos", "Malabuyoc", "Medellin", "Minglanilla", "Moalboal", "Oslob", "Pilar", "Pinamungahan", "Poro", "Ronda", "Samboan", "San Fernando", "San Francisco", "San Remigio", "Santa Fe", "Santander", "Sibonga", "Sogod", "Tabogon", "Tabuelan", "Tuburan", "Tudela"] };
    </script>
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
            majors[selectedProgram].forEach(function(major) {
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
        jQuery('.province-select').on('change', function() { updateCities(''); });
      }
    </script>
  </body>

  </html>
<?php } ?>