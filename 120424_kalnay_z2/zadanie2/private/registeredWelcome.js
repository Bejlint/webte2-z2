document.addEventListener("DOMContentLoaded", function () {
    const user = localStorage.getItem("loggedInUser");
    if (user) {
        document.getElementById("welcomeMessage").textContent = "Welcome " + user;
    }

    const logoutButton = document.getElementById("logoutButton");
    if (logoutButton) {
        logoutButton.addEventListener("click", function () {
            window.location.href = "logout.php";
        });
    }
});
