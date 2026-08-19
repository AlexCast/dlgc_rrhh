/**
 * Lógica específica para la Ficha Técnica del Empleado
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log('Módulo de Ficha Técnica cargado correctamente.');

    // Podríamos agregar interactividad adicional aquí, 
    // como formateo de moneda o fechas dinámicas.
    
    const salaryElements = document.querySelectorAll('.info-value');
    salaryElements.forEach(el => {
        if (el.textContent.includes('$')) {
            // Ejemplo de manipulación: resaltar el salario
            el.style.color = 'var(--primary-color)';
            el.style.fontWeight = '700';
        }
    });

    // Efecto de entrada suave para los items de información
    const infoItems = document.querySelectorAll('.info-item');
    infoItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(10px)';
        item.style.transition = `all 0.3s ease-out ${index * 0.05}s`;
        
        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, 100);
    });

    const mobileQuery = window.matchMedia('(max-width: 768px)');
    const cards = document.querySelectorAll('.data-card');

    const syncCardState = () => {
        cards.forEach(card => {
            if (mobileQuery.matches) {
                card.classList.add('is-collapsible');
                card.classList.remove('is-expanded');
                card.setAttribute('role', 'button');
                card.setAttribute('tabindex', '0');
                card.setAttribute('aria-expanded', 'false');
                card.style.setProperty('--expanded-height', `${card.scrollHeight}px`);
            } else {
                card.classList.remove('is-collapsible', 'is-expanded');
                card.removeAttribute('role');
                card.removeAttribute('tabindex');
                card.removeAttribute('aria-expanded');
                card.style.removeProperty('--expanded-height');
            }
        });
    };

    const toggleCard = card => {
        if (!mobileQuery.matches) {
            return;
        }

        card.style.setProperty('--expanded-height', `${card.scrollHeight}px`);
        const expanded = card.classList.toggle('is-expanded');
        card.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    };

    cards.forEach(card => {
        card.addEventListener('click', event => {
            if (!mobileQuery.matches) {
                return;
            }

            const interactiveElement = event.target.closest('a, button, input, select, textarea, label');
            if (interactiveElement) {
                return;
            }

            toggleCard(card);
        });

        card.addEventListener('keydown', event => {
            if (!mobileQuery.matches || (event.key !== 'Enter' && event.key !== ' ')) {
                return;
            }

            event.preventDefault();
            toggleCard(card);
        });
    });

    syncCardState();
    mobileQuery.addEventListener('change', syncCardState);
});

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('profile_picture_form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('profile_picture');
            const errorDiv = document.getElementById('upload_error');
            
            // Validar que se haya seleccionado un archivo
            if (fileInput.files.length === 0) {
                e.preventDefault();
                errorDiv.textContent = 'Por favor, selecciona una imagen primero.';
                errorDiv.style.display = 'block';
                return;
            }

            const file = fileInput.files[0];
            const maxSize = 5 * 1024 * 1024; // 5MB en bytes

            // Validar peso del archivo
            if (file.size > maxSize) {
                e.preventDefault(); // Detiene el envío del formulario
                errorDiv.textContent = 'El archivo es demasiado grande. El máximo permitido es 5MB.';
                errorDiv.style.display = 'block';
            } else {
                errorDiv.style.display = 'none'; // Todo bien, el formulario continúa
            }
        });
    }
});

