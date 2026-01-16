
//para kapag inaccept ng technician
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("accept-btn")) {
        const requestId = e.target.dataset.id;

        if (!confirm("Accept this request?")) return;

        fetch("../../backend/requests/accept_request.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "request_id=" + requestId
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    location.reload();
                } else {
                    alert("Unable to accept request");
                }
            })
            .catch(() => alert("Server error"));
    }
});
