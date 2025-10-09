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
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
                <div class="profile-card">
                  <?php
                  $sid = $_SESSION['sturecmsstuid'];
                  $sql = "SELECT StuID, FamilyName, FirstName, MiddleName, Program, Major, LearnersReferenceNo, DOB, PlaceOfBirth, Gender, CivilStatus, Religion, Height, Weight, Citizenship, FathersName, MothersMaidenName, BuildingHouseNumber, StreetName, Barangay, CityMunicipality, Province, PostalCode, ContactNumber, EmailAddress, EmergencyContactPerson, EmergencyRelationship, EmergencyContactNumber, EmergencyAddress, Category, YearLevel, Image, Academic, NonAcademic FROM tblstudent WHERE StuID=:sid";
                  $query = $dbh->prepare($sql);
                  $query->bindParam(':sid', $sid, PDO::PARAM_STR);
                  $query->execute();
                  $results = $query->fetchAll(PDO::FETCH_OBJ);
                  if ($query->rowCount() > 0) {
                    foreach ($results as $row) { ?>
                      <div class="profile-header" style="text-align: center;">
                        <img src="../admin/images/<?php echo htmlentities($row->Image ?: 'default.png'); ?>" alt="Profile Picture" class="profile-avatar">
                        <h1 class="profile-name"><?php echo htmlentities($row->FirstName . " " . $row->FamilyName); ?></h1>
                        <div class="profile-id">Student ID: <?php echo htmlentities($row->StuID); ?></div>
                      </div>
                      <div class="profile-body">
                        <!-- Personal Information Section -->
                        <div class="profile-section">
                          <h5 class="profile-section-title"><i class="fas fa-user-circle"></i>Personal Information</h5>
                          <ul class="profile-info-list">
                            <li><span class="info-label">Full Name</span> <span class="info-value"><?php echo htmlentities(trim($row->FirstName . ' ' . $row->MiddleName . ' ' . $row->FamilyName)); ?></span></li>
                            <li><span class="info-label">Date of Birth</span> <span class="info-value"><?php echo htmlentities($row->DOB); ?></span></li>
                            <li><span class="info-label">Gender</span> <span class="info-value"><?php echo htmlentities($row->Gender); ?></span></li>
                            <li><span class="info-label">Civil Status</span> <span class="info-value"><?php echo htmlentities($row->CivilStatus); ?></span></li>
                            <li><span class="info-label">Citizenship</span> <span class="info-value"><?php echo htmlentities($row->Citizenship); ?></span></li>
                            <li><span class="info-label">Religion</span> <span class="info-value"><?php echo htmlentities($row->Religion); ?></span></li>
                          </ul>
                        </div>

                        <!-- Academic Details Section -->
                        <div class="profile-section">
                          <h5 class="profile-section-title"><i class="fas fa-graduation-cap"></i>Academic Details</h5>
                          <ul class="profile-info-list">
                            <li><span class="info-label">Program</span> <span class="info-value"><?php echo htmlentities($row->Program); ?></span></li>
                            <li><span class="info-label">Major</span> <span class="info-value"><?php echo htmlentities($row->Major); ?></span></li>
                            <li><span class="info-label">Year Level</span> <span class="info-value"><?php echo htmlentities($row->YearLevel); ?></span></li>
                            <li><span class="info-label">LRN</span> <span class="info-value"><?php echo htmlentities($row->LearnersReferenceNo); ?></span></li>
                            <li><span class="info-label">Category</span> <span class="info-value"><?php echo htmlentities($row->Category); ?></span></li>
                          </ul>
                        </div>

                        <!-- Contact & Address Section -->
                        <div class="profile-section">
                          <h5 class="profile-section-title"><i class="fas fa-map-marker-alt"></i>Contact & Address</h5>
                          <ul class="profile-info-list">
                            <li><span class="info-label">Email</span> <span class="info-value"><?php echo htmlentities($row->EmailAddress); ?></span></li>
                            <li><span class="info-label">Phone</span> <span class="info-value"><?php echo htmlentities($row->ContactNumber); ?></span></li>
                            <li><span class="info-label">Address</span> <span class="info-value">
                                <?php
                                  $address = implode(', ', array_filter([
                                    $row->BuildingHouseNumber, $row->StreetName, $row->Barangay,
                                    $row->CityMunicipality, $row->Province, $row->PostalCode
                                  ]));
                                  echo htmlentities($address ?: 'N/A');
                                ?>
                              </span></li>
                          </ul>
                        </div>

                        <!-- Emergency Contact Section -->
                        <div class="profile-section">
                          <h5 class="profile-section-title"><i class="fas fa-first-aid"></i>Emergency Contact</h5>
                          <ul class="profile-info-list">
                            <li><span class="info-label">Name</span> <span class="info-value"><?php echo htmlentities($row->EmergencyContactPerson); ?></span></li>
                            <li><span class="info-label">Relationship</span> <span class="info-value"><?php echo htmlentities($row->EmergencyRelationship); ?></span></li>
                            <li><span class="info-label">Phone</span> <span class="info-value"><?php echo htmlentities($row->EmergencyContactNumber); ?></span></li>
                            <li><span class="info-label">Address</span> <span class="info-value"><?php echo htmlentities($row->EmergencyAddress); ?></span></li>
                          </ul>
                        </div>

                        <!-- Skills Section -->
                        <div class="profile-section" style="grid-column: 1 / -1;">
                          <h5 class="profile-section-title"><i class="fas fa-star"></i>Skills & Achievements</h5>
                          <div class="skills-container">
                            <div class="skill-category">
                              <h6 class="skill-category-title"><i class="fas fa-book"></i>Academic</h6>
                              <div class="skill-tags">
                                <?php
                                $academic_skills = !empty($row->Academic) ? array_map('trim', explode(',', $row->Academic)) : [];
                                if (!empty($academic_skills)) {
                                  foreach ($academic_skills as $skill) {
                                    echo '<span class="skill-tag">' . htmlentities($skill) . '</span>';
                                  }
                                } else {
                                  echo '<span class="text-muted">No academic skills listed.</span>';
                                }
                                ?>
                              </div>
                            </div>
                            <div class="skill-category">
                              <h6 class="skill-category-title"><i class="fas fa-basketball-ball"></i>Non-Academic</h6>
                              <div class="skill-tags">
                                <?php
                                $non_academic_skills = !empty($row->NonAcademic) ? array_map('trim', explode(',', $row->NonAcademic)) : [];
                                if (!empty($non_academic_skills)) {
                                  foreach ($non_academic_skills as $skill) {
                                    echo '<span class="skill-tag">' . htmlentities($skill) . '</span>';
                                  }
                                } else {
                                  echo '<span class="text-muted">No non-academic skills listed.</span>';
                                }
                                ?>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="profile-footer">
                        <a href="update-profile.php" class="btn btn-primary">Update Profile</a>
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