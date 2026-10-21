
<div id="page-loader" role="status" aria-live="polite" aria-label="Loading">
    <div class="pl-box">
        <div class="pl-logo">
            <span class="pl-ring"></span>
            <span class="pl-ring pl-ring-2"></span>
            <svg class="pl-mark" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="plGrad" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0" stop-color="#fbbf24"/>
                        <stop offset="1" stop-color="#f97316"/>
                    </linearGradient>
                </defs>
                <rect width="48" height="48" rx="14" fill="url(#plGrad)"/>
                <path d="M12 34V14l12 13 12-13v20" fill="none" stroke="#fff" stroke-width="4.5"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="pl-brand brand">My<b>Blog</b></div>
        <div class="pl-bar"><span></span></div>
        <div class="pl-text">Loading, please wait…</div>
    </div>
</div>

<noscript><style>#page-loader{display:none !important}</style></noscript>


<script>
    (function () {
        var loader = document.getElementById('page-loader');
        var root = document.documentElement;
        if (!loader) return;

        var started = Date.now();
        var MIN_TIME = 600;      // loader shows at least 0.6s on first load
        var MAX_TIME = 4000;     // and never longer than 4s on first load
        var loaded = false;
        var showTimer = null;

        root.classList.add('pl-lock');

        function hide() {
            clearTimeout(showTimer);
            loader.classList.add('pl-hide');
            root.classList.remove('pl-lock');
        }

        function show() {
            clearTimeout(showTimer);
            // small delay so very fast navigations don't flash the loader
            showTimer = setTimeout(function () {
                loader.classList.add('pl-fast');
                loader.classList.remove('pl-hide');
                root.classList.add('pl-lock');
            }, 120);
        }

        // First load
        window.addEventListener('load', function () {
            loaded = true;
            setTimeout(hide, Math.max(0, MIN_TIME - (Date.now() - started)));
        });
        setTimeout(function () { if (!loaded) hide(); }, MAX_TIME);

        // Back/forward button (page restored from browser cache)
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) hide();
        });

        // Internal link clicks
        document.addEventListener('click', function (e) {
            if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

            var a = e.target.closest('a[href]');
            if (!a) return;

            var href = a.getAttribute('href');
            if (!href || href.charAt(0) === '#' || /^(javascript:|mailto:|tel:)/i.test(href)) return;
            if (a.target === '_blank' || a.hasAttribute('download') || a.hasAttribute('data-bs-toggle')) return;

            var url;
            try { url = new URL(a.href, location.href); } catch (err) { return; }

            if (url.origin !== location.origin) return;
            if (url.pathname === location.pathname && url.search === location.search && url.hash) return;

            show();
        });

        // Form submits (login, comments, create post, logout, search...)
        document.addEventListener('submit', function (e) {
            if (e.defaultPrevented) return;          // e.g. delete confirm cancelled
            if (e.target.target === '_blank') return;
            show();
        });
    })();
</script>