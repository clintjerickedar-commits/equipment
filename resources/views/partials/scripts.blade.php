<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    // Mobile sidebar
    const sidebar = document.getElementById('sidebar'), backdrop = document.getElementById('sidebarBackdrop');
    document.getElementById('sidebarToggle')?.addEventListener('click', () => { sidebar.classList.toggle('open'); backdrop.classList.toggle('open'); });
    backdrop?.addEventListener('click', () => { sidebar.classList.remove('open'); backdrop.classList.remove('open'); });

    // Opening the notification menu marks its displayed items as read.
    const notificationMenu = document.querySelector('.notif-menu');
    const notificationDropdown = notificationMenu?.closest('.dropdown');
    if (notificationMenu && notificationDropdown) {
        let markingRead = null;
        let notificationsMarked = false;
        const markNotificationsRead = () => {
            if (notificationsMarked) return Promise.resolve(true);
            if (markingRead) return markingRead;
            const keys = Array.from(notificationMenu.querySelectorAll('[data-notification-key]'))
                .map(item => item.dataset.notificationKey);
            if (!keys.length) return Promise.resolve(true);

            const error = notificationMenu.querySelector('[data-notification-error]');
            markingRead = fetch(notificationMenu.dataset.readUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ keys }),
            }).then(response => {
                if (!response.ok) throw new Error('Notification read request failed.');
                notificationMenu.querySelector('.head .badge-soft').textContent = '0';
                document.querySelector('.notif-badge')?.remove();
                error.classList.add('d-none');
                notificationsMarked = true;
                return true;
            }).catch(() => {
                error.classList.remove('d-none');
                return false;
            }).finally(() => {
                markingRead = null;
            });

            return markingRead;
        };

        notificationDropdown.querySelector('[data-bs-toggle="dropdown"]')
            ?.addEventListener('shown.bs.dropdown', markNotificationsRead);
        notificationMenu.querySelectorAll('[data-notification-key]').forEach(item => {
            item.addEventListener('click', event => {
                event.preventDefault();
                markNotificationsRead().then(success => {
                    if (success) window.location.assign(item.href);
                });
            });
        });
    }

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
