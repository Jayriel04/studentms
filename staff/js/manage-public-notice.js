(function () {
  document.addEventListener('DOMContentLoaded', function () {
    // Show toastr notifications if messages exist
    if (typeof toastr !== 'undefined') {
      toastr.options = { positionClass: 'toast-top-right', closeButton: true };
      
      var addSuccessMsg = document.querySelector('.modal-body .alert-success');
      if (addSuccessMsg) {
        var msg = addSuccessMsg.innerText;
        if (msg) toastr.success(msg);
      }

      var addErrorMsg = document.querySelector('.modal-body .alert-danger');
      if (addErrorMsg) {
        var msg = addErrorMsg.innerText;
        if (msg) toastr.error(msg);
      }
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
        if (window.$) {
          $('#editPublicModal').modal('show');
        } else if (typeof bootstrap !== 'undefined') {
          var modal = new bootstrap.Modal(document.getElementById('editPublicModal'));
          modal.show();
        }
      });
    });
  });
})();