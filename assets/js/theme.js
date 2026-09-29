document.addEventListener('DOMContentLoaded', () => {
    injectGlobalScrollbarStyle();

    const themeToggleBtn = document.getElementById('theme-toggle');
    const body = document.body;
    const root = document.documentElement;
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        body.classList.add('dark-theme');
        root.classList.add('dark-theme');
        if (themeToggleBtn) {
            updateToggleIcon(true);
        }
    }

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            body.classList.toggle('dark-theme');
            root.classList.toggle('dark-theme', body.classList.contains('dark-theme'));
            const isDark = body.classList.contains('dark-theme');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateToggleIcon(isDark);
        });
    }

    function injectGlobalScrollbarStyle() {
        if (document.getElementById('global-scrollbar-style')) return;

        const style = document.createElement('style');
        style.id = 'global-scrollbar-style';
        style.textContent = `
            html,
            body {
                scrollbar-color: #3ba86a transparent;
                scrollbar-width: thin;
            }

            html::-webkit-scrollbar,
            body::-webkit-scrollbar,
            .sidebar::-webkit-scrollbar {
                width: 10px;
                height: 10px;
            }

            html::-webkit-scrollbar-button,
            body::-webkit-scrollbar-button,
            .sidebar::-webkit-scrollbar-button {
                display: none;
                width: 0;
                height: 0;
            }

            html::-webkit-scrollbar-track,
            body::-webkit-scrollbar-track,
            .sidebar::-webkit-scrollbar-track {
                background: transparent;
            }

            html::-webkit-scrollbar-thumb,
            body::-webkit-scrollbar-thumb,
            .sidebar::-webkit-scrollbar-thumb {
                background-color: #3ba86a;
                border-radius: 999px;
                border: 2px solid transparent;
                background-clip: content-box;
            }

            html::-webkit-scrollbar-thumb:hover,
            body::-webkit-scrollbar-thumb:hover,
            .sidebar::-webkit-scrollbar-thumb:hover {
                background-color: #2f8f57;
            }
        `;
        document.head.appendChild(style);
    }

    function updateToggleIcon(isDark) {
        if (!themeToggleBtn) return;

        if (isDark) {
            themeToggleBtn.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></svg>`;
            themeToggleBtn.setAttribute('aria-label', 'Activar modo claro');
        } else {
            themeToggleBtn.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>`;
            themeToggleBtn.setAttribute('aria-label', 'Activar modo oscuro');
        }
    }
});
