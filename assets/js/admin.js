// Admin panel repeaters: add / remove rows and keep field indices contiguous.
// PHP reads $_POST[<prefix>][<i>][<key>] in a 0..count-1 loop, so after any
// add/remove we renumber every row's field names and sync the hidden _count.
(function () {
    'use strict';

    function renumber(container) {
        var prefix = container.dataset.prefix;
        if (!prefix) return;
        var rows = container.querySelectorAll('.row');
        rows.forEach(function (row, idx) {
            row.querySelectorAll('input, select, textarea').forEach(function (el) {
                var name = el.getAttribute('name');
                if (!name) return;
                // Replace the numeric segment right after the prefix.
                var next = name.replace(new RegExp('^' + prefix + '\\[(\\d+)\\]'), prefix + '[' + idx + ']');
                if (next !== name) el.setAttribute('name', next);
            });
        });
        updateCount(container);
    }

    function updateCount(container) {
        var prefix = container.dataset.prefix;
        var count = container.querySelectorAll('.row').length;
        var hidden = document.querySelector('input[name="' + prefix + '_count"]');
        if (hidden) hidden.value = String(count);
    }

    function addRow(container) {
        var tpl = document.getElementById('tpl-' + container.dataset.prefix);
        if (!tpl) return;
        var idx = container.querySelectorAll('.row').length; // temporary index
        var html = tpl.innerHTML.replace(/__I__/g, String(idx));
        var wrap = document.createElement('div');
        wrap.innerHTML = html.trim();
        var row = wrap.firstElementChild;
        if (row) container.appendChild(row);
        renumber(container);
    }

    document.querySelectorAll('.repeater').forEach(function (container) {
        // Add buttons live in the same card, before the counter div.
        var card = container.closest('.card');
        if (card) {
            card.querySelectorAll('.btn.add').forEach(function (btn) {
                if (btn.dataset.target) {
                    var target = document.querySelector(btn.dataset.target);
                    if (target) btn.addEventListener('click', function () { addRow(target); });
                } else {
                    btn.addEventListener('click', function () { addRow(container); });
                }
            });
        }
        // Remove buttons are delegated so cloned rows work too.
        container.addEventListener('click', function (e) {
            var rm = e.target.closest('.btn.remove');
            if (rm && container.contains(rm)) {
                var row = rm.closest('.row');
                if (row) row.remove();
                renumber(container);
            }
        });
        renumber(container);
    });

    // Tab navigation: show one panel at a time, hide the save bar on Backup.
    var tabs = document.querySelectorAll('.tab');
    var panels = document.querySelectorAll('.tab-panel');
    var saveBar = document.getElementById('saveBar');
    function activateTab(name) {
        tabs.forEach(function (t) { t.classList.toggle('is-active', t.dataset.tab === name); });
        panels.forEach(function (p) { p.classList.toggle('is-active', p.dataset.panel === name); });
        if (saveBar) saveBar.classList.toggle('hidden', name === 'backup');
    }
    tabs.forEach(function (t) {
        t.addEventListener('click', function () { activateTab(t.dataset.tab); });
    });

    // Restore needs confirmation (replaces the old inline onclick).
    document.querySelectorAll('.btn.confirm-restore').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm('Restore this backup and overwrite current data.php?')) {
                e.preventDefault();
            }
        });
    });
})();
