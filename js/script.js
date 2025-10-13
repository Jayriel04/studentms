jQuery(function ($) {
  // Smooth scroll for links that have a hash target (works if href="#someID")
  $(document).on('click', '.scroll', function (event) {
    var targetHash = this.hash || $(this).attr('href');
    if (!targetHash || targetHash === '#') return;
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

  // Notice Modal Logic
  $(document).on('click', '.modern-notice-link', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    if (!id) {
      console.warn('notice id missing');
      return;
    }

    // show loader immediately
    $('#noticeModalLabel .notice-title-text').text('Loading...');
    $('#noticeModalBody').html('<div class="text-center p-4"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</div>');
    $('#noticeModalDate').text('');
    $('#noticeModal').modal('show');

    var $data = $('#notice-data-' + id);
    var content = '<div class="text-muted">No content</div>';

    if ($data.length) {
      var tplEl = $data.get(0);
      try {
        if (tplEl && tplEl.content) {
          // <template> support
          content = tplEl.content.innerHTML.trim() || content;
        } else {
          // fallback (e.g. <div class="d-none">) or browser without template
          content = $data.html().trim() || content;
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
});