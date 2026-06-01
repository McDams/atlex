/**
 * ATLEX - Sport — Calendrier interactif.
 * Récupère les événements via /api/events/{year}/{month} et affiche
 * une grille mensuelle avec pastilles + détails de la date sélectionnée.
 */
(function () {
    'use strict';

    var cfg = window.ATLEX_CALENDAR;
    if (!cfg) { return; }

    var grid = document.getElementById('cal-grid');
    var title = document.getElementById('cal-title');
    var eventsBox = document.getElementById('cal-events');
    var prevBtn = document.getElementById('cal-prev');
    var nextBtn = document.getElementById('cal-next');
    if (!grid || !title) { return; }

    var MONTHS = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    var WEEKDAYS = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

    var year = cfg.year;
    var month = cfg.month;
    var current = [];

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    function formatTime(dt) {
        if (!dt) { return ''; }
        var d = new Date(dt.replace(' ', 'T'));
        if (isNaN(d.getTime())) { return ''; }
        return pad(d.getHours()) + 'h' + pad(d.getMinutes());
    }

    function eventsForDay(day) {
        return current.filter(function (e) { return e.day === day; });
    }

    function renderEventsList(day) {
        if (!eventsBox) { return; }
        var list = eventsForDay(day);
        if (!list.length) {
            eventsBox.innerHTML = '<p class="text-white/40 text-sm font-montserrat">Aucun événement ce jour-là.</p>';
            return;
        }
        eventsBox.innerHTML = list.map(function (e) {
            var time = formatTime(e.start);
            var loc = e.location
                ? '<p class="text-white/50 text-xs mt-1">' + escapeHtml(e.location) + '</p>'
                : '';
            return '' +
                '<div class="bg-white/5 rounded-lg p-3 border-l-4 border-atlex-red">' +
                '<p class="font-montserrat text-xs text-atlex-red uppercase tracking-wide">' +
                (time ? time + ' · ' : '') + escapeHtml(e.type || '') + '</p>' +
                '<p class="font-poppins font-semibold mt-0.5">' + escapeHtml(e.title) + '</p>' +
                loc +
                '</div>';
        }).join('');
    }

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function buildGrid() {
        title.textContent = MONTHS[month - 1] + ' ' + year;
        grid.innerHTML = '';

        WEEKDAYS.forEach(function (wd) {
            var head = document.createElement('div');
            head.className = 'text-center text-white/40 text-xs font-montserrat py-2';
            head.textContent = wd;
            grid.appendChild(head);
        });

        var firstDay = new Date(year, month - 1, 1).getDay(); // 0=Dim
        var offset = (firstDay + 6) % 7; // Lundi en tête
        var daysInMonth = new Date(year, month, 0).getDate();

        for (var i = 0; i < offset; i++) {
            var blank = document.createElement('div');
            grid.appendChild(blank);
        }

        var today = new Date();
        var isCurrentMonth = today.getFullYear() === year && (today.getMonth() + 1) === month;

        for (var day = 1; day <= daysInMonth; day++) {
            (function (d) {
                var cell = document.createElement('button');
                cell.type = 'button';
                cell.className = 'relative aspect-square rounded-lg flex items-center justify-center ' +
                    'text-sm font-montserrat bg-white/5 hover:bg-white/10 transition-colors';
                cell.textContent = d;

                if (isCurrentMonth && today.getDate() === d) {
                    cell.classList.add('ring-1', 'ring-white/30');
                }

                if (eventsForDay(d).length) {
                    cell.classList.add('bg-atlex-red/20', 'text-white');
                    var dot = document.createElement('span');
                    dot.className = 'absolute bottom-1.5 w-1.5 h-1.5 rounded-full bg-atlex-red';
                    cell.appendChild(dot);
                }

                cell.addEventListener('click', function () {
                    grid.querySelectorAll('button').forEach(function (b) {
                        b.classList.remove('ring-2', 'ring-atlex-red');
                    });
                    cell.classList.add('ring-2', 'ring-atlex-red');
                    renderEventsList(d);
                });

                grid.appendChild(cell);
            })(day);
        }
    }

    function load() {
        if (eventsBox) {
            eventsBox.innerHTML = '<p class="text-white/40 text-sm font-montserrat">Chargement…</p>';
        }
        fetch(cfg.apiBase + '/' + year + '/' + month, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : { events: [] }; })
            .then(function (data) {
                current = (data && data.events) ? data.events : [];
                buildGrid();
                if (eventsBox) {
                    eventsBox.innerHTML = '<p class="text-white/40 text-sm font-montserrat">' +
                        'Sélectionnez une date pour voir les détails.</p>';
                }
            })
            .catch(function () {
                current = [];
                buildGrid();
            });
    }

    function shift(delta) {
        month += delta;
        if (month < 1) { month = 12; year--; }
        else if (month > 12) { month = 1; year++; }
        load();
    }

    if (prevBtn) { prevBtn.addEventListener('click', function () { shift(-1); }); }
    if (nextBtn) { nextBtn.addEventListener('click', function () { shift(1); }); }

    load();
})();
