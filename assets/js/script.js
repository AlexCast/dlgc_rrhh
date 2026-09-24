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

document.addEventListener('mousemove', (e) => {
    document.documentElement.style.setProperty('--mouse-x', `${e.clientX}px`);
    document.documentElement.style.setProperty('--mouse-y', `${e.clientY}px`);
});

document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('interactive-grid');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');

    let width, height;
    let points = [];
    const gap = 32; // Distancia entre puntos (coincide con tu cuadrícula)
    const mouse = { x: -1000, y: -1000, radius: 120 }; // Radio de interacción

    // Redimensionar canvas dinámicamente
    function resize() {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
        initPoints();
    }

    // Inicializar la cuadrícula de puntos con su posición original (originX, originY)
    function initPoints() {
        points = [];
        for (let x = 0; x < width; x += gap) {
            for (let y = 0; y < height; y += gap) {
                points.push({
                    x: x,
                    y: y,
                    originX: x,
                    originY: y,
                    vx: 0,
                    vy: 0
                });
            }
        }
    }

    // Rastrear posición del cursor
    window.addEventListener('mousemove', (e) => {
        mouse.x = e.clientX;
        mouse.y = e.clientY;
    });

    window.addEventListener('mouseleave', () => {
        mouse.x = -1000;
        mouse.y = -1000;
    });

    window.addEventListener('resize', resize);

    // Bucle de animación (Render loop)
    function animate() {
        ctx.clearRect(0, 0, width, height);

        // Detectar si el tema oscuro está activo para ajustar el color de los puntos
        const isDark = document.body.classList.contains('dark-theme') || 
                       document.documentElement.classList.contains('dark-theme');
        
        const dotColor = isDark ? 'rgba(255, 255, 255, 0.15)' : 'rgba(13, 19, 15, 0.15)';
        const activeColor = 'rgba(59, 168, 106, 0.8)'; // Verde corporativo DLGC

        for (let i = 0; i < points.length; i++) {
            const p = points[i];

            // Distancia entre el punto y el mouse
            const dx = mouse.x - p.x;
            const dy = mouse.y - p.y;
            const dist = Math.sqrt(dx * dx + dy * dy);

            // Si el mouse está cerca, empujar el punto
            if (dist < mouse.radius) {
                const angle = Math.atan2(dy, dx);
                const force = (mouse.radius - dist) / mouse.radius;
                
                // Fuerza de repulsión
                const pushX = -Math.cos(angle) * force * 15;
                const pushY = -Math.sin(angle) * force * 15;

                p.vx += pushX;
                p.vy += pushY;
            }

            // Retorno suave a la posición original (Efecto elástico)
            p.vx += (p.originX - p.x) * 0.1;
            p.vy += (p.originY - p.y) * 0.1;

            // Fricción para frenar el movimiento
            p.vx *= 0.75;
            p.vy *= 0.75;

            // Actualizar posición
            p.x += p.vx;
            p.y += p.vy;

            // Dibujar punto
            ctx.beginPath();
            ctx.arc(p.x, p.y, dist < mouse.radius ? 2.5 : 1.5, 0, Math.PI * 2);
            ctx.fillStyle = dist < mouse.radius ? activeColor : dotColor;
            ctx.fill();
        }

        requestAnimationFrame(animate);
    }

    resize();
    animate();
});

document.addEventListener('DOMContentLoaded', () => {
    const navLinksContainer = document.querySelector('.nav-links');
    const links = document.querySelectorAll('.nav-link');
    const indicator = document.querySelector('.nav-indicator');
    const sections = document.querySelectorAll('section[id]');

    if (!navLinksContainer || !indicator || links.length === 0) return;

    // Función para calcular y mover la pastilla deslizante
    function updateIndicator(activeLink) {
        if (!activeLink) return;
        
        const linkRect = activeLink.getBoundingClientRect();
        const containerRect = navLinksContainer.getBoundingClientRect();

        // Calculamos posición relativa e igualamos tamaño
        const left = linkRect.left - containerRect.left;
        const width = linkRect.width;

        indicator.style.left = `${left}px`;
        indicator.style.width = `${width}px`;
    }

    // Cambiar estado activo
    function setActive(activeLink) {
        links.forEach(link => link.classList.remove('active'));
        activeLink.classList.add('active');
        updateIndicator(activeLink);
    }

    // Detectar qué sección se está viendo en pantalla (Scroll Sync)
    const observerOptions = {
        root: null,
        rootMargin: '-20% 0px -60% 0px', // Activa la sección cuando pasa por el centro
        threshold: 0
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.getAttribute('id');
                const matchingLink = document.querySelector(`.nav-link[href="#${id}"]`);
                if (matchingLink) {
                    setActive(matchingLink);
                }
            }
        });
    }, observerOptions);

    sections.forEach(section => observer.observe(section));

    // Evento Clic en los enlaces
    links.forEach(link => {
        link.addEventListener('click', (e) => {
            setActive(link);
        });
    });

    // Ajustar la posición si la ventana cambia de tamaño
    window.addEventListener('resize', () => {
        const currentActive = document.querySelector('.nav-link.active');
        if (currentActive) updateIndicator(currentActive);
    });

    // Posición inicial
    const initialActive = document.querySelector('.nav-link.active') || links[0];
    setTimeout(() => updateIndicator(initialActive), 100);
});

document.addEventListener('DOMContentLoaded', () => {
    const cursorDot = document.querySelector('.custom-cursor-dot');
    const cursorOutline = document.querySelector('.custom-cursor-outline');

    if (!cursorDot || !cursorOutline) return;

    // Escuchar el movimiento del mouse
    window.addEventListener('mousemove', (e) => {
        const posX = e.clientX;
        const posY = e.clientY;

        // El punto interno sigue el mouse de inmediato
        cursorDot.style.left = `${posX}px`;
        cursorDot.style.top = `${posY}px`;

        // El anillo exterior lo sigue suavemente con la animación CSS
        cursorOutline.style.left = `${posX}px`;
        cursorOutline.style.top = `${posY}px`;
    });

    // Detectar elementos interactivos para expandir el cursor
    const interactiveElements = document.querySelectorAll('a, button, input, select, textarea, .card, .tab-btn, .nav-link');

    interactiveElements.forEach((el) => {
        el.addEventListener('mouseenter', () => {
            document.body.classList.add('cursor-hover');
        });
        el.addEventListener('mouseleave', () => {
            document.body.classList.remove('cursor-hover');
        });
    });

    // Ocultar si el mouse sale de la ventana del navegador
    document.addEventListener('mouseleave', () => {
        cursorDot.style.opacity = '0';
        cursorOutline.style.opacity = '0';
    });

    document.addEventListener('mouseenter', () => {
        cursorDot.style.opacity = '1';
        cursorOutline.style.opacity = '1';
    });
});