// Toggle the nav user dropdown. Closes on outside click and Escape.
(function () {
    function closeAll(except) {
        document.querySelectorAll('.nav-menu[data-open]').forEach(function (menu) {
            if (menu === except) {
                return;
            }
            menu.removeAttribute('data-open');
            const trigger = menu.querySelector('[data-nav-menu-trigger]');
            if (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-nav-menu-trigger]');

        if (trigger) {
            const menu = trigger.closest('.nav-menu');
            const willOpen = !menu.hasAttribute('data-open');
            closeAll(menu);

            if (willOpen) {
                menu.setAttribute('data-open', '');
                trigger.setAttribute('aria-expanded', 'true');
            } else {
                menu.removeAttribute('data-open');
                trigger.setAttribute('aria-expanded', 'false');
            }
            return;
        }

        // Click anywhere outside an open panel closes the menu.
        if (!event.target.closest('.nav-menu-panel')) {
            closeAll(null);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeAll(null);
        }
    });
})();
