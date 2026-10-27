// ==========================================
// Control segmentado con indicador deslizante (.tab-switch)
// Requiere la estructura:
//   .tab-switch > .tab-switch-btn + .tab-switch-indicator
// ==========================================
function updateTabSwitchIndicator(switcher, animate) {
    const indicator = switcher.querySelector('.tab-switch-indicator');
    if (!indicator) return;

    const activeTab = switcher.querySelector('.tab-switch-btn.active');
    if (!activeTab) {
        indicator.style.width = '0px';
        return;
    }

    const borderLeft = parseFloat(window.getComputedStyle(switcher).borderLeftWidth) || 0;
    const switcherRect = switcher.getBoundingClientRect();
    const tabRect = activeTab.getBoundingClientRect();

    if (animate === false) indicator.style.transition = 'none';
    indicator.style.left = (tabRect.left - switcherRect.left - borderLeft) + 'px';
    indicator.style.width = tabRect.width + 'px';

    if (animate === false) {
        void indicator.offsetWidth;
        indicator.style.transition = '';
    }
}

function initializeTabSwitch(switcher, onActivate) {
    const tabs = Array.from(switcher.querySelectorAll('.tab-switch-btn'));
    if (tabs.length === 0) return;

    const sync = () => updateTabSwitchIndicator(switcher, false);

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => {
                t.classList.toggle('active', t === tab);
                t.setAttribute('aria-selected', t === tab ? 'true' : 'false');
            });
            updateTabSwitchIndicator(switcher, true);
            if (typeof onActivate === 'function') onActivate(tab);
        });
    });

    sync();
    window.addEventListener('resize', sync);
    window.addEventListener('load', sync);
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(sync);
    }
}
