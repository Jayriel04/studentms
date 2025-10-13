<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
?>
<!doctype html>
<html>

<head>
  <title>Student Profiling System || Home Page</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <script
    type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
  <!--bootstrap-->
  <link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all">
  <!--coustom css-->
  <link href="css/style.css" rel="stylesheet" type="text/css" />
  <!--script-->
  <script src="js/jquery-1.11.0.min.js"></script>
  <!-- js -->
  <script src="js/bootstrap.js"></script>
  <!-- /js -->
  <!--fonts-->
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link
    href='//fonts.googleapis.com/css?family=Open+Sans:300,300italic,400italic,400,600,600italic,700,700italic,800,800italic'
    rel='stylesheet' type='text/css'>
  <!--/fonts-->
  <!--hover-girds-->
  <link rel="stylesheet" type="text/css" href="css/default.css" />
  <link rel="stylesheet" type="text/css" href="css/component.css" />
  <script src="js/modernizr.custom.js"></script>
  <!--/hover-grids-->
  <script type="text/javascript" src="js/move-top.js"></script>
  <script type="text/javascript" src="js/easing.js"></script>
  <script src="js/script.js"></script>
</head>

<body>
  <div class="top" id="top"></div>
  <?php include_once('includes/header.php'); ?>
  <div class="modern-hero-section">
    <div class="modern-hero-overlay">
      <div class="modern-hero-content">
        <h1 class="modern-hero-title">Student Profiling System</h1>
        <p class="modern-hero-desc">Students can Login Here</p>
        <a class="modern-btn" href="user/login.php">Student Login <i class="glyphicon glyphicon-menu-right"></i></a>
      </div>
    </div>
  </div>
  <div id="about" class="modern-section">
    <div class="modern-section-container">
      <!-- Wrapper to hold the two cards side-by-side -->
      <div class="modern-split-wrapper">

        <!-- About Us Card (Left) -->
        <div class="modern-card about-card">
            <?php
            $sql = "SELECT * from tblpage where PageType='aboutus'";
            $query = $dbh->prepare($sql);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);
            if ($query->rowCount() > 0) {
              foreach ($results as $row) { ?>
                <h2 class="modern-section-title" style="text-align: left;"><?php echo htmlentities($row->PageTitle); ?></h2>
                <div class="modern-section-desc" style="text-align: left;"><?php echo ($row->PageDescription); ?></div>
            <?php }
            } ?>
        </div>

        <!-- Image Card (Right) -->
        <div class="modern-card image-card">
            <img src="images/mcc_logo2.jpg" alt="Campus Life" style="width: 100%; border-radius: 16px; box-shadow: 0 6px 24px rgba(39, 147, 253, 0.2);">
        </div>

      </div>
    </div>
  </div>

  <div id="notice" class="modern-notices-section">
    <div class="modern-section-container">
      <!-- Notices Card -->
      <div class="modern-card modern-notice-card">
        <h3 class="modern-section-title">Public Notices</h3>
        <div class="modern-notices-list-wrapper">
          <div class="modern-notices-list">
            <?php
            $sql = "SELECT * from tblpublicnotice";
            $query = $dbh->prepare($sql);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);

            if ($query->rowCount() > 0) {
              foreach ($results as $row) { ?>
                <a class="modern-notice-link" href="#" data-id="<?php echo (int)$row->ID; ?>">
                  <div class="modern-notice-title"><?php echo htmlentities($row->NoticeTitle); ?></div>
                  <div class="modern-notice-date"><?php echo htmlentities($row->CreationDate); ?></div>
                </a>
                <!-- Hidden template for modal content -->
                <template id="notice-data-<?php echo (int)$row->ID; ?>">
                  <?php echo $row->NoticeMessage; ?>
                </template>
            <?php }
            } else {
              echo '<p class="text-muted">No public notices at this time.</p>';
            } ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="contact" class="modern-section" style="padding-top: 0;">
    <div class="modern-section-container">
        <!-- Contact Card -->
        <div class="modern-card" style="align-items: flex-start;">
          <h3 class="modern-section-title">Contact Information</h3>
          <?php
          $sql_contact = "SELECT * from tblpage where PageType='contactus'";
          $query_contact = $dbh->prepare($sql_contact);
          $query_contact->execute();
          $results_contact = $query_contact->fetchAll(PDO::FETCH_OBJ);
          if ($query_contact->rowCount() > 0) {
            foreach ($results_contact as $row_contact) { ?>
              <div style="line-height: 1.8; color: #3d4857;">
                <div class="modern-footer-contact-detail" style="margin-bottom: 0.5em;"><b><i class="fas fa-map-marker-alt fa-fw" style="color:#2793fd;"></i> Address:</b> <?php echo $row_contact->PageDescription; ?></div>
                <div class="modern-footer-contact-detail" style="margin-bottom: 0.5em;"><b><i class="fas fa-phone fa-fw" style="color:#2793fd;"></i> Phone:</b> <?php echo htmlentities($row_contact->MobileNumber); ?></div>
                <div class="modern-footer-contact-detail"><b><i class="fas fa-envelope fa-fw" style="color:#2793fd;"></i> Email:</b> <?php echo htmlentities($row_contact->Email); ?></div>
              </div>
          <?php }
          } ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Notice Modal -->
  <div class="modal fade" id="noticeModal" tabindex="-1" role="dialog" aria-labelledby="noticeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document" aria-describedby="noticeModalBody">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="noticeModalLabel">
            <span class="notice-icon" aria-hidden="true"><i class="fas fa-bullhorn"></i></span>
            <span class="notice-title-text">Loading...</span>
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" title="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="noticeModalBody" class="p-2">
            <div class="text-center">Please wait...</div>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between align-items-center">
          <small class="notice-meta" id="noticeModalDate"></small>
          <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <?php include_once('includes/footer.php'); ?>
</body>

</html>
