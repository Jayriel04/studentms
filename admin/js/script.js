// Function to toggle the "Please Specify" input field for gender
function toggleOtherGenderInput() {
  const genderSelect = document.getElementById("gender");
  const otherGenderInput = document.getElementById("otherGenderInput");

  if (genderSelect.value === "Other") {
    otherGenderInput.style.display = "block";
  } else {
    otherGenderInput.style.display = "none";
    document.getElementById("otherGender").value = ""; // Clear the input field
  }
}