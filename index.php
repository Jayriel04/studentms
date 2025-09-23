<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
?>
<!doctype html>
<html>

<head>
  <title>Student Profiling System || Home Page</title>
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
  <!--script-->
  <script type="text/javascript">
    jQuery(document).ready(function ($) {
      $(".scroll").click(function (event) {
        event.preventDefault();
        $('html,body').animate({ scrollTop: $(this.hash).offset().top }, 900);
      });
    });
  </script>
  <!--/script-->
</head>

<body>
  <?php include_once('includes/header.php'); ?>
  <div class="modern-hero-section">
    <div class="modern-hero-overlay">
      <div class="modern-hero-content">
        <h1 class="modern-hero-title">Student Profiling System</h1>
        <p class="modern-hero-desc">Registered Students can Login Here</p>
        <a class="modern-btn" href="user/login.php">Student Login <i class="glyphicon glyphicon-menu-right"></i></a>
      </div>
    </div>
  </div>
  <div class="modern-section">
    <div class="modern-section-container">
      <?php
      $sql = "SELECT * from tblpage where PageType='aboutus'";
      $query = $dbh->prepare($sql);
      $query->execute();
      $results = $query->fetchAll(PDO::FETCH_OBJ);

      $cnt = 1;
      if ($query->rowCount() > 0) {
        foreach ($results as $row) { ?>
          <div class="modern-card">
            <h2 class="modern-section-title"><?php echo htmlentities($row->PageTitle); ?></h2>
            <div class="modern-section-desc"><?php echo ($row->PageDescription); ?></div>
          </div>
          <?php $cnt = $cnt + 1;
        }
      } ?>
    </div>
  </div>

  <div class="modern-notices-section">
    <div class="modern-section-container">
      <div class="modern-card modern-notice-card">
        <h3 class="modern-section-title">Public Notices</h3>
        <div class="modern-notices-list-wrapper">
          <div class="modern-notices-list">
            <?php
            $sql = "SELECT * from tblpublicnotice";
            $query = $dbh->prepare($sql);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);

            $cnt = 1;
            if ($query->rowCount() > 0) {
              foreach ($results as $row) { ?>
                <a class="modern-notice-link" href="view-public-notice.php?viewid=<?php echo htmlentities($row->ID); ?>"
                  target="_blank">
                  <div class="modern-notice-title"><?php echo htmlentities($row->NoticeTitle); ?></div>
                  <div class="modern-notice-date"><?php echo htmlentities($row->CreationDate); ?></div>
                </a>
                <?php $cnt = $cnt + 1;
              }
            } ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include_once('includes/footer.php'); ?>
</body>

</html>