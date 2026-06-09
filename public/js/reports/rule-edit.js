// Toggle product-group rule rows between read-only and edit mode.
document.addEventListener('click', function (event) {
    const editBtn = event.target.closest('[data-rule-edit]');
    if (editBtn) {
        const row = editBtn.closest('.rule-row');
        if (row) {
            row.classList.add('is-editing');
        }
        return;
    }

    const cancelBtn = event.target.closest('[data-rule-cancel]');
    if (cancelBtn) {
        const row = cancelBtn.closest('.rule-row');
        if (row) {
            row.classList.remove('is-editing');
        }
        // Restore the row's fields to their saved values.
        const form = document.getElementById(cancelBtn.getAttribute('data-rule-cancel'));
        if (form) {
            form.reset();
        }
    }
});
