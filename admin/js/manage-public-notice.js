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

  function showModalById(id) {
    var el = document.getElementById(id);
    if (!el) return;
    if (window.$) { $('#' + id).modal('show'); }
    else if (typeof bootstrap !== 'undefined') {
      var modal = new bootstrap.Modal(el);
      modal.show();
    }
  }

  function decodeEntities(encodedString) {
    var textarea = document.createElement('textarea');
    textarea.innerHTML = encodedString;
    return textarea.value;
  }

  document.addEventListener('DOMContentLoaded', function () {
    // Open add modal if server reported add validation error
    if (data.openAddModal) {
      showModalById('addPublicModal');
    }

    // Reopen edit modal and populate fields if server requested it
    if (data.openEditModal) {
      try {
        document.getElementById('edit_id_public_modal').value = data.editPost.id || '';
        document.getElementById('edit_nottitle_public_modal').value = data.editPost.title || '';
        document.getElementById('edit_notmsg_public_modal').value = data.editPost.msg || '';
      } catch (e) { /* ignore missing fields */ }
      showModalById('editPublicModal');
    }

    // Wire edit buttons to populate modal
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

        showModalById('editPublicModal');
      });
    });
  });
})();