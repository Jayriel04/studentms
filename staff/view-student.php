<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (!isset($_SESSION['sturecmsstaffid']) || strlen($_SESSION['sturecmsstaffid']) == 0) {
  header('location:logout.php');
  exit();
}
$viewid = isset($_GET['viewid']) ? trim($_GET['viewid']) : '';
if ($viewid === '') {
  echo "<script>alert('No student selected.');window.location='manage-students.php';</script>";
  exit();
}

// Helper function to get initials from a name
function getInitials($name)
{
  $words = explode(' ', trim($name));
  $initials = '';
  if (count($words) >= 2) {
    $initials .= strtoupper(substr($words[0], 0, 1));
    $initials .= strtoupper(substr(end($words), 0, 1));
  } else if (count($words) == 1) {
    $initials .= strtoupper(substr($words[0], 0, 2));
  }
  return $initials;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Student Profiling System || View Student Profile</title>
  <link rel="icon" href="../images/favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" sizes="180x180" href="../images/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="../images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../images/favicon-16x16.png">
  <link rel="icon" type="image/png" sizes="192x192" href="../images/android-chrome-192x192.png">
  <link rel="manifest" href="../images/site.webmanifest">
  <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="./css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="./css/style(v2).css">
  <link rel="stylesheet" href="./css/responsive.css">
  <style>
    /* ===================================================================
 *  Creative Student Profile Card
 * =================================================================== */

    :root {
      --card-bg: #ffffff;
      --card-shadow: 0 10px 30px -15px rgba(0, 0, 0, 0.15);
      --card-border-radius: 20px;
      --primary-accent: #1565C0;
      --text-primary: #212529;
      --text-secondary: #6c757d;
      --header-bg-gradient: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
      --icon-color: #adb5bd;
    }

    .profile-card {
      background-color: var(--card-bg);
      border-radius: var(--card-border-radius);
      box-shadow: var(--card-shadow);
      overflow: hidden;
      width: 100%;
      max-width: 900px;
      margin: 2rem auto;
      border: 2px solid #90caf9;
    }

    /* --- Profile Header --- */
    .profile-header {
      background: var(--header-bg-gradient);
      padding: 2rem;
      text-align: center;
      position: relative;
    }

    .profile-avatar {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 5px solid #ffffff;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      margin-bottom: 1rem;
    }

    .profile-name {
      font-size: 1.75rem;
      font-weight: 600;
      color: var(--text-primary);
      margin: 0;
    }

    .profile-id {
      font-size: 1rem;
      font-weight: 400;
      color: var(--text-secondary);
      background-color: rgba(255, 255, 255, 0.7);
      padding: 0.25rem 0.75rem;
      border-radius: 12px;
      display: inline-block;
      margin-top: 0.5rem;
    }

    /* --- Profile Body --- */
    .profile-body {
      padding: 2rem;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
    }

    .profile-section {
      padding: 0;
    }

    .profile-section-title {
      font-size: 1.2rem;
      font-weight: 600;
      color: var(--primary-accent);
      margin-bottom: 1.5rem;
      padding-bottom: 0.5rem;
      border-bottom: 2px solid #e9ecef;
      display: flex;
      align-items: center;
    }

    .profile-section-title i {
      margin-right: 0.75rem;
      color: var(--primary-accent);
      font-size: 1.1em;
    }

    .profile-info-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .profile-info-list li {
      display: flex;
      justify-content: space-between;
      padding: 0.75rem 0;
      border-bottom: 1px solid #f1f3f5;
      font-size: 0.95rem;
    }

    .profile-info-list li:last-child {
      border-bottom: none;
    }

    .info-label {
      font-weight: 500;
      color: var(--text-secondary);
      padding-right: 1rem;
      flex-shrink: 0;
    }

    .info-value {
      color: var(--text-primary);
      text-align: right;
      word-break: break-word;
    }

    /* --- Footer Actions --- */
    .profile-footer {
      padding: 1.5rem 2rem;
      background-color: #f8f9fa;
      text-align: right;
      border-top: 1px solid #e9ecef;
    }

    /* --- Skills Section --- */
    .skills-container {
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
    }

    .skill-category {
      padding: 0;
    }

    .skill-category-title {
      font-size: 1rem;
      font-weight: 600;
      color: var(--text-secondary);
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
    }

    .skill-category-title i {
      margin-right: 0.5rem;
      font-size: 1.1em;
    }

    .skill-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem 0.75rem;
    }

    .notice-title {
      white-space: nowrap;
    }

    .skill-tag {
      background-color: #e9ecef;
      color: var(--text-primary);
      padding: 0.4rem 0.8rem;
      border-radius: 6px;
      font-size: 0.85rem;
      font-weight: 500;
      border: 1px solid #dee2e6;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
      .profile-body {
        grid-template-columns: 1fr;
        padding: 1.5rem;
      }

      .profile-header {
        padding: 1.5rem;
      }

      .profile-avatar {
        width: 100px;
        height: 100px;
      }
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
            <h3 class="page-title">Student Profile</h3>
            <?php
            $back_link = 'manage-students.php'; // Default link
            if (isset($_SERVER['HTTP_REFERER'])) {
              $referer_url = parse_url($_SERVER['HTTP_REFERER']);
              if (isset($referer_url['path']) && (strpos($referer_url['path'], 'search.php') !== false || strpos($referer_url['path'], 'manage-students.php') !== false)) {
                $back_link = $_SERVER['HTTP_REFERER'];
              }
            }
            ?>
            <a href="<?php echo htmlspecialchars($back_link); ?>" class="add-btn"
              style="text-decoration: none; margin-right: 20px;">↩ Back</a>
          </div>

          <div class="row justify-content-center">
            <div class="col-12">
              <?php
              // Support both numeric DB ID and string StuID. If viewid is all digits, treat as ID (INT), else StuID (string).
              if (ctype_digit($viewid)) {
                $sql = "SELECT StuID, FamilyName, FirstName, MiddleName, Program, Major, LearnersReferenceNo, DOB, PlaceOfBirth, Gender, CivilStatus, Religion, Height, Weight, Citizenship, FathersName, MothersMaidenName, BuildingHouseNumber, StreetName, Barangay, CityMunicipality, Province, PostalCode, ContactNumber, EmailAddress, EmergencyContactPerson, EmergencyRelationship, EmergencyContactNumber, EmergencyAddress, Category, YearLevel, Image, Academic, NonAcademic FROM tblstudent WHERE ID = :viewid LIMIT 1";
                $query = $dbh->prepare($sql);
                $query->bindValue(':viewid', (int) $viewid, PDO::PARAM_INT);
              } else {
                $sql = "SELECT StuID, FamilyName, FirstName, MiddleName, Program, Major, LearnersReferenceNo, DOB, PlaceOfBirth, Gender, CivilStatus, Religion, Height, Weight, Citizenship, FathersName, MothersMaidenName, BuildingHouseNumber, StreetName, Barangay, CityMunicipality, Province, PostalCode, ContactNumber, EmailAddress, EmergencyContactPerson, EmergencyRelationship, EmergencyContactNumber, EmergencyAddress, Category, YearLevel, Image, Academic, NonAcademic FROM tblstudent WHERE StuID = :viewid LIMIT 1";
                $query = $dbh->prepare($sql);
                $query->bindValue(':viewid', $viewid, PDO::PARAM_STR);
              }
              $query->execute();
              $row = $query->fetch(PDO::FETCH_OBJ);
              if ($row) { ?>
                <div class="profile-card">
                  <div class="profile-header">
                    <?php
                    if (!empty($row->Image)) {
                      echo '<img src="../admin/images/' . htmlentities($row->Image) . '" alt="Profile Picture" class="profile-avatar">';
                    } else {
                      if ($row->Gender == 'Male') {
                        echo '<img src="../admin/images/faces/man.jpg" alt="Profile Picture" class="profile-avatar">';
                      } elseif ($row->Gender == 'Female') {
                        echo '<img src="../admin/images/faces/women.png" alt="Profile Picture" class="profile-avatar">';
                      } else {
                        // Fallback to initials for 'Other' or null gender
                        echo '<div class="profile-avatar" style="display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); font-size: 48px; color: white;">';
                        echo getInitials($row->FirstName . ' ' . $row->FamilyName);
                        echo '</div>';
                      }
                    }
                    ?>
                    <h1 class="profile-name"><?php echo htmlentities($row->FirstName . " " . $row->FamilyName); ?></h1>
                    <div class="profile-id">Student ID: <?php echo htmlentities($row->StuID); ?></div>
                  </div>

                  <div class="profile-body">
                    <!-- Personal Information Section -->
                    <div class="profile-section">
                      <h5 class="profile-section-title"><i class="fas fa-user-circle"></i>Personal Information</h5>
                      <ul class="profile-info-list">
                        <li><span class="info-label">Full Name</span> <span
                            class="info-value"><?php echo htmlentities(trim($row->FirstName . ' ' . $row->MiddleName . ' ' . $row->FamilyName)); ?></span>
                        </li>
                        <li><span class="info-label">Date of Birth</span> <span
                            class="info-value"><?php echo htmlentities($row->DOB); ?></span></li>
                        <li><span class="info-label">Gender</span> <span
                            class="info-value"><?php echo htmlentities($row->Gender); ?></span></li>
                        <li><span class="info-label">Civil Status</span> <span
                            class="info-value"><?php echo htmlentities($row->CivilStatus); ?></span></li>
                        <li><span class="info-label">Citizenship</span> <span
                            class="info-value"><?php echo htmlentities($row->Citizenship); ?></span></li>
                        <li><span class="info-label">Religion</span> <span
                            class="info-value"><?php echo htmlentities($row->Religion); ?></span></li>
                      </ul>
                    </div>

                    <!-- Academic Details Section -->
                    <div class="profile-section">
                      <h5 class="profile-section-title"><i class="fas fa-graduation-cap"></i>Academic Details</h5>
                      <ul class="profile-info-list">
                        <li><span class="info-label">Program</span> <span
                            class="info-value"><?php echo htmlentities($row->Program); ?></span></li>
                        <li><span class="info-label">Major</span> <span
                            class="info-value"><?php echo htmlentities($row->Major); ?></span></li>
                        <li><span class="info-label">Year Level</span> <span
                            class="info-value"><?php echo htmlentities($row->YearLevel); ?></span></li>
                        <li><span class="info-label">LRN</span> <span
                            class="info-value"><?php echo htmlentities($row->LearnersReferenceNo); ?></span></li>
                        <li><span class="info-label">Category</span> <span
                            class="info-value"><?php echo htmlentities($row->Category); ?></span></li>
                      </ul>
                    </div>

                    <!-- Contact & Address Section -->
                    <div class="profile-section">
                      <h5 class="profile-section-title"><i class="fas fa-map-marker-alt"></i>Contact & Address</h5>
                      <ul class="profile-info-list">
                        <li><span class="info-label">Email</span> <span
                            class="info-value"><?php echo htmlentities($row->EmailAddress); ?></span></li>
                        <li><span class="info-label">Phone</span> <span
                            class="info-value"><?php echo htmlentities($row->ContactNumber); ?></span></li>
                        <li><span class="info-label">Address</span> <span class="info-value">
                            <?php
                            $address = implode(', ', array_filter([
                              $row->BuildingHouseNumber,
                              $row->StreetName,
                              $row->Barangay,
                              $row->CityMunicipality,
                              $row->Province,
                              $row->PostalCode
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
                        <li><span class="info-label">Name</span> <span
                            class="info-value"><?php echo htmlentities($row->EmergencyContactPerson); ?></span></li>
                        <li><span class="info-label">Relationship</span> <span
                            class="info-value"><?php echo htmlentities($row->EmergencyRelationship); ?></span></li>
                        <li><span class="info-label">Phone</span> <span
                            class="info-value"><?php echo htmlentities($row->EmergencyContactNumber); ?></span></li>
                        <li><span class="info-label">Address</span> <span
                            class="info-value"><?php echo htmlentities($row->EmergencyAddress); ?></span></li>
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
                  </div>

                </div>
              <?php } else {
                echo "<div class='alert alert-danger'>Student not found.</div>";
              } ?>
            </div>
          </div>


        </div>
      </div>
    </div>
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script src="js/toast.js"></script>
</body>

</html>