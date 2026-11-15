<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    // Mobile sidebar
    const sidebar = document.getElementById('sidebar'), backdrop = document.getElementById('sidebarBackdrop');
    document.getElementById('sidebarToggle')?.addEventListener('click', () => { sidebar.classList.toggle('open'); backdrop.classList.toggle('open'); });
    backdrop?.addEventListener('click', () => { sidebar.classList.remove('open'); backdrop.classList.remove('open'); });

    // Confirmation modal: <form data-confirm="Message" data-confirm-title="Title" data-confirm-button="Delete">
    const modalEl = document.getElementById('confirmModal');
    let pendingForm = null;
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (form.dataset.confirm && !form.dataset.confirmed) {
                e.preventDefault();
                pendingForm = form;
                document.getElementById('confirmTitle').textContent = form.dataset.confirmTitle || 'Are you sure?';
                document.getElementById('confirmMessage').textContent = form.dataset.confirm;
                document.getElementById('confirmOk').textContent = form.dataset.confirmButton || 'Confirm';
                const nameEl = document.getElementById('confirmName'), noteEl = document.getElementById('confirmNote');
                nameEl.textContent = form.dataset.confirmName || ''; nameEl.classList.toggle('d-none', !form.dataset.confirmName);
                noteEl.textContent = form.dataset.confirmNote || ''; noteEl.classList.toggle('d-none', !form.dataset.confirmNote);
                modal.show();
            }
        }, true);
        document.getElementById('confirmOk').addEventListener('click', function () {
            if (pendingForm) { pendingForm.dataset.confirmed = '1'; modal.hide(); pendingForm.requestSubmit(); }
        });
    }

    // Loading indicator: disable the submit button and show a spinner once a valid form is submitted
    document.addEventListener('submit', function (e) {
        if (e.defaultPrevented) return;
        const btn = e.target.querySelector('button[type=submit], button:not([type])');
        if (btn && !btn.classList.contains('no-loading')) {
            setTimeout(() => {
                btn.disabled = true;
                btn.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm me-2" role="status"></span>');
            }, 0);
        }
    });
    // Restore buttons when the user comes back with the browser Back button
    window.addEventListener('pageshow', e => { if (e.persisted) location.reload(); });
})();
</script>
<script>
    // Thin red loading bar while the browser navigates to another page
    (function () {
        const bar = document.createElement('div'); bar.className = 'top-loader'; document.body.appendChild(bar);
        window.addEventListener('beforeunload', () => { bar.classList.add('run'); setTimeout(() => bar.classList.remove('run'), 10000); });
        window.addEventListener('pageshow', () => bar.classList.remove('run'));
    })();
</script>
