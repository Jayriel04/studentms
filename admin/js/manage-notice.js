(function () {
  var data = window.mnData || {};

  // Toastr notifications
  if (typeof toastr !== 'undefined') {
    toastr.options = { "positionClass": "toast-top-right", "closeButton": true };
    if (data.delete_message) toastr.success(data.delete_message);
    if (data.add_success_message) toastr.success(data.add_success_message);
    if (data.add_error_message) toastr.error(data.add_error_message);
    if (data.edit_success_message) toastr.success(data.edit_success_message);
    if (data.edit_error_message) toastr.error(data.edit_error_message);
  }

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
    // initialize mention fields if mention.js is available
    try {
      var addMsg = document.getElementById('notmsg_modal');
      if (addMsg && typeof initializeMention === 'function') initializeMention(addMsg, 'search.php?mention_suggest=1');
      var editMsg = document.getElementById('edit_notmsg_modal');
      if (editMsg && typeof initializeMention === 'function') initializeMention(editMsg, 'search.php?mention_suggest=1');
    } catch (e) { /* ignore */ }

    // Open add modal if server reported add error
    if (data.openAddModal) {
      openModal('addNoticeModalOverlay');
    }

    // Reopen edit modal and populate fields if server requested it
    if (data.openEditModal) {
      try {
        document.getElementById('edit_id_modal').value = data.editPost.id || '';
        document.getElementById('edit_nottitle_modal').value = data.editPost.title || '';
        document.getElementById('edit_notmsg_modal').value = data.editPost.msg || '';
      } catch (e) { /* ignore */ }
      openModal('editNoticeModalOverlay');
    }

    // Wire edit buttons to populate and open modal
    var editButtons = document.querySelectorAll('.btn-edit-notice');
    editButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = this.getAttribute('data-id') || '';
        var title = this.getAttribute('data-title') || '';
        var msg = this.getAttribute('data-msg') || '';

        try {
          document.getElementById('edit_id_modal').value = id;
          document.getElementById('edit_nottitle_modal').value = title;
          document.getElementById('edit_notmsg_modal').value = msg;
        } catch (e) { /* ignore */ }

        openModal('editNoticeModalOverlay');
      });
    });

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

    // Event listeners for the new edit notice modal
    var closeEditBtns = document.querySelectorAll('#editNoticeModalOverlay .new-close-btn, #editNoticeModalOverlay .new-btn-cancel');
    closeEditBtns.forEach(function(btn) {
      btn.addEventListener('click', function() {
        closeModal('editNoticeModalOverlay');
      });
    });
  });
})();