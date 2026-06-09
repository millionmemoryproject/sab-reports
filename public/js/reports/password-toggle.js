// Show/hide for masked password inputs.
//
// The inputs are real type="text" fields (so macOS Secure Keyboard Entry stays
// OFF and text expanders like Raycast snippets work) and are masked purely with
// CSS (-webkit-text-security). Toggling .is-visible reveals/hides the value.
document.addEventListener('click', function (event) {
    const button = event.target.closest('.password-toggle');
    if (!button) {
        return;
    }

    const field = button.closest('.password-field');
    const input = field ? field.querySelector('.password-input') : null;
    if (!input) {
        return;
    }

    const visible = input.classList.toggle('is-visible');
    button.textContent = visible ? 'Hide' : 'Show';
    button.setAttribute('aria-pressed', visible ? 'true' : 'false');
});

// On submit (e.g. signing in), re-mask any revealed password so it isn't left
// visible on screen. The value still submits — masking is visual only.
document.addEventListener('submit', function (event) {
    event.target.querySelectorAll('.password-input.is-visible').forEach(function (input) {
        input.classList.remove('is-visible');
        const field = input.closest('.password-field');
        const button = field ? field.querySelector('.password-toggle') : null;
        if (button) {
            button.textContent = 'Show';
            button.setAttribute('aria-pressed', 'false');
        }
    });
});

