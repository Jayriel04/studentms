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

    // Wire edit buttons to populate and show modal
    var editButtons = document.querySelectorAll('.btn-edit-public');
    editButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = this.getAttribute('data-id') || '';
        var title = this.getAttribute('data-title') || '';
        var msg = this.getAttribute('data-msg') || '';

        try {
          document.getElementById('edit_id_public_modal').value = id;
          document.getElementById('edit_nottitle_public_modal').value = title;
          
          // Decode HTML entities if present
          var textarea = document.createElement('textarea');
          textarea.innerHTML = msg;
          document.getElementById('edit_notmsg_public_modal').value = textarea.value;
        } catch (e) { /* ignore */ }

        // Show edit modal
        openModal('editPublicModalOverlay');
      });
    });

    // Event listeners for the new add public notice modal
    var openAddBtn = document.querySelector('[data-target="#addPublicModal"]');
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