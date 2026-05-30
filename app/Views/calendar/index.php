<?php
/**
 * @var int $year
 * @var int $month
 * @var array<int,array<string,mixed>> $events
 */
?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
    <h1 class="font-bebas text-5xl sm:text-6xl tracking-wider mb-2">Calendrier</h1>
    <p class="text-white/60 font-montserrat mb-10">Tous les rendez-vous d'ATLÉX-SPORT</p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Calendrier -->
        <div class="lg:col-span-2 bg-atlex-dark rounded-xl p-6 border border-white/5">
            <div class="flex items-center justify-between mb-6">
                <button id="cal-prev" class="px-3 py-1.5 rounded bg-white/5 hover:bg-white/10 font-montserrat text-sm">←</button>
                <h2 id="cal-title" class="font-bebas text-2xl tracking-wider"></h2>
                <button id="cal-next" class="px-3 py-1.5 rounded bg-white/5 hover:bg-white/10 font-montserrat text-sm">→</button>
            </div>
            <div class="grid grid-cols-7 gap-1 text-center text-xs font-montserrat uppercase text-white/40 mb-2">
                <div>Lun</div><div>Mar</div><div>Mer</div><div>Jeu</div><div>Ven</div><div>Sam</div><div>Dim</div>
            </div>
            <div id="cal-grid" class="grid grid-cols-7 gap-1"></div>
        </div>

        <!-- Liste événements -->
        <aside>
            <h3 class="font-bebas text-2xl tracking-wider mb-4">Événements du mois</h3>
            <div id="cal-events" class="space-y-3">
                <p class="text-white/40 text-sm font-montserrat">Sélectionnez une date pour voir les détails.</p>
            </div>
        </aside>
    </div>
</section>

<script>
    window.ATLEX_CALENDAR = {
        year: <?= (int) $year ?>,
        month: <?= (int) $month ?>,
        apiBase: '<?= url('/api/events') ?>'
    };
</script>
<script src="<?= asset('js/calendar.js') ?>" defer></script>
