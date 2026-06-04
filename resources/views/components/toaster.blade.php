@php
    // Collect flash messages + validation errors into a single toast list.
    $toasts = [];

    $flashMap = [
        'success' => 'success',
        'error'   => 'error',
        'danger'  => 'error',
        'warning' => 'warning',
        'info'    => 'info',
        'status'  => 'info',
        'message' => 'info',
    ];

    foreach ($flashMap as $key => $type) {
        if (session()->has($key)) {
            $val = session($key);
            if (is_string($val) && $val !== '') {
                $toasts[] = ['type' => $type, 'message' => $val];
            }
        }
    }

    foreach ($errors->all() as $error) {
        $toasts[] = ['type' => 'error', 'message' => $error];
    }
@endphp

<style>
    @keyframes toast-in {
        0%   { opacity: 0; transform: translateX(120%); }
        100% { opacity: 1; transform: translateX(0); }
    }
    @keyframes toast-out {
        0%   { opacity: 1; transform: translateX(0); }
        100% { opacity: 0; transform: translateX(120%); }
    }
    #toaster { position: fixed; top: 1.25rem; right: 1.25rem; z-index: 9999;
               display: flex; flex-direction: column; gap: 0.75rem;
               width: 22rem; max-width: calc(100vw - 2rem); pointer-events: none; }
    .toast { pointer-events: auto; animation: toast-in .35s cubic-bezier(.21,1.02,.73,1) both; }
    .toast.toast--leaving { animation: toast-out .3s ease forwards; }
</style>

<div id="toaster" aria-live="polite" aria-atomic="true"></div>

<script>
    (function () {
        const ICONS = {
            success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>',
            error:   '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/></svg>',
            warning: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4M12 17h.01"/></svg>',
            info:    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>',
        };

        const STYLES = {
            success: { bg: '#ECFDF3', border: '#A6F4C5', icon: '#16A34A', text: '#166534' },
            error:   { bg: '#FEF3F2', border: '#FECDCA', icon: '#DC2626', text: '#991B1B' },
            warning: { bg: '#FFFAEB', border: '#FEDF89', icon: '#D97706', text: '#92400E' },
            info:    { bg: '#EFF8FF', border: '#B2DDFF', icon: '#2563EB', text: '#1E40AF' },
        };

        const container = document.getElementById('toaster');
        if (!container) return;

        window.toast = function (message, type = 'info', duration = 5000) {
            if (!message) return;
            const s = STYLES[type] || STYLES.info;

            const el = document.createElement('div');
            el.className = 'toast';
            el.setAttribute('role', 'alert');
            el.style.cssText =
                'display:flex;align-items:flex-start;gap:.75rem;padding:.85rem 1rem;border-radius:1rem;' +
                'box-shadow:0 10px 25px -5px rgba(0,0,0,.15);font-family:Nunito,sans-serif;font-size:.9rem;' +
                'line-height:1.35;background:' + s.bg + ';border:1px solid ' + s.border + ';color:' + s.text + ';';

            el.innerHTML =
                '<span style="flex-shrink:0;color:' + s.icon + ';margin-top:1px;">' + (ICONS[type] || ICONS.info) + '</span>' +
                '<span style="flex:1;font-weight:600;word-break:break-word;"></span>' +
                '<button type="button" aria-label="Tutup" style="flex-shrink:0;background:none;border:none;cursor:pointer;' +
                'color:' + s.text + ';opacity:.5;padding:0;line-height:0;margin-top:2px;">' +
                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" ' +
                'stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg></button>';

            el.querySelector('span:nth-child(2)').textContent = message;

            const remove = () => {
                el.classList.add('toast--leaving');
                el.addEventListener('animationend', () => el.remove(), { once: true });
            };

            el.querySelector('button').addEventListener('click', remove);
            container.appendChild(el);

            if (duration > 0) setTimeout(remove, duration);
        };

        const initial = @json($toasts);
        initial.forEach((t, i) => setTimeout(() => window.toast(t.message, t.type), i * 150));
    })();
</script>
