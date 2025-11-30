(function () {
  document.addEventListener('DOMContentLoaded', function () {
    // Wire message modal buttons to populate fields
    var messageButtons = document.querySelectorAll('.message-btn');
    messageButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        document.getElementById('studentEmail').value = this.getAttribute('data-email');
        document.getElementById('studentName').innerText = this.getAttribute('data-name');
        document.getElementById('studentStuID').value = this.getAttribute('data-stuid');
      });
    });

    // Initialize mention functionality on the notice message textarea
    var notemsgTextarea = document.getElementById('notmsg');
    if (notemsgTextarea && typeof initializeMention === 'function') {
      initializeMention(notemsgTextarea, 'search.php?mention_suggest=1');
    }

    // Display toastr notifications from server
    if (typeof toastr !== 'undefined') {
      var flashMsg = document.querySelector('[data-flash-message]');
      var flashError = document.querySelector('[data-flash-error]');
      
      if (flashMsg && flashMsg.getAttribute('data-flash-message')) {
        toastr.success(flashMsg.getAttribute('data-flash-message'));
      }
      if (flashError && flashError.getAttribute('data-flash-error')) {
        toastr.error(flashError.getAttribute('data-flash-error'));
      }
    }
  });
})();