<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsstaffid']) == 0) { // Fixed condition
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
    $buildinghouse = $_POST['buildinghouse']; // Building/House Number
    $streetname = $_POST['streetname']; // New field
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
    $eid = $_GET['editid'];

    // Check if the new Student ID already exists
    $checkSql = "SELECT COUNT(*) FROM tblstudent WHERE StuID = :stuid AND ID != :eid";
    $checkQuery = $dbh->prepare($checkSql);
    $checkQuery->bindParam(':stuid', $stuid, PDO::PARAM_STR);
    $checkQuery->bindParam(':eid', $eid, PDO::PARAM_STR);
    $checkQuery->execute();
    $isDuplicate = $checkQuery->fetchColumn();

    if ($isDuplicate > 0) {
      echo '<script>if(window.showToast) showToast("This Student ID already exists. Please choose a different one.","warning");</script>';
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
                YearLevel=:yearlevel 
                WHERE ID=:eid";

      $query = $dbh->prepare($sql);
      // Binding parameters
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

      echo '<script>if(window.showToast) showToast("Student details updated successfully.","success");</script>';
    }
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <title>Student Profiling System || Update Students</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="css/style.css" />
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
                                <label>Student ID</label>
                                <input type="text" name="stuid" value="<?php echo htmlentities($row->StuID); ?>"
                                  class="form-control" required>
                              </div>
                              <div class="form-group">
                                <label>Family Name</label>
                                <input type="text" name="familyname" value="<?php echo htmlentities($row->FamilyName); ?>"
                                  class="form-control" required>
                              </div>
                              <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="firstname" value="<?php echo htmlentities($row->FirstName); ?>"
                                  class="form-control" required>
                              </div>
                              <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" name="middlename" value="<?php echo htmlentities($row->MiddleName); ?>"
                                  class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Program</label>
                                <input type="text" name="program" value="<?php echo htmlentities($row->Program); ?>"
                                  class="form-control" required>
                              </div>
                              <div class="form-group">
                                <label>Major</label>
                                <input type="text" name="major" value="<?php echo htmlentities($row->Major); ?>"
                                  class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Learner's Reference No.</label>
                                <input type="text" name="lrn" value="<?php echo htmlentities($row->LearnersReferenceNo); ?>"
                                  class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" name="dob" value="<?php echo htmlentities($row->DOB); ?>"
                                  class="form-control" required>
                              </div>
                              <div class="form-group">
                                <label>Place of Birth</label>
                                <input type="text" name="placeofbirth" value="<?php echo htmlentities($row->PlaceOfBirth); ?>"
                                  class="form-control">
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
                                <input type="text" name="otherGender" id="otherGender" class="form-control"
                                  value="<?php if (!in_array($row->Gender, ['Male', 'Female']))
                                    echo htmlentities($row->Gender); ?>">
                              </div>
                              <div class="form-group">
                                <label>Civil Status</label>
                                <input type="text" name="civilstatus" value="<?php echo htmlentities($row->CivilStatus); ?>"
                                  class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Religion</label>
                                <input type="text" name="religion" value="<?php echo htmlentities($row->Religion); ?>"
                                  class="form-control">
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
                                  class="form-control">
                              </div>
                            </div>
                            <div class="col-6">
                              <h4>Contact Details</h4>
                              <hr />
                              <div class="form-group">
                                <label>Father's Name</label>
                                <input type="text" name="fathersname" value="<?php echo htmlentities($row->FathersName); ?>"
                                  class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Mother's Maiden Name</label>
                                <input type="text" name="mothersmaidenname"
                                  value="<?php echo htmlentities($row->MothersMaidenName); ?>" class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Building/House Number</label>
                                <input type="text" name="buildinghouse"
                                  value="<?php echo htmlentities($row->BuildingHouseNumber); ?>" class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Street Name</label>
                                <input type="text" name="streetname" value="<?php echo htmlentities($row->StreetName); ?>"
                                  class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Barangay</label>
                                <input type="text" name="barangay" value="<?php echo htmlentities($row->Barangay); ?>"
                                  class="form-control">
                              </div>
                              <div class="form-group">
                                <label>City/Municipality</label>
                                <input type="text" name="citymunicipality"
                                  value="<?php echo htmlentities($row->CityMunicipality); ?>" class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Province</label>
                                <input type="text" name="province" value="<?php echo htmlentities($row->Province); ?>"
                                  class="form-control">
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
                                  value="<?php echo htmlentities($row->EmergencyContactPerson); ?>" class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Emergency Relationship</label>
                                <input type="text" name="emergencyrelationship"
                                  value="<?php echo htmlentities($row->EmergencyRelationship); ?>" class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Emergency Contact Number</label>
                                <input type="text" name="emergencycontactnumber"
                                  value="<?php echo htmlentities($row->EmergencyContactNumber); ?>" class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Emergency Address</label>
                                <textarea name="emergencyaddress"
                                  class="form-control"><?php echo htmlentities($row->EmergencyAddress); ?></textarea>
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
                                  <option value="v">Regular</option>
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
      // Trigger on page load to set initial state
      window.addEventListener('DOMContentLoaded', function () {
        toggleOtherGenderInput();
      });
    </script>
  </body>

  </html>
<?php } ?>