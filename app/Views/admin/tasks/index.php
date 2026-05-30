<?php
/** @var array<string,array<int,array<string,mixed>>> $board */
$columns = ['a_faire' => 'À faire', 'en_cours' => 'En cours', 'termine' => 'Terminé'];
$priorityColors = ['urgente' => 'border-l-atlex-red', 'haute' => 'border-l-orange-400', 'normale' => 'border-l-atlex-beige', 'basse' => 'border-l-white/20'];
?>
<div class="flex items-center justify-between mb-6">
    <h2 class="font-bebas text-xl tracking-wider">Tableau Kanban</h2>
    <button type="button" onclick="document.getElementById('new-task').classList.toggle('hidden')" class="btn-atlex text-sm">+ Nouvelle tâche</button>
</div>

<!-- Formulaire nouvelle tâche -->
<form id="new-task" method="POST" action="<?= url('/admin/taches') ?>" class="hidden bg-atlex-dark rounded-xl border border-white/5 p-6 mb-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
    <?= csrf_field() ?>
    <input name="title" required placeholder="Titre de la tâche *" class="form-input sm:col-span-2">
    <select name="status" class="form-input"><?php foreach ($columns as $k => $v): ?><option value="<?= e($k) ?>"><?= e($v) ?></option><?php endforeach; ?></select>
    <select name="priority" class="form-input">
        <option value="normale">Normale</option><option value="basse">Basse</option><option value="haute">Haute</option><option value="urgente">Urgente</option>
    </select>
    <input type="date" name="due_date" class="form-input">
    <textarea name="description" rows="2" placeholder="Description" class="form-input sm:col-span-2"></textarea>
    <button type="submit" class="btn-atlex text-sm w-fit">Créer la tâche</button>
</form>

<!-- Token CSRF pour les actions AJAX de drag-and-drop -->
<input type="hidden" id="kanban-csrf" value="<?= e(\App\Core\CSRF::generateToken()) ?>">

<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <?php foreach ($columns as $statusKey => $label): ?>
        <div class="bg-atlex-dark/60 rounded-xl border border-white/5 p-4 kanban-col" data-status="<?= e($statusKey) ?>">
            <h3 class="font-montserrat font-bold uppercase text-sm tracking-wide text-white/70 mb-4 flex items-center justify-between">
                <?= e($label) ?>
                <span class="text-xs bg-white/10 px-2 py-0.5 rounded-full"><?= count($board[$statusKey]) ?></span>
            </h3>
            <div class="space-y-3 min-h-[100px] kanban-list">
                <?php foreach ($board[$statusKey] as $task): ?>
                    <div class="kanban-card bg-atlex-bg rounded-lg p-4 border border-white/5 border-l-4 <?= $priorityColors[$task['priority']] ?? 'border-l-white/20' ?> cursor-move"
                         draggable="true" data-id="<?= (int) $task['id'] ?>">
                        <p class="font-montserrat font-semibold text-sm"><?= e($task['title']) ?></p>
                        <?php if (!empty($task['description'])): ?>
                            <p class="text-white/50 text-xs mt-1 line-clamp-2"><?= e($task['description']) ?></p>
                        <?php endif; ?>
                        <div class="flex items-center justify-between mt-3">
                            <span class="text-xs text-white/40"><?= $task['due_date'] ? e(format_date_fr($task['due_date'])) : '' ?></span>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] uppercase px-1.5 py-0.5 rounded bg-white/10 text-white/60"><?= e($task['priority']) ?></span>
                                <form method="POST" action="<?= url('/admin/taches/' . $task['id']) ?>" class="inline" data-confirm="Supprimer cette tâche ?">
                                    <?= csrf_field() ?><?= method_field('DELETE') ?>
                                    <button type="submit" class="text-atlex-red text-xs">✕</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
