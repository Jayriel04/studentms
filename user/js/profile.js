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
    if (!passwordInput || !toggleIcon) return;
    
    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      toggleIcon.classList.add('active');
    } else {
      passwordInput.type = 'password';
      toggleIcon.classList.remove('active');
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
    
    if (genderSelect) {
      if (genderSelect.value === 'Other') {
        otherGenderInput.style.display = 'block';
      }
      genderSelect.addEventListener('change', function () {
        if (this.value === 'Other') {
          otherGenderInput.style.display = 'block';
        } else {
          otherGenderInput.style.display = 'none';
        }
      });
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
})(jQuery);