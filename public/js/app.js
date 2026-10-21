/* ===== Site JavaScript ===== */
(function () {
    // Theme toggle (dark / light)
    const themeBtn = document.getElementById('themeBtn');
    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const root = document.documentElement;
            const next = root.dataset.bsTheme === 'dark' ? 'light' : 'dark';
            root.dataset.bsTheme = next;
            localStorage.setItem('theme', next);
        });
    }

    // Navbar shadow when scrolled
    const topbar = document.getElementById('topbar');
    const onScroll = () => topbar && topbar.classList.toggle('scrolled', window.scrollY > 10);
    window.addEventListener('scroll', onScroll);
    onScroll();

    // "/" focuses the search box
    document.addEventListener('keydown', (e) => {
        const tag = document.activeElement.tagName;
        const search = document.getElementById('searchInput');
        if (e.key === '/' && tag !== 'INPUT' && tag !== 'TEXTAREA' && search) {
            e.preventDefault();
            search.focus();
        }
    });

    // Scroll progress bar + back-to-top ring
    const bar = document.getElementById('scrollBar');
    const toTop = document.getElementById('toTop');
    if (bar && toTop) {
        const ring = toTop.querySelector('.bar'), RING = 138.2;
        const onProgress = () => {
            const h = document.documentElement.scrollHeight - innerHeight;
            const p = h > 0 ? Math.min(scrollY / h, 1) : 0;
            bar.style.width = (p * 100) + '%';
            ring.style.strokeDashoffset = RING * (1 - p);
            toTop.classList.toggle('show', scrollY > 500);
        };
        addEventListener('scroll', onProgress, { passive: true });
        onProgress();
        toTop.addEventListener('click', () => scrollTo({ top: 0, behavior: 'smooth' }));
    }

    // Reveal on scroll
    const io = new IntersectionObserver((entries) => {
        entries.forEach(en => { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } });
    }, { threshold: .12, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.reveal').forEach((el, i) => {
        if (!el.style.getPropertyValue('--d')) el.style.setProperty('--d', ((i % 4) * 80) + 'ms');
        io.observe(el);
    });

    // Spotlight follows cursor
    document.addEventListener('mousemove', (e) => {
        const el = e.target.closest && e.target.closest('.spot');
        if (!el) return;
        const r = el.getBoundingClientRect();
        el.style.setProperty('--mx', (e.clientX - r.left) + 'px');
        el.style.setProperty('--my', (e.clientY - r.top) + 'px');
    });
})();

/* ===== Custom confirm popup ===== */
(function () {
    const box = document.getElementById('cfm');
    if (!box) return;

    const titleEl = document.getElementById('cfmTitle'), textEl = document.getElementById('cfmText');
    const okBtn = document.getElementById('cfmOk'), cancelBtn = document.getElementById('cfmCancel'), icon = document.getElementById('cfmIcon');
    let resolver = null, lastFocus = null;

    function close(result) {
        box.classList.remove('open');
        box.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (lastFocus && lastFocus.focus) lastFocus.focus();
        if (resolver) { const r = resolver; resolver = null; r(result); }
    }

    // Opens the popup. Returns a Promise<boolean>.
    window.confirmDialog = function (opts) {
        opts = opts || {};
        const msg = opts.message || 'Are you sure?';
        const danger = opts.type ? opts.type === 'danger' : /delete|remove|discard|clear/i.test(msg);

        // "Delete this post? This cannot be undone."  ->  title + text
        let title = msg, text = opts.text || '';
        const m = msg.match(/^(.*?[?!.])\s+(.+)$/);
        if (m && !opts.text) { title = m[1]; text = m[2]; }
        if (!text && danger) text = 'This action cannot be undone.';

        titleEl.textContent = title;
        textEl.textContent = text;
        okBtn.textContent = opts.ok || (/delete/i.test(msg) ? 'Delete' : /remove/i.test(msg) ? 'Remove' : 'Confirm');
        cancelBtn.textContent = opts.cancel || 'Cancel';
        icon.className = 'bi ' + (opts.icon || (danger ? 'bi-trash3' : 'bi-question-circle'));
        box.classList.toggle('primary', !danger);

        lastFocus = document.activeElement;
        box.classList.add('open');
        box.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        setTimeout(() => (danger ? cancelBtn : okBtn).focus(), 60);

        return new Promise(res => { resolver = res; });
    };

    okBtn.addEventListener('click', () => close(true));
    cancelBtn.addEventListener('click', () => close(false));
    box.addEventListener('mousedown', e => { if (e.target === box) close(false); });
    document.addEventListener('keydown', e => {
        if (!box.classList.contains('open')) return;
        if (e.key === 'Escape') { e.preventDefault(); close(false); }
        if (e.key === 'Tab') {                       // keep focus inside the popup
            e.preventDefault();
            (document.activeElement === okBtn ? cancelBtn : okBtn).focus();
        }
    });

    // Turn old  onsubmit="return confirm('...')"  /  onclick="return confirm('...')"  into data-confirm
    document.querySelectorAll('[onsubmit*="confirm("], [onclick*="confirm("]').forEach(el => {
        ['onsubmit', 'onclick'].forEach(attr => {
            const v = el.getAttribute(attr);
            const m = v && v.match(/confirm\(\s*(['"])([\s\S]*?)\1\s*\)/);
            if (m) { el.dataset.confirm = m[2]; el.removeAttribute(attr); }
        });
    });

    // Forms:  <form data-confirm="Delete this post?">
    document.addEventListener('submit', e => {
        const f = e.target;
        if (!f.dataset || !f.dataset.confirm || f.dataset.confirmed) return;
        e.preventDefault();
        confirmDialog({ message: f.dataset.confirm, text: f.dataset.confirmText, ok: f.dataset.confirmOk, type: f.dataset.confirmType })
            .then(ok => {
                if (!ok) return;
                f.dataset.confirmed = '1';
                (e.submitter && f.requestSubmit) ? f.requestSubmit(e.submitter) : f.submit();
            });
    }, true);

    // Buttons / links:  <button data-confirm="Remove your photo?">
    document.addEventListener('click', e => {
        const el = e.target.closest('[data-confirm]:not(form)');
        if (!el) return;
        if (el.dataset.confirmed) { delete el.dataset.confirmed; return; }
        e.preventDefault();
        e.stopPropagation();
        confirmDialog({ message: el.dataset.confirm, text: el.dataset.confirmText, ok: el.dataset.confirmOk, type: el.dataset.confirmType })
            .then(ok => { if (ok) { el.dataset.confirmed = '1'; el.click(); } });
    }, true);
})();