// Moved from the inline <script> block at the end of manage-public-notice.php

(function () {
  var data = window.ppnData || {};

  // Toastr notifications
  if (typeof toastr !== 'undefined') {
    toastr.options = { positionClass: 'toast-top-right', closeButton: true };
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
    var el = document.getElementById(modalId);
    if (!el) return;
    el.classList.remove('active');
    document.body.style.overflow = 'auto';
  }

  function closeOverlay(id) {
    var el = document.getElementById(id);
    if (!el) return;
    el.classList.remove('active');
  }

  function decodeEntities(encodedString) {
    var textarea = document.createElement('textarea');
    textarea.innerHTML = encodedString || '';
    return textarea.value;
  }

  document.addEventListener('DOMContentLoaded', function () {
    // Open add modal if server reported add validation error
    if (data.openAddModal) {
      openModal('addPublicModalOverlay');
    }

    // Reopen edit modal and populate fields if server requested it (bootstrap modal)
    if (data.openEditModal) {
      try {
        document.getElementById('edit_id_public_modal').value = data.editPost.id || '';
        document.getElementById('edit_nottitle_public_modal').value = data.editPost.title || '';
        document.getElementById('edit_notmsg_public_modal').value = data.editPost.msg || '';
      } catch (e) { /* ignore missing fields */ }
      openModal('editPublicModalOverlay');
    }

    // Wire edit buttons to populate bootstrap edit modal
    var editButtons = document.querySelectorAll('.btn-edit-public');
    editButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = this.getAttribute('data-id') || '';
        var title = this.getAttribute('data-title') || '';
        var msg = this.getAttribute('data-msg') || '';

        try {
          document.getElementById('edit_id_public_modal').value = id;
          document.getElementById('edit_nottitle_public_modal').value = title;
          document.getElementById('edit_notmsg_public_modal').value = decodeEntities(msg);
        } catch (e) { /* ignore */ }

        // Open bootstrap modal
        openModal('editPublicModalOverlay');
      });
    });

    // Event listeners for the new add public notice overlay
    var openAddBtn = document.querySelector('[data-target="#addPublicModalOverlay"], .add-btn');
    if (openAddBtn) {
      openAddBtn.addEventListener('click', function (e) {
        e.preventDefault();
        openModal('addPublicModalOverlay');
      });
    }

    var closeBtns = document.querySelectorAll('#addPublicModalOverlay .new-close-btn, #addPublicModalOverlay .new-btn-cancel');
    closeBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        closeModal('addPublicModalOverlay');
      });
    });

    // Event listeners for the new edit public notice modal
    var closeEditBtns = document.querySelectorAll('#editPublicModalOverlay .new-close-btn, #editPublicModalOverlay .new-btn-cancel');
    closeEditBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        closeModal('editPublicModalOverlay');
      });
    });

  });
})();