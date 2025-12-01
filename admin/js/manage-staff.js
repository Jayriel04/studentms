// Moved from the <script> block at the bottom of manage-staff.php

(function () {
  var data = window.msData || {};

  // Toastr messages
  if (typeof toastr !== 'undefined') {
    if (data.statusMessage) toastr.success(data.statusMessage);
    if (data.add_success_message) toastr.success(data.add_success_message);
    if (data.add_error_message) toastr.error(data.add_error_message);
    if (data.edit_success_message) toastr.success(data.edit_success_message);
    if (data.edit_error_message) toastr.error(data.edit_error_message);
  }

  // Helpers to show bootstrap/jQuery modal
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

  // If there was an error adding staff, open the add modal
  if (data.openAddModal) {
    window.addEventListener('DOMContentLoaded', function () {
      openModal('addStaffModalOverlay');
    });
  }

  // If edit failed validation, reopen edit modal and populate with posted values
  if (data.openEditModal) {
    window.addEventListener('DOMContentLoaded', function () {
      var modalEl = document.getElementById('editStaffModal');
      if (modalEl && typeof bootstrap !== "undefined") {
        try {
          document.getElementById('edit_id').value = data.editPost.id || '';
          document.getElementById('edit_name').value = data.editPost.name || '';
          document.getElementById('edit_username').value = data.editPost.username || ''; // This ID is correct
          document.getElementById('edit_email').value = data.editPost.email || '';
          document.getElementById('edit_regdate').value = data.editPost.regdate || '';
        } catch (e) { /* ignore missing fields */ }
        $('#editStaffModal').modal('show'); // Keep bootstrap for edit modal
      } else {
        // attempt jQuery fallback
        if (window.$) {
          try {
            $('#edit_id').val(data.editPost.id || '');
            $('#edit_name').val(data.editPost.name || '');
            $('#edit_username').val(data.editPost.username || '');
            $('#edit_email').val(data.editPost.email || ''); // This ID is correct
            $('#edit_regdate').val(data.editPost.regdate || '');
            $('#editStaffModal').modal('show');
          } catch (e) { /* ignore */ }
        }
      }
    });
  }

  // Toggle password visibility for add modal
  (function () {
    var toggle = document.querySelector('#toggleAddPassword');
    var pwd = document.getElementById('add_password');
    if (!toggle || !pwd) return;
    toggle.addEventListener('click', function () {
      if (pwd.type === 'password') {
        pwd.type = 'text';
        toggle.classList.add('active');
      } else {
        pwd.type = 'password';
        toggle.classList.remove('active');
      }
    });
  })();

  // Toggle password visibility for edit modal
  (function () {
    var toggle = document.querySelector('#toggleEditPassword');
    var pwd = document.getElementById('edit_password');
    if (!toggle || !pwd) return;
    toggle.addEventListener('click', function () {
      if (pwd.type === 'password') {
        pwd.type = 'text';
        toggle.classList.add('active');
      } else {
        pwd.type = 'password';
        toggle.classList.remove('active');
      }
    });
  })();

  // Populate edit modal when edit button clicked
  document.addEventListener('DOMContentLoaded', function () {
    var editButtons = document.querySelectorAll('.btn-edit');
    editButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = this.getAttribute('data-id');
        var name = this.getAttribute('data-name');
        var username = this.getAttribute('data-username');
        var email = this.getAttribute('data-email');
        var regdate = this.getAttribute('data-regdate');

        try {
          document.getElementById('edit_id').value = id;
          document.getElementById('edit_name').value = name;
          document.getElementById('edit_username').value = username;
          document.getElementById('edit_email').value = email;
          document.getElementById('edit_regdate').value = regdate;
          document.getElementById('edit_password').value = '';
        } catch (e) { /* ignore */ }        openModal('editStaffModalOverlay');
      });
    });
  });

  // Event listeners for the new add modal
  document.addEventListener('DOMContentLoaded', function () {
    var addModalOverlay = document.getElementById('addStaffModalOverlay');
    var openAddBtn = document.querySelector('[data-target="#addStaffModal"]');
    var closeBtns = document.querySelectorAll('.new-close-btn, .new-btn-cancel');

    if (openAddBtn) {
      openAddBtn.addEventListener('click', function(e) {
        e.preventDefault();
        openModal('addStaffModalOverlay');
      });
    }

    closeBtns.forEach(function(btn) {
      btn.addEventListener('click', function() {
        closeModal('addStaffModalOverlay');
      });
    });
  });
  // Event listeners for the new edit modal
  document.addEventListener('DOMContentLoaded', function () {
    var closeBtns = document.querySelectorAll('#editStaffModalOverlay .new-close-btn, #editStaffModalOverlay .new-btn-cancel');
    closeBtns.forEach(function(btn) {
      btn.addEventListener('click', function() {
        closeModal('editStaffModalOverlay');
      });
    });
  });
})();