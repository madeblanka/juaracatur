    <script>
    (function () {
        const badge = document.getElementById('notif-badge');
        const itemsEl = document.getElementById('notif-items');
        if (!badge || !itemsEl) return;

        const csrfToken = document.querySelector('meta[name=csrf-token]').content;

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        async function poll() {
            try {
                const res = await fetch('{{ route('notifications.unread') }}', { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();

                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = '';
                } else {
                    badge.style.display = 'none';
                }

                if (data.items.length === 0) {
                    itemsEl.innerHTML = '<li><span class="dropdown-item-text small text-muted">Tidak ada notifikasi baru.</span></li>';
                    return;
                }

                itemsEl.innerHTML = data.items.map(function (n) {
                    return '<li><form method="POST" action="' + n.read_url + '">' +
                        '<input type="hidden" name="_token" value="' + csrfToken + '">' +
                        '<button type="submit" class="dropdown-item small text-wrap">' + escapeHtml(n.message) + '</button>' +
                        '</form></li>';
                }).join('');
            } catch (e) { /* ignore transient network errors */ }
        }

        setInterval(poll, 10000);
    })();
    </script>
