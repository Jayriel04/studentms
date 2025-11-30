(function () {
  document.addEventListener('DOMContentLoaded', function () {
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

    // Show bootstrap toasts if messages exist and handle modal visibility
    var addSuccessToast = document.getElementById('addSuccessToast');
    if (addSuccessToast) {
      if (window.$) {
        $(addSuccessToast).toast('show');
      } else if (typeof bootstrap !== 'undefined') {
        new bootstrap.Toast(addSuccessToast).show();
      }
      if (window.$) {
        $('#addNoticeModal').modal('hide');
      } else if (typeof bootstrap !== 'undefined') {
        try {
          new bootstrap.Modal(document.getElementById('addNoticeModal')).hide();
        } catch (e) { /* ignore */ }
      }
    }

    var addErrorToast = document.getElementById('addErrorToast');
    if (addErrorToast) {
      if (window.$) {
        $(addErrorToast).toast('show');
      } else if (typeof bootstrap !== 'undefined') {
        new bootstrap.Toast(addErrorToast).show();
      }
      if (window.$) {
        $('#addNoticeModal').modal('show');
      } else if (typeof bootstrap !== 'undefined') {
        new bootstrap.Modal(document.getElementById('addNoticeModal')).show();
      }
    }

    var editSuccessToast = document.getElementById('editSuccessToast');
    if (editSuccessToast) {
      if (window.$) {
        $(editSuccessToast).toast('show');
      } else if (typeof bootstrap !== 'undefined') {
        new bootstrap.Toast(editSuccessToast).show();
      }
      if (window.$) {
        $('#editNoticeModal').modal('hide');
      } else if (typeof bootstrap !== 'undefined') {
        try {
          new bootstrap.Modal(document.getElementById('editNoticeModal')).hide();
        } catch (e) { /* ignore */ }
      }
    }

    var editErrorToast = document.getElementById('editErrorToast');
    if (editErrorToast) {
      if (window.$) {
        $(editErrorToast).toast('show');
      } else if (typeof bootstrap !== 'undefined') {
        new bootstrap.Toast(editErrorToast).show();
      }
      if (window.$) {
        $('#editNoticeModal').modal('show');
      } else if (typeof bootstrap !== 'undefined') {
        new bootstrap.Modal(document.getElementById('editNoticeModal')).show();
      }
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

        if (window.$) {
          $('#editNoticeModal').modal('show');
        } else if (typeof bootstrap !== 'undefined') {
          new bootstrap.Modal(document.getElementById('editNoticeModal')).show();
        }
      });
    });
  });
})();