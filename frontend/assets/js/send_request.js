const openRequestModalBtn = document.getElementById('open-request-modal');
const closeRequestModalBtn = document.getElementById('close-request-modal');
const requestModal = document.getElementById('request-modal');

// Open the modal when the open button is clicked
openRequestModalBtn.onclick = function () {
    requestModal.style.display = "block";
}

// Close the modal when the close button is clicked
closeRequestModalBtn.onclick = function () {
    requestModal.style.display = "none";
}

// Close the modal when clicking outside of it
window.addEventListener('click', function (e) {
    if (e.target == requestModal) {
        requestModal.style.display = "none";
    }
});