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

  function showModalById(id) {
    var el = document.getElementById(id);
    if (!el) return;
    if (window.$) { $('#' + id).modal('show'); }
    else if (typeof bootstrap !== "undefined") {
      var modal = new bootstrap.Modal(el);
      modal.show();
    }
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
      showModalById('addNoticeModal');
    }

    // Reopen edit modal and populate fields if server requested it
    if (data.openEditModal) {
      try {
        document.getElementById('edit_id_modal').value = data.editPost.id || '';
        document.getElementById('edit_nottitle_modal').value = data.editPost.title || '';
        document.getElementById('edit_notmsg_modal').value = data.editPost.msg || '';
      } catch (e) { /* ignore */ }
      showModalById('editNoticeModal');
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

        showModalById('editNoticeModal');
      });
    });
  });
})();