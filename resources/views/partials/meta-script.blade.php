<script>
(function () {
    const $ = (id) => document.getElementById(id);
    const titleEl   = $('title');
    const excerptEl = $('excerpt');
    const catEl     = document.querySelector('select[name="category_id"]');
    const tagsEl    = $('tagsHidden');
    const imageIn   = $('image');
    const previewEl = $('preview');
    const section   = $('seoSection');

    const STOP = new Set(('about after also always because been before being between both could does doing down each even every from have here into ' +
        'just like make many more most much must only other over same should some such than that their them then there these they this those ' +
        'through under until upon very want were what when where which while will with would your start using used dont doesnt cant well good ' +
        'best easy simple small first time').split(' '));

    const squish = (s) => (s || '').replace(/\s+/g, ' ').trim();
    const cut = (s, n, tail = '') => s.length > n ? s.slice(0, n - tail.length).trimEnd() + tail : s;
    const bodyText = () => squish(quill.getText());
    const slugify = (s) => squish(s).toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    const tagList = () => (tagsEl.value || '').split(',').map(t => t.trim()).filter(Boolean);

    function suggestTitle() { return cut(squish(titleEl.value), 60); }

    function suggestDescription() {
        const text = squish(excerptEl.value) || bodyText();
        return text.length > 157 ? text.slice(0, 157).trimEnd() + '...' : text;
    }

    function suggestKeywords() {
        const score = {};
        [[titleEl.value, 5], [bodyText(), 1]].forEach(([text, weight]) => {
            (text.toLowerCase().match(/\p{L}{4,}/gu) || []).forEach(w => {
                if (!STOP.has(w)) score[w] = (score[w] || 0) + weight;
            });
        });
        const top = Object.entries(score).sort((a, b) => b[1] - a[1]).slice(0, 8).map(e => e[0]);
        const list = [];
        if (catEl && catEl.value) list.push(catEl.options[catEl.selectedIndex].text.toLowerCase());
        tagList().forEach(t => list.push(t.toLowerCase()));
        list.push(...top);
        return cut([...new Set(list)].slice(0, 10).join(', '), 255);
    }

    function suggestAlt() { return cut(squish(titleEl.value), 125); }

    const suggestions = () => ({
        meta_title: suggestTitle(),
        meta_description: suggestDescription(),
        meta_keywords: suggestKeywords(),
        image_alt: suggestAlt()
    });

    const fields = {};
    section.querySelectorAll('.seo-field').forEach(wrap => {
        const name = wrap.dataset.field;
        fields[name] = {
            wrap,
            input: wrap.querySelector('input, textarea'),
            state: wrap.querySelector('.seo-state'),
            reset: wrap.querySelector('.seo-reset'),
            count: wrap.querySelector('[data-count]'),
            min: +wrap.dataset.min,
            max: +wrap.dataset.max,
            unit: wrap.dataset.unit || '',
            auto: wrap.dataset.auto === '1',
            suggest: ''
        };
    });

    function measure(f) {
        return f.unit === 'keywords'
            ? f.input.value.split(',').map(s => s.trim()).filter(Boolean).length
            : f.input.value.length;
    }

    function paint(f) {
        const edited = !f.auto && f.input.value !== f.suggest;
        f.state.textContent = edited ? 'Edited' : 'Auto-suggested';
        f.state.className = 'seo-state ' + (edited ? 'edited' : 'auto');
        f.reset.classList.toggle('show', edited);

        const n = measure(f);
        f.count.textContent = f.unit === 'keywords' ? n + ' keywords' : n + ' / ' + f.max;
        f.count.className = 'counter ms-auto ' + (n >= f.min && n <= f.max ? 'ok' : (n ? 'warn' : ''));
    }

    Object.values(fields).forEach(f => {
        f.input.addEventListener('input', () => { f.auto = false; paint(f); previews(); checks(); });
        f.input.addEventListener('blur', () => {              // emptied -> go back to auto
            if (!f.input.value.trim()) { f.auto = true; refresh(); }
        });
        f.reset.addEventListener('click', () => { f.auto = true; refresh(); });
    });

    function previews() {
        const title = fields.meta_title.input.value.trim() || squish(titleEl.value) || 'Your post title appears here';
        const desc  = fields.meta_description.input.value.trim() || 'Your description appears here. Write a short description or some content.';
        const slug  = section.dataset.slug || slugify(titleEl.value) || 'your-post';
        const host  = section.dataset.host;

        $('gpTitle').textContent = title;
        $('gpDesc').textContent  = desc;
        $('gpUrl').textContent   = host + ' › posts › ' + slug;
        $('spHost').textContent  = host;
        $('spTitle').textContent = title;
        $('spDesc').textContent  = desc;

        const box = $('spImg');
        const hasImg = previewEl && !previewEl.classList.contains('d-none') && previewEl.getAttribute('src');
        if (hasImg) {
            box.style.backgroundImage = 'url("' + previewEl.src + '")';
            box.innerHTML = '';
        } else {
            box.style.backgroundImage = '';
            box.innerHTML = '<i class="bi bi-image"></i>';
        }
    }

    function checks() {
        const f = fields;
        const hasImg = previewEl && !previewEl.classList.contains('d-none') && previewEl.getAttribute('src');
        const inRange = (x) => { const n = measure(x); return n >= x.min && n <= x.max; };

        const items = [
            [inRange(f.meta_title),       'Meta title is 30 to 60 characters'],
            [inRange(f.meta_description), 'Meta description is 120 to 160 characters'],
            [inRange(f.meta_keywords),    '3 to 10 keywords added'],
            [hasImg ? f.image_alt.input.value.trim().length >= 5 : false,
                hasImg ? 'Cover image has alt text' : 'Add a cover image with alt text']
        ];

        const passed = items.filter(i => i[0]).length;
        $('seoScore').textContent = passed + ' / ' + items.length;
        const bar = $('seoBar');
        bar.style.width = (passed / items.length * 100) + '%';
        bar.style.background = passed >= 4 ? '#22c55e' : passed >= 2 ? '#f59e0b' : '#ef4444';

        $('seoChecks').innerHTML = items.map(i =>
            '<li class="' + (i[0] ? 'pass' : 'fail') + '"><i class="bi ' + (i[0] ? 'bi-check-circle-fill' : 'bi-dash-circle') + '"></i>' + i[1] + '</li>'
        ).join('');
    }

    function refresh() {
        const s = suggestions();
        Object.keys(fields).forEach(k => {
            const f = fields[k];
            f.suggest = s[k];
            if (f.auto) f.input.value = s[k];
            paint(f);
        });
        previews();
        checks();
    }

    let timer = null;
    const schedule = () => { clearTimeout(timer); timer = setTimeout(refresh, 250); };

    titleEl.addEventListener('input', schedule);
    excerptEl.addEventListener('input', schedule);
    quill.on('text-change', schedule);
    if (catEl) catEl.addEventListener('change', schedule);
    if (imageIn) imageIn.addEventListener('change', () => setTimeout(refresh, 50));
    new MutationObserver(schedule).observe($('tagBox'), { childList: true });   // tags added / removed

    $('seoRegen').addEventListener('click', () => {
        Object.values(fields).forEach(f => f.auto = true);
        refresh();
    });

    refresh();
})();
</script>