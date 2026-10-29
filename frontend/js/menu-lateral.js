(() => {
    const toggle = document.querySelector('.menu-lateral-toggle');
    const menu = document.querySelector('.menu-lateral');
    const backdrop = document.querySelector('.menu-lateral-fundo');
    const closeButton = document.querySelector('.menu-lateral-fechar');

    if (!toggle || !menu || !backdrop || !closeButton) {
        return;
    }

    const setOpen = (open) => {
        menu.hidden = !open;
        backdrop.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
        document.body.classList.toggle('menu-lateral-aberto', open);

        if (open) {
            closeButton.focus();
        } else {
            toggle.focus();
        }
    };

    toggle.addEventListener('click', () => {
        setOpen(menu.hidden);
    });
    closeButton.addEventListener('click', () => setOpen(false));
    backdrop.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !menu.hidden) {
            setOpen(false);
        }
    });
})();
