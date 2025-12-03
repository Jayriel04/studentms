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
    <link rel="stylesheet" href="./css/modal.css">
    <link rel="stylesheet" href="css/responsive.css">

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
              <a href="manage-students.php" class="add-btn" style="text-decoration: none; margin-right: 20px;">↩ Back</a>
            </div>
            <div class="row">
              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <form class="forms-sample" method="post" id="addStudentForm">
                    <div class="add-student-form-container">
                      <h2>Student Registration Form</h2>
                      <p class="form-description">Fill the form below to add a new student.</p>

                      <!-- Progress Bar -->
                      <div class="add-student-progress-bar">
                        <div class="step active" data-step="1"><div class="step-icon">1</div><div class="step-label">Personal</div></div>
                        <div class="step" data-step="2"><div class="step-icon">2</div><div class="step-label">Academic</div></div>
                        <div class="step" data-step="3"><div class="step-icon">3</div><div class="step-label">Contact</div></div>
                        <div class="step" data-step="4"><div class="step-icon">4</div><div class="step-label">Emergency</div></div>
                        <div class="step" data-step="5"><div class="step-icon">5</div><div class="step-label">Account</div></div>
                      </div>

                      <!-- Step 1: Personal Information -->
                      <div class="form-step active" data-step="1">
                        <div class="add-student-section-title">Personal Information</div>
                        <div class="add-student-form-grid">
                          <div class="add-student-input-group"><label>Student ID</label><div class="add-student-input-wrapper"><input type="text" name="stuid" required placeholder="e.g., 222 - 08410" pattern="\d{3} - \d{5}" title="The format must be: 222 - 08410"></div></div>
                          <div class="add-student-input-group"><label>Family Name</label><div class="add-student-input-wrapper"><input type="text" name="familyname" required></div></div>
                          <div class="add-student-input-group"><label>First Name</label><div class="add-student-input-wrapper"><input type="text" name="firstname" required></div></div>
                          <div class="add-student-input-group"><label>Middle Name</label><div class="add-student-input-wrapper"><input type="text" name="middlename"></div></div>
                          <div class="add-student-input-group"><label>Date of Birth</label><div class="add-student-input-wrapper"><input type="date" name="dob" required></div></div>
                          <div class="add-student-input-group"><label>Place of Birth</label><div class="add-student-input-wrapper"><input type="text" name="placeofbirth"></div></div>
                          <div class="add-student-input-group"><label>Gender</label><div class="add-student-input-wrapper"><select name="gender" id="gender" required onchange="toggleOtherGenderInput()"><option value="">Select Gender</option><option value="Male">Male</option><option value="Female">Female</option><option value="Other">Other</option></select></div></div>
                          <div class="add-student-input-group" id="otherGenderInput" style="display: none;"><label>Please Specify Gender</label><div class="add-student-input-wrapper"><input type="text" name="otherGender" id="otherGender"></div></div>
                          <div class="add-student-input-group"><label>Civil Status</label><div class="add-student-input-wrapper"><select name="civilstatus"><option value="">Select Status</option><option value="Single">Single</option><option value="Married">Married</option><option value="Divorced">Divorced</option><option value="Widowed">Widowed</option><option value="Separated">Separated</option></select></div></div>
                          <div class="add-student-input-group"><label>Religion</label><div class="add-student-input-wrapper"><input type="text" name="religion"></div></div>
                          <div class="add-student-input-group"><label>Height (cm)</label><div class="add-student-input-wrapper"><input type="text" name="height"></div></div>
                          <div class="add-student-input-group"><label>Weight (kg)</label><div class="add-student-input-wrapper"><input type="text" name="weight"></div></div>
                          <div class="add-student-input-group"><label>Citizenship</label><div class="add-student-input-wrapper"><input type="text" name="citizenship"></div></div>
                          <div class="add-student-input-group"><label>Father's Name</label><div class="add-student-input-wrapper"><input type="text" name="fathersname"></div></div>
                          <div class="add-student-input-group"><label>Mother's Maiden Name</label><div class="add-student-input-wrapper"><input type="text" name="mothersmaidenname"></div></div>
                        </div>
                      </div>

                      <!-- Step 2: Academic Details -->
                      <div class="form-step" data-step="2">
                        <div class="add-student-section-title">Academic Details</div>
                        <div class="add-student-form-grid">
                          <div class="add-student-input-group"><label>Program</label><div class="add-student-input-wrapper"><select name="program" id="program" required onchange="updateMajors()"><option value="">Select Program</option><option value="Bachelor of Elementary Education (BEEd)">Bachelor of Elementary Education (BEEd)</option><option value="Bachelor of Secondary Education (BSEd)">Bachelor of Secondary Education (BSEd)</option><option value="Bachelor of Science in Business Administration (BSBA)">Bachelor of Science in Business Administration (BSBA)</option><option value="Bachelor of Industrial Technology (BindTech)">Bachelor of Industrial Technology (BindTech)</option><option value="Bachelor of Science in Information Technology (BSIT)">Bachelor of Science in Information Technology (BSIT)</option></select></div></div>
                          <div class="add-student-input-group"><label>Major</label><div class="add-student-input-wrapper"><select name="major" id="major"><option value="">Select Major</option></select></div></div>
                          <div class="add-student-input-group"><label>Year Level</label><div class="add-student-input-wrapper"><select name="yearlevel" required><option value="">Select Year Level</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option></select></div></div>
                          <div class="add-student-input-group"><label>Learner's Reference No. (LRN)</label><div class="add-student-input-wrapper"><input type="text" name="lrn"></div></div>
                          <div class="add-student-input-group"><label>Category</label><div class="add-student-input-wrapper"><select name="category" required><option value="">Select Category</option><option value="New Freshman">New Freshman</option><option value="Continuing/Returnee">Continuing/Returnee</option><option value="Shiftee">Shiftee</option><option value="Second Degree">Second Degree</option><option value="Regular">Regular</option><option value="Irregular">Irregular</option></select></div></div>
                        </div>
                      </div>

                      <!-- Step 3: Contact & Address -->
                      <div class="form-step" data-step="3">
                        <div class="add-student-section-title">Contact & Address</div>
                        <div class="add-student-form-grid">
                          <div class="add-student-input-group"><label>Email Address</label><div class="add-student-input-wrapper"><input type="email" name="emailaddress" required></div></div>
                          <div class="add-student-input-group"><label>Contact Number</label><div class="add-student-input-wrapper"><input type="text" name="contactnumber" required></div></div>
                          <div class="add-student-input-group"><label>Building/House Number</label><div class="add-student-input-wrapper"><input type="text" name="buildinghouse"></div></div>
                          <div class="add-student-input-group"><label>Street Name</label><div class="add-student-input-wrapper"><input type="text" name="streetname"></div></div>
                          <div class="add-student-input-group"><label>Barangay</label><div class="add-student-input-wrapper"><input type="text" name="barangay"></div></div>
                          <div class="add-student-input-group">
                            <label>Province</label>
                            <div class="add-student-input-wrapper">
                              <select name="province" class="province-select">
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
                          </div>
                          <div class="add-student-input-group">
                              <label>City/Municipality</label>
                              <div class="add-student-input-wrapper" id="city-municipality-container">
                                  <input type="text" name="citymunicipality" id="citymunicipality-text">
                              </div>
                          </div>
                          <div class="add-student-input-group"><label>Postal Code</label><div class="add-student-input-wrapper"><input type="text" name="postalcode"></div></div>
                        </div>
                      </div>

                      <!-- Step 4: Emergency Contact -->
                      <div class="form-step" data-step="4">
                        <div class="add-student-section-title">Emergency Contact</div>
                        <div class="add-student-form-grid">
                          <div class="add-student-input-group"><label>Full Name</label><div class="add-student-input-wrapper"><input type="text" name="emergencycontactperson"></div></div>
                          <div class="add-student-input-group"><label>Relationship</label><div class="add-student-input-wrapper"><input type="text" name="emergencyrelationship"></div></div>
                          <div class="add-student-input-group"><label>Phone Number</label><div class="add-student-input-wrapper"><input type="text" name="emergencycontactnumber"></div></div>
                          <div class="add-student-input-group"><label>Address</label><div class="add-student-input-wrapper"><textarea name="emergencyaddress"></textarea></div></div>
                        </div>
                      </div>

                      <!-- Step 5: Account -->
                      <div class="form-step" data-step="5">
                        <div class="add-student-section-title">Account</div>
                        <div class="add-student-form-grid">
                          <div class="add-student-input-group"><label>Password</label><div class="add-student-input-wrapper"><input type="password" name="password" required></div></div>
                        </div>
                      </div>

                      <!-- Navigation Buttons -->
                      <div class="form-navigation-buttons">
                        <button type="button" class="btn btn-light btn-prev" style="display: none;">Previous</button>
                        <button type="button" class="btn btn-primary btn-next">Next</button>
                        <button type="submit" class="add-student-btn-submit" name="submit" style="display: none;">Add Student</button>
                      </div>

                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="vendors/select2/select2.min.js"></script>
    <script src="vendors/typeahead.js/typeahead.bundle.min.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script src="js/toast.js"></script>
    <script src="js/manage-student.js"></script>
  </body>

  </html>
<?php } ?>