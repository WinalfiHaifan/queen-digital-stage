document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    if (!window.confirm(form.dataset.confirmDelete || 'Delete this record?')) event.preventDefault();
  });
});
