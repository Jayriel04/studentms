(function () {
  var data = window.srData || {};

  // Helpers for new modal
  function openModal(modalId) {
    var overlay = document.getElementById(modalId);
    if (overlay) overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeModal(modalId) {
    var overlay = document.getElementById(modalId);
    if (overlay) overlay.classList.remove('active');
    document.body.style.overflow = 'auto';
  }

  document.addEventListener('DOMContentLoaded', function () {
    // Wire message modal buttons
    var messageButtons = document.querySelectorAll('.message-btn');
    messageButtons.forEach(function (button) {      
      button.addEventListener('click', function () {
        var email = this.getAttribute('data-email') || '';
        var stuid = this.getAttribute('data-stuid') || '';
        var name = this.getAttribute('data-name') || '';
        var emailField = document.getElementById('studentEmail');
        var stuidField = document.getElementById('studentStuID');
        var nameEl = document.getElementById('studentName');
        if (emailField) emailField.value = email;
        if (stuidField) stuidField.value = stuid;
        if (nameEl) nameEl.innerText = name;        
        openModal('messageModalOverlay');
      });
    });

    // Initialize mention functionality on the notice message textarea
    var notemsgTextarea = document.getElementById('notmsg');
    if (notemsgTextarea && typeof initializeMention === 'function') {
      initializeMention(notemsgTextarea, 'search.php?mention_suggest=1');
    }

    // Toastr notifications from server
    if (typeof toastr !== 'undefined') {
      if (data.flash_message) {
        toastr.success(data.flash_message);
      }
      if (data.flash_message_error) {
        toastr.error(data.flash_message_error);
      }
    }

    // Event listeners for the new add notice modal
    var openAddBtn = document.querySelector('[data-target="#addNoticeModal"]');
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
  });
})();