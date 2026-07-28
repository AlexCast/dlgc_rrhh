document.addEventListener('DOMContentLoaded', () => {
    // ==========================================
    // 1. Animaciones al hacer Scroll (Intersection Observer)
    // ==========================================
    // Esto hace que la página no se vea estática, los elementos aparecen suavemente al bajar.
    const reveals = document.querySelectorAll('.reveal');

    const revealOnScroll = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                // Opcional: Descomentar la siguiente línea si quieres que la animación solo ocurra una vez
                // observer.unobserve(entry.target); 
            }
        });
    }, {
        root: null,
        threshold: 0.1, // El elemento aparece cuando el 10% es visible
        rootMargin: "0px 0px -50px 0px"
    });

    reveals.forEach(reveal => {
        revealOnScroll.observe(reveal);
    });
});