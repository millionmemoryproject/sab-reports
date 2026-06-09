// Lightweight password strength meter (no dependencies).
// Attaches to each `.js-pw-strength` input and updates the `.pw-meter` element
// that immediately follows its `.password-field` wrapper.
(function () {
    const LEVELS = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    const CLASSES = ['', 'is-weak', 'is-fair', 'is-good', 'is-strong'];

    function score(pw) {
        if (!pw) {
            return 0;
        }

        let s = 0;
        if (pw.length >= 8) s++;
        if (pw.length >= 12) s++;
        if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) s++;
        if (/\d/.test(pw)) s++;
        if (/[^A-Za-z0-9]/.test(pw)) s++;

        return Math.min(s, 4);
    }

    document.querySelectorAll('.js-pw-strength').forEach(function (input) {
        const field = input.closest('.password-field');
        const meter = field ? field.nextElementSibling : null;
        if (!meter || !meter.classList.contains('pw-meter')) {
            return;
        }

        const fill = meter.querySelector('.pw-meter-fill');
        const label = meter.querySelector('.pw-meter-label');

        input.addEventListener('input', function () {
            const value = input.value;
            const s = score(value);

            CLASSES.forEach(function (c) {
                if (c) meter.classList.remove(c);
            });
            if (CLASSES[s]) {
                meter.classList.add(CLASSES[s]);
            }

            fill.style.width = (s * 25) + '%';
            label.textContent = value ? LEVELS[s] : '';
        });
    });
})();
