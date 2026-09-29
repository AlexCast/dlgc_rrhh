document.addEventListener('DOMContentLoaded', () => {
    // ==========================================
    // 1. Control del Menú Colapsable (Móvil)
    // ==========================================
    const openSidebarBtn = document.getElementById('open-sidebar');
    const closeSidebarBtn = document.getElementById('close-sidebar');
    const sidebar = document.getElementById('sidebar');

    if (openSidebarBtn && closeSidebarBtn && sidebar) {
        openSidebarBtn.addEventListener('click', () => {
            sidebar.classList.add('active');
            openSidebarBtn.setAttribute('aria-expanded', 'true');
        });

        closeSidebarBtn.addEventListener('click', () => {
            sidebar.classList.remove('active');
            openSidebarBtn.setAttribute('aria-expanded', 'false');
        });

        // Cerrar el menú si se hace clic fuera de él en pantallas móviles
        document.addEventListener('click', (event) => {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnOpenBtn = openSidebarBtn.contains(event.target);
            
            if (!isClickInsideSidebar && !isClickOnOpenBtn && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                openSidebarBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // ==========================================
    // 2. Carrusel de Últimos Trámites
    // ==========================================
    const track = document.getElementById('tramites-carousel-track');
    if (track) {
        const carouselCard = track.closest('.carousel-card');
        const slides = Array.from(track.querySelectorAll('.carousel-slide'));
        const dots = Array.from(carouselCard.querySelectorAll('[data-carousel-dot]'));
        const prevBtn = carouselCard.querySelector('[data-carousel-prev]');
        const nextBtn = carouselCard.querySelector('[data-carousel-next]');
        const titleEl = carouselCard.querySelector('.card-title');
        let activeIndex = 0;

        function goToSlide(index) {
            activeIndex = Math.max(0, Math.min(index, slides.length - 1));
            track.style.transform = `translateX(-${activeIndex * 100}%)`;
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === activeIndex);
                dot.setAttribute('aria-selected', i === activeIndex ? 'true' : 'false');
            });
            if (titleEl && slides[activeIndex].dataset.slideTitle) {
                titleEl.textContent = slides[activeIndex].dataset.slideTitle;
            }
        }

        if (prevBtn) prevBtn.addEventListener('click', () => goToSlide(activeIndex - 1));
        if (nextBtn) nextBtn.addEventListener('click', () => goToSlide(activeIndex + 1));
        dots.forEach((dot, i) => dot.addEventListener('click', () => goToSlide(i)));

        // Deslizar con dedo/mouse
        let startX = 0;
        let currentX = 0;
        let isDragging = false;

        function onDragStart(x) {
            isDragging = true;
            startX = x;
            currentX = x;
            track.style.transition = 'none';
        }

        function onDragMove(x) {
            if (!isDragging) return;
            currentX = x;
            const delta = currentX - startX;
            track.style.transform = `translateX(calc(-${activeIndex * 100}% + ${delta}px))`;
        }

        function onDragEnd() {
            if (!isDragging) return;
            isDragging = false;
            track.style.transition = '';
            const delta = currentX - startX;
            const threshold = 50;
            if (delta < -threshold) {
                goToSlide(activeIndex + 1);
            } else if (delta > threshold) {
                goToSlide(activeIndex - 1);
            } else {
                goToSlide(activeIndex);
            }
        }

        track.addEventListener('touchstart', (e) => onDragStart(e.touches[0].clientX), { passive: true });
        track.addEventListener('touchmove', (e) => onDragMove(e.touches[0].clientX), { passive: true });
        track.addEventListener('touchend', onDragEnd);

        track.addEventListener('mousedown', (e) => { e.preventDefault(); onDragStart(e.clientX); });
        window.addEventListener('mousemove', (e) => onDragMove(e.clientX));
        window.addEventListener('mouseup', onDragEnd);

        goToSlide(0);
    }
});