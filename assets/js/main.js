/* ============================================================
   sajib.bd — behaviour
   1) Accessible catalogue tabs (mouse + arrow keys, ARIA state)
   2) Scroll reveal (reduced-motion aware)
   3) Offer form -> WhatsApp deep link  (field ids: name/email/amount/message)
   ============================================================ */

/* ---------- 1. Tabs ---------- */
(function initTabs() {
    var tablist = document.querySelector('[role="tablist"]');
    if (!tablist) return;
    var tabs = Array.prototype.slice.call(tablist.querySelectorAll('[role="tab"]'));

    function activate(tab, focus) {
        tabs.forEach(function (t) {
            var selected = t === tab;
            t.setAttribute('aria-selected', selected ? 'true' : 'false');
            t.setAttribute('tabindex', selected ? '0' : '-1');
            var panel = document.getElementById(t.getAttribute('aria-controls'));
            if (panel) {
                if (selected) {
                    panel.removeAttribute('hidden');
                    panel.setAttribute('data-active', 'true');
                } else {
                    panel.setAttribute('data-active', 'false');
                    panel.setAttribute('hidden', '');
                }
            }
        });
        if (focus) tab.focus();
    }

    tabs.forEach(function (tab, i) {
        tab.addEventListener('click', function () { activate(tab, false); });
        tab.addEventListener('keydown', function (e) {
            var next = null;
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') next = tabs[(i + 1) % tabs.length];
            else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') next = tabs[(i - 1 + tabs.length) % tabs.length];
            else if (e.key === 'Home') next = tabs[0];
            else if (e.key === 'End') next = tabs[tabs.length - 1];
            if (next) { e.preventDefault(); activate(next, true); }
        });
    });
})();

/* ---------- 2. Scroll reveal ---------- */
(function initReveal() {
    var items = document.querySelectorAll('.reveal');
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce || !('IntersectionObserver' in window)) {
        items.forEach(function (el) { el.classList.add('is-in'); });
        return;
    }
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-in');
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
    items.forEach(function (el) { io.observe(el); });

    // Panels toggled after load may already be visible; reveal them on activation too
    document.querySelectorAll('[role="tab"]').forEach(function (t) {
        t.addEventListener('click', function () {
            var p = document.getElementById(t.getAttribute('aria-controls'));
            if (p) { p.classList.add('is-in'); }
        });
    });
})();

/* ---------- 3. Offer -> WhatsApp ---------- */
function handleSubmit() {
    var name    = document.getElementById('name');
    var email   = document.getElementById('email');
    var amount  = document.getElementById('amount');
    var message = document.getElementById('message');
    var feedback = document.getElementById('feedback');

    if (!name.value.trim() || !email.value.trim() || !amount.value.trim()) {
        alert('Please complete your name, email, and offer amount.');
        return;
    }

    feedback.hidden = false;

    var text = 'Hello! I am interested in buying sajib.bd.\n\n' +
               '*Name:* ' + name.value.trim() + '\n' +
               '*Email:* ' + email.value.trim() + '\n' +
               '*Offer Amount:* ' + amount.value.trim() + '\n' +
               '*Message:* ' + message.value.trim();

    var whatsappUrl = 'https://wa.me/966535453006?text=' + encodeURIComponent(text);

    setTimeout(function () {
        window.open(whatsappUrl, '_blank');
    }, 500);
}
