document.addEventListener("DOMContentLoaded", function() {
    let errorMessage = document.getElementById("errorWrongPassword");

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has("error")) {
        errorMessage.style.display = "block";
    }
});

