(function () {
    var toggleBtn = document.getElementById("toggle-password");
    var passwordInput = document.getElementById("password");
    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener("click", function () {
            var isHidden = passwordInput.type === "password";
            passwordInput.type = isHidden ? "text" : "password";
            toggleBtn.querySelector(".material-symbols-outlined").textContent =
                isHidden ? "visibility_off" : "visibility";
        });
    }
})();
