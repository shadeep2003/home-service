(() => {
    const sidebar = document.querySelector('[data-dashboard-sidebar]');
    if (!sidebar) return;

    const root = document.documentElement;
    const main = document.querySelector('[data-dashboard-main]');
    const collapse = document.querySelector('[data-dashboard-collapse]');
    const opener = document.querySelector('[data-dashboard-open]');
    const closer = sidebar.querySelector('[data-dashboard-close]');
    const backdrop = document.querySelector('[data-dashboard-backdrop]');
    const mobile = window.matchMedia('(max-width: 767px)');
    let drawerOpen = false;

    const updateCollapse = () => {
        const collapsed = root.dataset.sidebarCollapsed === 'true';
        collapse?.setAttribute('aria-expanded', String(!collapsed));
        collapse?.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
        collapse?.setAttribute('title', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
    };

    const closeDrawer = (restoreFocus = false) => {
        drawerOpen = false;
        document.body.classList.remove('dashboard-drawer-open');
        opener?.setAttribute('aria-expanded', 'false');
        opener?.setAttribute('aria-label', 'Open workspace navigation');
        sidebar.removeAttribute('role');
        sidebar.removeAttribute('aria-modal');
        sidebar.inert = mobile.matches;
        if (mobile.matches) sidebar.setAttribute('aria-hidden', 'true');
        else sidebar.removeAttribute('aria-hidden');
        if (main) main.inert = false;
        if (backdrop) backdrop.hidden = true;
        if (restoreFocus) opener?.focus();
    };

    const openDrawer = () => {
        if (!mobile.matches) return;
        drawerOpen = true;
        sidebar.inert = false;
        sidebar.removeAttribute('aria-hidden');
        sidebar.setAttribute('role', 'dialog');
        sidebar.setAttribute('aria-modal', 'true');
        if (main) main.inert = true;
        if (backdrop) backdrop.hidden = false;
        document.body.classList.add('dashboard-drawer-open');
        opener?.setAttribute('aria-expanded', 'true');
        opener?.setAttribute('aria-label', 'Close workspace navigation');
        closer?.focus();
    };

    collapse?.addEventListener('click', () => {
        if (mobile.matches) return;
        root.dataset.sidebarCollapsed = root.dataset.sidebarCollapsed === 'true' ? 'false' : 'true';
        updateCollapse();
        try { localStorage.setItem('homeservices.sidebar.collapsed', root.dataset.sidebarCollapsed); } catch (error) {}
    });
    opener?.addEventListener('click', () => drawerOpen ? closeDrawer(true) : openDrawer());
    closer?.addEventListener('click', () => closeDrawer(true));
    backdrop?.addEventListener('click', () => closeDrawer(true));
    sidebar.querySelectorAll('a[href]').forEach(link => link.addEventListener('click', () => {
        if (drawerOpen) closeDrawer(true);
    }));

    document.addEventListener('keydown', event => {
        if (!drawerOpen) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            closeDrawer(true);
        } else if (event.key === 'Tab') {
            const focusable = [...sidebar.querySelectorAll('a[href], button:not([disabled])')]
                .filter(element => element.getClientRects().length > 0);
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault(); last?.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault(); first?.focus();
            }
        }
    });

    document.addEventListener('focusin', event => {
        if (drawerOpen && !sidebar.contains(event.target)) closer?.focus();
    });
    mobile.addEventListener('change', () => {
        const focusWasInside = sidebar.contains(document.activeElement);
        closeDrawer();
        if (focusWasInside) {
            if (mobile.matches) opener?.focus();
            else if (document.activeElement === closer) collapse?.focus();
        }
    });

    // The admin's existing account table is an in-page destination, not another backend page.
    const updateAccountSection = () => {
        const links = [...sidebar.querySelectorAll('[data-dashboard-section]')];
        if (!links.length) return;
        const providersActive = sidebar.querySelector('a[aria-current="page"][href*="role=provider"]');
        links.forEach(link => {
            const active = !providersActive && window.location.hash === '#accounts'
                ? link.dataset.dashboardSection === 'accounts'
                : link.dataset.routeActive === 'true';
            if (active) link.setAttribute('aria-current', 'page');
            else link.removeAttribute('aria-current');
        });
    };
    window.addEventListener('hashchange', updateAccountSection);

    root.classList.add('dashboard-nav-ready');
    updateCollapse();
    closeDrawer();
    updateAccountSection();
    // Initial stored widths render without a transition; later interactions animate.
    window.requestAnimationFrame(() => window.requestAnimationFrame(() => root.classList.add('dashboard-nav-animated')));
})();
