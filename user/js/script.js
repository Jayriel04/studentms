function toggleOtherInput() {
  const select = document.getElementById("genderSelect");
  const otherInputGroup = document.getElementById("otherInputGroup");

  // Show or hide the input field based on the selected value
  if (select.value === "Other") {
    otherInputGroup.style.display = "block"; // Show input
  } else {
    otherInputGroup.style.display = "none"; // Hide input
  }
}
