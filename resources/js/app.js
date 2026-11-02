const menuButton = document.querySelector('[data-menu]');
menuButton?.addEventListener('click', () => {
    const expanded = menuButton.getAttribute('aria-expanded') === 'true';
    menuButton.setAttribute('aria-expanded', String(!expanded));
    document.querySelector('#sidebar')?.classList.toggle('is-open', !expanded);
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && menuButton) {
        menuButton.setAttribute('aria-expanded', 'false');
        document.querySelector('#sidebar')?.classList.remove('is-open');
    }
});

document.querySelector('[data-dismiss]')?.addEventListener('click', (event) => {
    event.currentTarget.closest('.notice').remove();
});

const dialog = document.querySelector('#delete-dialog');
let pendingDeleteForm = null;
document.querySelectorAll('[data-delete-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        pendingDeleteForm = form;
        dialog.querySelector('[data-delete-title]').textContent = form.dataset.taskTitle;
        dialog.showModal();
        dialog.querySelector('[data-cancel-delete]').focus();
    });
});
dialog?.querySelector('[data-cancel-delete]').addEventListener('click', () => dialog.close());
dialog?.querySelector('[data-confirm-delete]').addEventListener('click', (event) => {
    if (pendingDeleteForm) {
        event.currentTarget.disabled = true;
        pendingDeleteForm.submit();
    }
});
