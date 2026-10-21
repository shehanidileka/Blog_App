<script>
(function () {
    // Show / hide password
    document.querySelectorAll('[data-toggle-pw]').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.togglePw);
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    });

    // Caps Lock warning
    document.querySelectorAll('input[data-caps]').forEach(input => {
        const hint = document.getElementById(input.dataset.caps);
        const check = e => hint.classList.toggle('show', e.getModifierState && e.getModifierState('CapsLock'));
        input.addEventListener('keydown', check);
        input.addEventListener('keyup', check);
        input.addEventListener('blur', () => hint.classList.remove('show'));
    });

    // Submit loading state
    document.querySelectorAll('form[data-auth-form]').forEach(form => {
        form.addEventListener('submit', () => {
            const btn = form.querySelector('.btn-main');
            if (!form.checkValidity()) return;
            btn.classList.add('loading');
            btn.querySelector('.btn-text').textContent = btn.dataset.loading;
        });
    });
    window.addEventListener('pageshow', e => {
        if (!e.persisted) return;
        document.querySelectorAll('.btn-main.loading').forEach(b => {
            b.classList.remove('loading');
            b.querySelector('.btn-text').textContent = b.dataset.label;
        });
    });

    // Shake card when there are errors
    if (document.querySelector('.auth-form .alert-danger')) {
        setTimeout(() => document.querySelector('.auth-card').classList.add('shake'), 700);
    }
})();
</script>