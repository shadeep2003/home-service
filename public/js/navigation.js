// Keep desktop links visible; retain native, keyboard-accessible details on mobile.
const menu = document.querySelector('.nav-menu');
if (menu) {
    const desktop = window.matchMedia('(min-width: 981px)');
    const syncMenu = () => { menu.open = desktop.matches; };
    syncMenu();
    desktop.addEventListener('change', syncMenu);
    menu.addEventListener('toggle', () => {
        if (desktop.matches && !menu.open) menu.open = true;
    });
}
