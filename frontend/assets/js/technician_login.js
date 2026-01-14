function loginTechnician() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
}
const openTechnicianModalBtn = document.getElementById('open-technician-modal');
const closeTechnicianModalBtn = document.getElementById('close-technician-modal');
const technicianModal = document.getElementById('technician-login-modal');

// Open the modal when the open button is clicked
openTechnicianModalBtn.onclick = function () {
    technicianModal.style.display = "block";
}

// Close the modal when the close button is clicked
closeTechnicianModalBtn.onclick = function () {
    technicianModal.style.display = "none";
}

// Close the modal when clicking outside of it
window.addEventListener('click', function (e) {
    if (e.target == technicianModal) {
        technicianModal.style.display = "none";
    }
});