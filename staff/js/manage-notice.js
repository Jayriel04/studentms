(function () {
  document.addEventListener('DOMContentLoaded', function () {
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

    // Initialize mention for add modal textarea
    var notmsg = document.getElementById('notmsg_modal');
    if (notmsg && typeof initializeMention === 'function') {
      initializeMention(notmsg, 'search.php?mention_suggest=1');
    }

    // Initialize mention for edit modal textarea
    var editMsg = document.getElementById('edit_notmsg_modal');
    if (editMsg && typeof initializeMention === 'function') {
      initializeMention(editMsg, 'search.php?mention_suggest=1');
    }

    // Wire edit buttons to populate modal
    var editButtons = document.querySelectorAll('.btn-edit-notice');
    editButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = this.getAttribute('data-id');
        var title = this.getAttribute('data-title');
        var msg = this.getAttribute('data-msg');

        document.getElementById('edit_id_modal').value = id;
        document.getElementById('edit_nottitle_modal').value = title;

        // Decode HTML entities if present
        try {
          var decoded = msg
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>')
            .replace(/&quot;/g, '"')
            .replace(/&#039;/g, "'")
            .replace(/&amp;/g, '&');
          document.getElementById('edit_notmsg_modal').value = decoded;
        } catch (e) {
          document.getElementById('edit_notmsg_modal').value = msg;
        }

        openModal('editNoticeModalOverlay');
      });
    });

    // Event listeners for the new add notice modal
    var openAddBtn = document.querySelector('[data-target="#addNoticeModal"]');
    if (openAddBtn) {
      openAddBtn.addEventListener('click', function (e) {
        e.preventDefault();
        openModal('addNoticeModalOverlay');
      });
    }

    var closeBtns = document.querySelectorAll('#addNoticeModalOverlay .new-close-btn, #addNoticeModalOverlay .new-btn-cancel');
    closeBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        closeModal('addNoticeModalOverlay');
      });
    });

    // Event listeners for the new edit notice modal
    var closeEditBtns = document.querySelectorAll('#editNoticeModalOverlay .new-close-btn, #editNoticeModalOverlay .new-btn-cancel');
    closeEditBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        closeModal('editNoticeModalOverlay');
      });
    });

  });
})();