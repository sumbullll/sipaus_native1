// public/js/admin/admin_common.js (Versi Final untuk SEMUA role)

// public/js/admin/admin_common.js (Versi Final untuk SEMUA role)

document.addEventListener("DOMContentLoaded", () => {
    
    // --- Bagian Logika Sidebar ---
    const toggleBtn = document.getElementById("toggle-btn");
    const sidebar = document.getElementById("sidebar");
    const mainContent = document.getElementById("main-content");

    // Hanya jalankan logika sidebar jika elemen intinya ada di halaman
    if (toggleBtn && sidebar && mainContent) {
        // Fungsi untuk menerapkan state collapsed/expanded
        const setSidebarState = (isCollapsed) => {
            if (isCollapsed) {
                sidebar.classList.add("collapsed");
                mainContent.classList.add("expanded");
            } else {
                sidebar.classList.remove("collapsed");
                mainContent.classList.remove("expanded");
            }
        };

        // Cek state dari localStorage saat halaman dimuat
        const isCollapsedFromStorage = localStorage.getItem("sidebarCollapsed") === "true";
        setSidebarState(isCollapsedFromStorage);

        // Tambahkan event listener untuk tombol toggle
        toggleBtn.addEventListener("click", () => {
            const isNowCollapsed = sidebar.classList.toggle("collapsed");
            mainContent.classList.toggle("expanded");
            localStorage.setItem("sidebarCollapsed", isNowCollapsed);
        });
    }

    // --- Bagian Logika Logout Popup ---
    const logoutLink = document.getElementById('logout-btn-link');
    const logoutPopup = document.getElementById('logout-popup');
    const confirmLogoutBtn = document.getElementById('confirm-logout-btn');
    const cancelLogoutBtn = document.getElementById('cancel-logout');
    const logoutForm = document.getElementById('logout-form');

    // Hanya jalankan logika logout jika semua elemennya ada
    if (logoutLink && logoutPopup && confirmLogoutBtn && cancelLogoutBtn && logoutForm) {
        logoutLink.addEventListener('click', (e) => {
            e.preventDefault();
            logoutPopup.style.display = 'flex';
        });
        cancelLogoutBtn.addEventListener('click', () => {
            logoutPopup.style.display = 'none';
        });
        confirmLogoutBtn.addEventListener('click', () => {
            logoutForm.submit();
        });
        logoutPopup.addEventListener('click', (e) => {
            if (e.target === logoutPopup) {
                logoutPopup.style.display = 'none';
            }
        });
    }
});