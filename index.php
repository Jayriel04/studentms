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
    jQuery(function ($) {
      // Smooth scroll for links that have a hash target (works if href="#someID")
      $(document).on('click', '.scroll', function (event) {
        var targetHash = this.hash || $(this).attr('href');
        if (!targetHash || targetHash === '#' ) return;
        var $target = $(targetHash);
        if ($target.length) {
          event.preventDefault();
          $('html, body').animate({ scrollTop: $target.offset().top }, 700);
        }
      });

      // Back-to-top button: show after scrolling down, animate to top on click
      var $backBtn = $('<a/>', {
        id: 'back-to-top',
        href: '#top',
        title: 'Back to top',
        'aria-label': 'Back to top',
        class: 'modern-back-to-top',
        html: '&#8679;',
        css: {
          display: 'none',
          position: 'fixed',
          right: '18px',
          bottom: '18px',
          width: '42px',
          height: '42px',
          'line-height': '42px',
          'text-align': 'center',
          'background-color': '#0b61d6',
          color: '#fff',
          'border-radius': '6px',
          'z-index': 9999,
          cursor: 'pointer',
          'box-shadow': '0 6px 18px rgba(11,97,214,0.12)'
        }
      }).appendTo('body');

      $(window).on('scroll.backToTop', function () {
        if ($(this).scrollTop() > 200) {
          $backBtn.fadeIn(200);
        } else {
          $backBtn.fadeOut(200);
        }
      });

      $backBtn.on('click', function (e) {
        e.preventDefault();
        $('html, body').animate({ scrollTop: 0 }, 600);
      });
    });
  </script>

  <!-- Improved Notice Modal styles -->
  <style>
    /* Notice modal UI improvements (scoped) */
    #noticeModal .modal-content {
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 12px 30px rgba(6, 40, 99, 0.12);
      border: none;
      background: #ffffff;
    }
    #noticeModal .modal-header {
      background: linear-gradient(90deg, #e6f6ff 0%, #dff2ff 100%);
      border-bottom: none;
      padding: 18px 20px;
    }
    #noticeModal .modal-title {
      color: #0b61d6;
      font-weight: 700;
      font-size: 1.1rem;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    #noticeModal .notice-icon {
      width: 40px;
      height: 40px;
      background: #ffffff;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 12px rgba(11,97,214,0.08);
      color: #0b61d6;
      font-size: 18px;
    }
    #noticeModal .modal-body {
      padding: 20px;
      max-height: 62vh;
      overflow: auto;
      color: #083044;
      line-height: 1.6;
      font-size: 0.98rem;
      background: #ffffff;
    }
    #noticeModal .modal-footer {
      background: transparent;
      border-top: none;
      padding: 12px 20px;
    }
    #noticeModal .close {
      opacity: 0.8;
      color: #083044;
    }
    #noticeModal .close:hover { opacity: 1; }
    .notice-meta { font-size: 0.85rem; color: #486e8f; }
    .notice-content img { max-width: 100%; height: auto; border-radius: 6px; margin: 10px 0; }
    .notice-content p { margin: 0 0 0.85rem; }
    @media (max-width: 576px) {
      #noticeModal .modal-dialog { max-width: 95%; margin: 12px; }
      #noticeModal .modal-body { max-height: 50vh; padding: 14px; }
    }
  </style>
</head>

<body>
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
          <div class="modern-notices-list" style="height: 200px; width: 80vh;">
            <?php
            $sql = "SELECT * from tblpublicnotice";
            $query = $dbh->prepare($sql);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);

            $cnt = 1;
            if ($query->rowCount() > 0) {
              foreach ($results as $row) { ?>
                <a class="modern-notice-link" href="#" data-id="<?php echo (int)$row->ID; ?>">
                  <div class="modern-notice-title"><?php echo htmlentities($row->NoticeTitle); ?></div>
                  <div class="modern-notice-date"><?php echo htmlentities($row->CreationDate); ?></div>
                </a>
                <!-- Hidden DIV for modal content (not visible on page; used by JS) -->
                <div class="notice-data d-none" id="notice-data-<?php echo (int)$row->ID; ?>" aria-hidden="true" style="display:none !important;">
                  <?php echo $row->NoticeMessage; ?>
                </div>
                <?php $cnt = $cnt + 1;
              }
            } ?>
          </div>
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

  <script>
    (function ($) {
      $(document).on('click', '.modern-notice-link', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        console.log('notice clicked, id=', id);
        if (!id) {
          console.warn('notice id missing');
          return;
        }

        // show loader immediately
        $('#noticeModalLabel .notice-title-text').text('Loading...');
        $('#noticeModalBody').html('<div class="text-center p-4"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</div>');
        $('#noticeModalDate').text('');
        $('#noticeModal').modal('show');

        // debug: confirm template exists
        var $data = $('#notice-data-' + id);
        console.log('$data length:', $data.length);

        var content = '<div class="text-muted">No content</div>';

        if ($data.length) {
          var tplEl = $data.get(0);
          try {
            if (tplEl && tplEl.content) {
              // <template> support
              content = tplEl.content.innerHTML.trim() || content;
              console.log('template content length:', content.length);
            } else {
              // fallback (e.g. <div class="d-none">) or browser without template
              content = $data.html().trim() || content;
              console.log('fallback html length:', content.length);
            }
          } catch (err) {
            console.error('error reading template content', err);
          }

          // fill title and date from link
          var title = $('.modern-notice-link[data-id="' + id + '"] .modern-notice-title').text().trim() || 'Notice';
          var dateText = $('.modern-notice-link[data-id="' + id + '"] .modern-notice-date').text().trim();
          $('#noticeModalLabel .notice-title-text').text(title);
          $('#noticeModalBody').html('<div class="notice-content">' + content + '</div>');
          $('#noticeModalDate').text(dateText ? 'Posted: ' + dateText : '');
        } else {
          console.error('notice-data element not found for id', id);
          $('#noticeModalLabel .notice-title-text').text('Error');
          $('#noticeModalBody').html('<div class="text-danger">Notice content not found.</div>');
        }
      });
    })(jQuery);
  </script>

  <?php include_once('includes/footer.php'); ?>
</body>

</html>