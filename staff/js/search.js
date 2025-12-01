(function () {
  // Modal helper functions (fixes "openModal is not defined" / "closeModal is not defined")
  function openModal(modalId) {
    var el = document.getElementById(modalId);
    if (!el) return;
    el.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeModal(modalId) {
    var el = document.getElementById(modalId);
    if (!el) return;
    el.classList.remove('active');
    document.body.style.overflow = 'auto';
  }

  document.addEventListener('DOMContentLoaded', function () {
    // Wire message modal buttons to populate fields
    var messageButtons = document.querySelectorAll('.message-btn');
    messageButtons.forEach(function(button) {
      button.addEventListener('click', function() {
        document.getElementById('studentEmail').value = this.getAttribute('data-email') || '';
        document.getElementById('studentName').innerText = this.getAttribute('data-name') || '';
        document.getElementById('studentStuID').value = this.getAttribute('data-stuid') || '';
        openModal('messageModalOverlay');
      });
    });

    // Initialize mention functionality on the notice message textarea
    var notemsgTextarea = document.getElementById('notmsg');
    if (notemsgTextarea && typeof initializeMention === 'function') {
      initializeMention(notemsgTextarea, 'search.php?mention_suggest=1');
    }

    // Display toastr notifications from server
    if (typeof toastr !== 'undefined') {
      var flashMsg = document.querySelector('[data-flash-message]');
      var flashError = document.querySelector('[data-flash-error]');
      
      if (flashMsg && flashMsg.getAttribute('data-flash-message')) {
        toastr.success(flashMsg.getAttribute('data-flash-message'));
      }
      if (flashError && flashError.getAttribute('data-flash-error')) {
        toastr.error(flashError.getAttribute('data-flash-error'));
      }
    }

    // Event listeners for the new add notice modal
    // Accept both bootstrap-style data-target and the custom overlay id, plus .add-btn
    var openAddBtn = document.querySelector('[data-target="#addNoticeModal"], [data-target="#addNoticeModalOverlay"], .add-btn');
    if (openAddBtn) {
      openAddBtn.addEventListener('click', function(e) {
        e.preventDefault();
        openModal('addNoticeModalOverlay');
      });
    }

    var closeBtns = document.querySelectorAll('#addNoticeModalOverlay .new-close-btn, #addNoticeModalOverlay .new-btn-cancel');
    closeBtns.forEach(function(btn) {
      btn.addEventListener('click', function() {
        closeModal('addNoticeModalOverlay');
      });
    });

    // Event listeners for the new message modal
    var closeMessageBtns = document.querySelectorAll('#messageModalOverlay .new-close-btn, #messageModalOverlay .new-btn-cancel');
    closeMessageBtns.forEach(function(btn) {
      btn.addEventListener('click', function() {
        closeModal('messageModalOverlay');
      });
    });

    // allow clicking backdrop to close overlays
    var addOverlay = document.getElementById('addNoticeModalOverlay');
    if (addOverlay) {
      addOverlay.addEventListener('click', function(e) {
        if (e.target === addOverlay) closeModal('addNoticeModalOverlay');
      });
    }
    var msgOverlay = document.getElementById('messageModalOverlay');
    if (msgOverlay) {
      msgOverlay.addEventListener('click', function(e) {
        if (e.target === msgOverlay) closeModal('messageModalOverlay');
      });
    }
  });
})();