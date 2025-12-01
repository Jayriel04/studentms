(function ($) {
  'use strict';

  function updateMajors(currentMajor) {
    const programSelect = document.getElementById('program');
    const majorSelect = document.getElementById('major');
    const selectedProgram = programSelect.value;

    // Clear existing options
    majorSelect.innerHTML = '<option value="">Select Major</option>';

    const majors = {
      "Bachelor of Elementary Education (BEEd)": [
        "Major in General Content"
      ],
      "Bachelor of Secondary Education (BSEd)": [
        "Major in English",
        "Major in Filipino",
        "Major in Mathematics"
      ],
      "Bachelor of Science in Business Administration (BSBA)": [
        "Major in Human Resource Management",
        "Major in Marketing Management"
      ],
      "Bachelor of Industrial Technology (BindTech)": [
        "Major in Computer Technology",
        "Major in Electronics Technology"
      ],
      "Bachelor of Science in Information Technology (BSIT)": [
        "Major in information technology"
      ]
    };

    if (majors[selectedProgram]) {
      majors[selectedProgram].forEach(function (major) {
        const option = document.createElement('option');
        option.value = major;
        option.textContent = major;
        if (major === currentMajor) {
          option.selected = true;
        }
        majorSelect.appendChild(option);
      });
    }
  }

  function updateCities(selectedCity) {
    var province = $('.province-select').val();
    var container = $('#city-municipality-container');
    container.empty();

    if (window.citiesData && window.citiesData[province]) {
      var select = $('<select name="citymunicipality" id="citymunicipality-select" class="form-control" style="text-transform: capitalize;"></select>');
      select.append('<option value="">Select City/Municipality</option>');
      window.citiesData[province].forEach(function (city) {
        var option = $('<option></option>').val(city).text(city);
        if (city === selectedCity) {
          option.prop('selected', true);
        }
        select.append(option);
      });
      container.append(select);
      $('#citymunicipality-select').select2();
    } else {
      var input = $('<input type="text" name="citymunicipality" id="citymunicipality-text" class="form-control" style="text-transform: capitalize;">').val(selectedCity);
      container.append(input);
    }
  }

  // Password visibility toggle
  function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePassword');
    if (!passwordInput || !toggleIcon) return; // Exit if elements don't exist

    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      toggleIcon.classList.remove('icon-eye');
      toggleIcon.classList.add('icon-eye-slash'); // Assuming you have an eye-slash icon
    } else {
      passwordInput.type = 'password';
      toggleIcon.classList.remove('icon-eye-slash');
      toggleIcon.classList.add('icon-eye');
    }
  }

  // Initialize on page load
  window.addEventListener('DOMContentLoaded', function () {
    // Expose functions to global scope
    window.updateMajors = updateMajors;
    window.updateCities = updateCities;
    window.togglePasswordVisibility = togglePasswordVisibility;

    // Gender toggle
    var genderSelect = document.getElementById('gender');
    var otherGenderInput = document.getElementById('otherGenderInput');

    function handleGenderChange() {
      if (genderSelect.value === 'Other') {
        otherGenderInput.style.display = 'block';
      } else {
        otherGenderInput.style.display = 'none';
      }
    }

    if (genderSelect) {
      handleGenderChange(); // Set initial state
      genderSelect.addEventListener('change', handleGenderChange);
    }

    // Set initial major
    var currentMajor = document.body.getAttribute('data-current-major') || '';
    updateMajors(currentMajor);

    // Load cities data
    var currentCity = document.body.getAttribute('data-current-city') || '';
    fetch('../data/cities.json')
      .then(response => response.json())
      .then(data => {
        window.citiesData = data;
        updateCities(currentCity);
      })
      .catch(error => console.error('Error loading cities:', error));

    // Initialize Select2 for province dropdown
    if (window.jQuery) {
      $('.province-select').select2();
      $('.province-select').on('change', function () { updateCities(''); });
    }

    // Password visibility toggle
    var togglePasswordBtn = document.getElementById('togglePassword');
    if (togglePasswordBtn) {
      togglePasswordBtn.addEventListener('click', togglePasswordVisibility);
    }
  });

  // Tabbed form logic for update-profile.php
  document.addEventListener('DOMContentLoaded', function () {
    const tabs = Array.from(document.querySelectorAll('.form-tab'));
    const contents = Array.from(document.querySelectorAll('.form-tab-content'));
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const submitBtn = document.querySelector('button[name="update"]');
    let idx = 0;

    function activate(i) {
      if (!tabs.length || !contents.length || !prevBtn || !nextBtn || !submitBtn) return;
      idx = i;
      tabs.forEach((t, ti) => t.classList.toggle('active', ti === i));
      contents.forEach((c, ci) => c.classList.toggle('active', ci === i));
      prevBtn.style.display = i === 0 ? 'none' : 'inline-block';
      nextBtn.style.display = i === tabs.length - 1 ? 'none' : 'inline-block';
      submitBtn.style.display = i === tabs.length - 1 ? 'inline-block' : 'none';
    }

    tabs.forEach((tab, i) => tab.addEventListener('click', () => activate(i)));

    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        if (idx < tabs.length - 1) activate(idx + 1);
      });
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        if (idx > 0) activate(idx - 1);
      });
    }
    activate(0); // Initialize the first tab
  });
})(jQuery);