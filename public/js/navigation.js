const navigation = document.querySelector('.navigation');
const header = document.querySelector('.site-header');

if (navigation) {
    navigation.classList.add('navigation-ready');

    const toggle = navigation.querySelector('[data-nav-open]');
    const closeButton = navigation.querySelector('[data-nav-close]');
    const panel = navigation.querySelector('#mobile-navigation');
    const mobile = window.matchMedia('(max-width: 980px)');

    if (panel) panel.hidden = true;

    const closeMenu = ({ restoreFocus = false } = {}) => {
        if (!toggle || !panel || panel.hidden) return;

        panel.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Open navigation menu');
        document.body.classList.remove('navigation-drawer-open');

        if (restoreFocus) toggle.focus();
    };

    const openMenu = () => {
        if (!toggle || !panel) return;

        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        toggle.setAttribute('aria-label', 'Close navigation menu');
        document.body.classList.add('navigation-drawer-open');
        closeButton?.focus();
    };

    toggle?.addEventListener('click', () => {
        if (toggle.getAttribute('aria-expanded') === 'true') closeMenu({ restoreFocus: true });
        else openMenu();
    });

    closeButton?.addEventListener('click', () => closeMenu({ restoreFocus: true }));
    panel?.querySelectorAll('a, form button').forEach((item) => {
        item.addEventListener('click', () => closeMenu());
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            if (panel && !panel.hidden) closeMenu({ restoreFocus: true });

            navigation.querySelectorAll('.account-menu[open]').forEach((menu) => {
                menu.open = false;
                menu.querySelector('summary')?.focus();
            });
        }

        if (event.key === 'Tab' && panel && !panel.hidden && mobile.matches) {
            const focusable = [...panel.querySelectorAll('a[href], button:not([disabled])')];
            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last?.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first?.focus();
            }
        }
    });

    document.addEventListener('click', (event) => {
        navigation.querySelectorAll('.account-menu[open]').forEach((menu) => {
            if (!menu.contains(event.target)) menu.open = false;
        });
    });

    mobile.addEventListener('change', (event) => {
        if (!event.matches) closeMenu();
    });
}

if (header) {
    let scheduled = false;
    const updateScrollState = () => {
        header.classList.toggle('is-scrolled', window.scrollY > 12);
        scheduled = false;
    };

    window.addEventListener('scroll', () => {
        if (!scheduled) {
            window.requestAnimationFrame(updateScrollState);
            scheduled = true;
        }
    }, { passive: true });

    updateScrollState();
}
