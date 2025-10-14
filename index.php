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
  <link rel="icon" href="https://img.icons8.com/color/480/student-vue.png" type="image/png" sizes="180x180">
  <!--script-->
  <script src="js/jquery-1.11.0.min.js"></script>
  <!-- js -->
  <script src="js/bootstrap.js"></script>
  <!-- /js -->
  <!--fonts-->
  <link rel="stylesheet" type="text/css"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link
    href='https://fonts.googleapis.com/css?family=Open+Sans:300,300italic,400italic,400,600,600italic,700,700italic,800,800italic'
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
          <img src="images/group.png" alt="Campus Life"
            style="width: 100%; border-radius: 16px; box-shadow: 0 6px 24px rgba(39, 147, 253, 0.2);">
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
                <a class="modern-notice-link" href="#" data-id="<?php echo (int) $row->ID; ?>">
                  <div class="modern-notice-title"><?php echo htmlentities($row->NoticeTitle); ?></div>
                  <div class="modern-notice-date"><?php echo htmlentities($row->CreationDate); ?></div>
                </a>
                <!-- Hidden container for modal content -->
                <div id="notice-data-<?php echo (int) $row->ID; ?>" style="display:none;">
                  <?php echo $row->NoticeMessage; ?>
                </div>
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
      <!-- Contact Section Card -->
      <div class="modern-card" style="max-width: 1200px; padding: 0; overflow: hidden;">
        <div class="modern-contact-wrapper">
          <!-- Left side with contact info -->
          <div class="modern-contact-info">
            <h3 class="modern-section-title" style="text-align: left; margin-bottom: 1.5em;">Get in Touch</h3>
            <?php
            $sql_contact = "SELECT * from tblpage where PageType='contactus'";
            $query_contact = $dbh->prepare($sql_contact);
            $query_contact->execute();
            $results_contact = $query_contact->fetchAll(PDO::FETCH_OBJ);
            if ($query_contact->rowCount() > 0) {
              foreach ($results_contact as $row_contact) { ?>
                <div class="contact-item">
                  <i class="fas fa-map-marker-alt contact-icon"></i>
                  <div>
                    <div class="contact-label">Address</div>
                    <div class="contact-value"><?php echo $row_contact->PageDescription; ?></div>
                  </div>
                </div>
                <div class="contact-item">
                  <i class="fas fa-phone contact-icon"></i>
                  <div>
                    <div class="contact-label">Phone</div>
                    <div class="contact-value"><?php echo htmlentities($row_contact->MobileNumber); ?></div>
                  </div>
                </div>
                <div class="contact-item">
                  <i class="fas fa-envelope contact-icon"></i>
                  <div>
                    <div class="contact-label">Email</div>
                    <div class="contact-value"><?php echo htmlentities($row_contact->Email); ?></div>
                  </div>
                </div>
              <?php }
            } ?>
          </div>
          <!-- Right side with map -->
          <div class="modern-contact-map">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3925.223907187851!2d123.9389304750355!3d10.323957889798617!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33a999b1bf111e65%3A0xf045169ca1950d6c!2sMandaue%20City%20College!5e0!3m2!1sen!2sph!4v1670000000000!5m2!1sen!2sph"
              width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Notice Modal -->
  <div class="modal fade" id="noticeModal" tabindex="-1" role="dialog" aria-labelledby="noticeModalLabel"
    aria-hidden="true">
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