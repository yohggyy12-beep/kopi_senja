import './bootstrap';

// ==================================================
// SIDEBAR MOBILE
// ==================================================

document.addEventListener('DOMContentLoaded', function () {

    const sidebar = document.getElementById('appSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    const toggleButton = document.querySelector(
        '[data-sidebar-toggle]'
    );

    if (!sidebar || !overlay || !toggleButton) {
        return;
    }

    function bukaSidebar() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');

        toggleButton.setAttribute('aria-expanded', 'true');
    }

    function tutupSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');

        toggleButton.setAttribute('aria-expanded', 'false');
    }

    toggleButton.addEventListener('click', function () {

        const sedangTerbuka =
            !sidebar.classList.contains('-translate-x-full');

        sedangTerbuka ? tutupSidebar() : bukaSidebar();

    });

    overlay.addEventListener('click', tutupSidebar);

});
