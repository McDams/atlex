/**
 * ATLÉX-SPORT — Espace d'administration.
 * Confirmation de suppression, Kanban drag-and-drop, bascule publication AJAX.
 */
(function () {
    'use strict';

    // --- Confirmation avant suppression ---
    document.querySelectorAll('[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.getAttribute('data-confirm') || 'Confirmer ?')) {
                e.preventDefault();
            }
        });
    });

    // --- Bascule de publication (actualités) en AJAX ---
    document.querySelectorAll('form.js-toggle-publish').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var body = new FormData(form);
            body.append('toggle', '1');
            body.append('_method', 'PUT');
            fetch(form.getAttribute('action'), {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: body
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data && data.ok) { window.location.reload(); }
                })
                .catch(function () { form.submit(); });
        });
    });

    // --- Kanban drag-and-drop ---
    var csrfInput = document.getElementById('kanban-csrf');
    var cards = document.querySelectorAll('.kanban-card');
    var cols = document.querySelectorAll('.kanban-col');

    if (cards.length && cols.length) {
        var dragged = null;

        cards.forEach(function (card) {
            card.addEventListener('dragstart', function () {
                dragged = card;
                card.classList.add('dragging');
            });
            card.addEventListener('dragend', function () {
                card.classList.remove('dragging');
                dragged = null;
            });
        });

        cols.forEach(function (col) {
            col.addEventListener('dragover', function (e) {
                e.preventDefault();
                col.classList.add('drag-over');
            });
            col.addEventListener('dragleave', function () {
                col.classList.remove('drag-over');
            });
            col.addEventListener('drop', function (e) {
                e.preventDefault();
                col.classList.remove('drag-over');
                if (!dragged) { return; }

                var status = col.getAttribute('data-status');
                var id = dragged.getAttribute('data-id');
                var dropZone = col.querySelector('.kanban-list') || col;
                dropZone.appendChild(dragged);

                persistStatus(id, status, dragged);
            });
        });
    }

    function persistStatus(id, status, card) {
        var body = new FormData();
        body.append('_method', 'PUT');
        body.append('status_only', '1');
        body.append('status', status);
        if (csrfInput) { body.append('_token', csrfInput.value); }

        fetch('/admin/taches/' + id, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: body
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.ok) {
                    if (card) { card.classList.add('ring-2', 'ring-red-500'); }
                }
            })
            .catch(function () {
                if (card) { card.classList.add('ring-2', 'ring-red-500'); }
            });
    }
})();
