//para gumana yung submit request button sa index.php
document.getElementById("requestForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch("../../backend/requests/send_request.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        alert("Request submitted successfully with code: " + data.request_code);
        // Clear the form
        document.getElementById("requestForm").reset();
        // Redirect to requested page
        setTimeout(() => {
          window.location.href = "../../frontend/pages/requested.php";
        }, 1000);
      } else {
        alert("Failed to submit request: " + (data.message || "Unknown error"));
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Server error");
    });
});
