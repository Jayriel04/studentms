<?php
session_start();
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsstuid']) == 0) { // Fixed condition
  header('location:logout.php');
} else {
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <title>Student Management System | View Student Profile</title>
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/style(v2).css" />
  </head>

  <body>
    <div class="container-scroller">
      <?php include_once('includes/header.php'); ?>
      <div class="container-fluid page-body-wrapper">
        <?php include_once('includes/sidebar.php'); ?>
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="page-header">
              <h3 class="page-title">My Profile</h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page">View Profile</li>
                </ol>
              </nav>
            </div>
            <div class="row justify-content-center">
              <div class="col-12">
                <div class="profile-card-grid">
                  <?php
                  $sid = $_SESSION['sturecmsstuid'];
                  $sql = "SELECT StuID, FamilyName, FirstName, MiddleName, Program, Major, LearnersReferenceNo, DOB, PlaceOfBirth, Gender, CivilStatus, Religion, Height, Weight, Citizenship, FathersName, MothersMaidenName, BuildingHouseNumber, StreetName, Barangay, CityMunicipality, Province, PostalCode, ContactNumber, EmailAddress, EmergencyContactPerson, EmergencyRelationship, EmergencyContactNumber, EmergencyAddress, Category, YearLevel, Image FROM tblstudent WHERE StuID=:sid";
                  $query = $dbh->prepare($sql);
                  $query->bindParam(':sid', $sid, PDO::PARAM_STR);
                  $query->execute();
                  $results = $query->fetchAll(PDO::FETCH_OBJ);
                  if ($query->rowCount() > 0) {
                    foreach ($results as $row) { ?>
                      <div class="profile-header">
                        <img src="../admin/images/<?php echo $row->Image; ?>" alt="Profile Picture" class="profile-avatar">
                        <div class="profile-name"><?php echo htmlentities($row->FirstName . " " . $row->FamilyName); ?></div>
                        <div class="profile-id">Student ID: <?php echo htmlentities($row->StuID); ?></div>
                      </div>
                      <div class="profile-body">
                        <table class="table profile-table">
                          <tr>
                            <th>First Name</th>
                            <td><?php echo htmlentities($row->FirstName); ?></td>
                          </tr>
                          <tr>
                            <th>Middle Name</th>
                            <td><?php echo htmlentities($row->MiddleName); ?></td>
                          </tr>
                          <tr>
                            <th>Family Name</th>
                            <td><?php echo htmlentities($row->FamilyName); ?></td>
                          </tr>
                          <tr>
                            <th>Program</th>
                            <td><?php echo htmlentities($row->Program); ?></td>
                          </tr>
                          <tr>
                            <th>Major</th>
                            <td><?php echo htmlentities($row->Major); ?></td>
                          </tr>
                          <tr>
                            <th>Year Level</th>
                            <td><?php echo htmlentities($row->YearLevel); ?></td>
                          </tr>
                          <tr>
                            <th>Learner's Reference No.</th>
                            <td><?php echo htmlentities($row->LearnersReferenceNo); ?></td>
                          </tr>
                        </table>
                        <table class="table profile-table">
                          <tr>
                            <th>Date of Birth</th>
                            <td><?php echo htmlentities($row->DOB); ?></td>
                          </tr>
                          <tr>
                            <th>Place of Birth</th>
                            <td><?php echo htmlentities($row->PlaceOfBirth); ?></td>
                          </tr>
                          <tr>
                            <th>Gender</th>
                            <td><?php echo htmlentities($row->Gender); ?></td>
                          </tr>
                          <tr>
                            <th>Civil Status</th>
                            <td><?php echo htmlentities($row->CivilStatus); ?></td>
                          </tr>
                          <tr>
                            <th>Religion</th>
                            <td><?php echo htmlentities($row->Religion); ?></td>
                          </tr>
                          <tr>
                            <th>Height (cm)</th>
                            <td><?php echo htmlentities($row->Height); ?></td>
                          </tr>
                          <tr>
                            <th>Weight (kg)</th>
                            <td><?php echo htmlentities($row->Weight); ?></td>
                          </tr>
                        </table>
                        <table class="table profile-table">
                          <tr>
                            <th>Citizenship</th>
                            <td><?php echo htmlentities($row->Citizenship); ?></td>
                          </tr>
                          <tr>
                            <th>Father's Name</th>
                            <td><?php echo htmlentities($row->FathersName); ?></td>
                          </tr>
                          <tr>
                            <th>Mother's Maiden Name</th>
                            <td><?php echo htmlentities($row->MothersMaidenName); ?></td>
                          </tr>
                          <tr>
                            <th>Contact Number</th>
                            <td><?php echo htmlentities($row->ContactNumber); ?></td>
                          </tr>
                          <tr>
                            <th>Building/House Number</th>
                            <td><?php echo htmlentities($row->BuildingHouseNumber); ?></td>
                          </tr>
                          <tr>
                            <th>Street Name</th>
                            <td><?php echo htmlentities($row->StreetName); ?></td>
                          </tr>
                          <tr>
                            <th>Barangay</th>
                            <td><?php echo htmlentities($row->Barangay); ?></td>
                          </tr>
                        </table>
                        <a href="update-profile.php" class="btn btn-primary profile-btn">Update Profile</a>
                      </div>
                    <?php }
                  } ?>
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
    <script src="js/typeahead.js"></script>
    <script src="js/select2.js"></script>
  </body>

  </html>
<?php } ?>
