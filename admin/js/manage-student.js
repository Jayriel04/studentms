function toggleOtherGenderInput() {
    var genderSelect = document.getElementById("gender");
    var otherGenderInput = document.getElementById("otherGenderInput");
    if (genderSelect.value === "Other") {
        otherGenderInput.style.display = "block";
    } else {
        otherGenderInput.style.display = "none";
    }
}

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

var citiesData = {};
fetch('../data/cities.json')
    .then(response => response.json())
    .then(data => {
        citiesData = data;
        // Check if we are on the edit page by looking for a specific data attribute
        const container = document.querySelector('.container-scroller');
        const currentCity = container ? container.dataset.currentCity : '';
        const currentMajor = container ? container.dataset.currentMajor : '';
        updateCities(currentCity || '');
    })
    .catch(error => console.error('Error loading cities:', error));

function updateCities(selectedCity) {
    var province = $('.province-select').val();
    var container = $('#city-municipality-container');
    container.empty();

    if (citiesData[province]) {
        var select = $('<select name="citymunicipality" id="citymunicipality-select" class="form-control" style="text-transform: capitalize;"></select>');
        select.append('<option value="">Select City/Municipality</option>');
        citiesData[province].forEach(function (city) {
            var option = $('<option></option>').val(city).text(city);
            if (city === selectedCity) {
                option.prop('selected', true);
            }
            select.append(option);
        });
        container.append(select);
        $('#citymunicipality-select').select2();
    } else {
        var input = $('<input type="text" name="citymunicipality" id="citymunicipality-text" class="form-control" style="text-transform: capitalize;">');
        if (selectedCity) {
            input.val(selectedCity);
        }
        container.append(input);
    }
}
// Initialize Select2 for province dropdown
if (window.jQuery) {
    jQuery('.province-select').select2();
    jQuery('.province-select').on('change', function () {
        updateCities('');
    });
    // This will run for both add and edit pages.
    jQuery(document).ready(function () {
        toggleOtherGenderInput();
        const container = document.querySelector('.container-scroller');
        const currentMajor = container ? container.dataset.currentMajor : '';
        updateMajors(currentMajor || '');
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const multiStepForm = document.querySelector('#addStudentForm');
    if (!multiStepForm) return;

    const steps = Array.from(multiStepForm.querySelectorAll('.form-step'));
    const progressBarSteps = Array.from(multiStepForm.querySelectorAll('.add-student-progress-bar .step'));
    const nextBtn = multiStepForm.querySelector('.btn-next');
    const prevBtn = multiStepForm.querySelector('.btn-prev');
    const submitBtn = multiStepForm.querySelector('.add-student-btn-submit');

    let currentStep = 1;

    const updateFormSteps = () => {
        steps.forEach(step => {
            step.classList.toggle('active', parseInt(step.dataset.step) === currentStep);
        });
    };

    const updateProgressBar = () => {
        progressBarSteps.forEach((step, index) => {
            if (index < currentStep) {
                step.classList.add('active');
            } else {
                step.classList.remove('active');
            }
        });
    };

    const updateButtons = () => {
        prevBtn.style.display = currentStep > 1 ? 'inline-block' : 'none';
        nextBtn.style.display = currentStep < steps.length ? 'inline-block' : 'none';
        submitBtn.style.display = currentStep === steps.length ? 'inline-block' : 'none';
    };

    nextBtn.addEventListener('click', () => {
        if (currentStep < steps.length) {
            currentStep++;
            updateFormSteps();
            updateProgressBar();
            updateButtons();
        }
    });

    prevBtn.addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep--;
            updateFormSteps();
            updateProgressBar();
            updateButtons();
        }
    });

});