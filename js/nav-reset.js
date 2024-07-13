document.addEventListener('DOMContentLoaded', function() {
    function resetNavLinks() {
        var navLinks = document.querySelectorAll('.nav-reset');
        navLinks.forEach(function(link) {
            link.classList.remove('active');
        });
    }

    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            resetNavLinks();
        }
    });

    resetNavLinks();
});