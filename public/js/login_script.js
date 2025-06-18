// public/js/login_script.js

// Menangani animasi geser antara panel Login dan Ganti Sandi
const signUpButton = document.getElementById('signUp');
const signInButton = document.getElementById('signIn');
const forgotPasswordLink = document.getElementById('forgot-password-link');
const container = document.getElementById('container');

if (signUpButton) {
    signUpButton.addEventListener('click', () => {
        container.classList.add("right-panel-active");
    });
}

if (signInButton) {
    signInButton.addEventListener('click', () => {
        container.classList.remove("right-panel-active");
    });
}

// Link "Lupa Password?" juga akan membuka panel Ganti Sandi
if (forgotPasswordLink) {
    forgotPasswordLink.addEventListener('click', (e) => {
        e.preventDefault(); // Mencegah link pindah halaman
        container.classList.add("right-panel-active");
    });
}
