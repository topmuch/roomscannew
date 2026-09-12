<?php
/**
 * @var array $stats
 * @var array $groups  keys: pending, in_progress, completed, inspected, flagged
 */

$statusLabels = [
  'pending'     => ['label' => 'En attente', 'color' => 'amber'],
  'in_progress' => ['label' => 'En cours',   'color' => 'blue'],
  'completed'   => ['label' => 'Terminé',    'color' => 'green'],
  'inspected'   => ['label' => 'Inspecté',   'color' => 'navy-700'],
  'flagged'     => ['label' => 'Problème',   'color' => 'red'],
];

$priorityLabels = [
  'urgent' => ['label' => 'Urgent',  'class' => 'bg-red-100 text-red-700'],
  'high'   => ['label' => 'Haute',   'class' => 'bg-orange-100 text-orange-700'],
  'normal' => ['label' => 'Normale', 'class' => 'bg-blue-100 text-blue-700'],
  'low'    => ['label' => 'Basse',   'class' => 'bg-gray-100 text-gray-600'],
];

$columns = ['pending', 'in_progress', 'completed', 'inspected'];
?>
<div class="mb-6">
  <h1 class="text-2xl font-bold text-navy-900">Gouvernante</h1>
  <p class="text-sm text-gray-500 mt-1">Tâches de ménage — <?= date('d/m/Y') ?></p>
</div>

<!-- Stats -->
<div class="grid grid-cols-3 gap-3 sm:gap-4 mb-6">
  <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-gray-100">
    <div class="text-[11px] sm:text-xs font-medium text-gray-500 uppercase tracking-wide">En attente</div>
    <div class="text-xl sm:text-2xl font-bold text-amber-600 mt-1"><?= (int) $stats['pending'] ?></div>
  </div>
  <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-gray-100">
    <div class="text-[11px] sm:text-xs font-medium text-gray-500 uppercase tracking-wide">En cours</div>
    <div class="text-xl sm:text-2xl font-bold text-blue-600 mt-1"><?= (int) $stats['in_progress'] ?></div>
  </div>
  <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-gray-100">
    <div class="text-[11px] sm:text-xs font-medium text-gray-500 uppercase tracking-wide">Terminées aujourd'hui</div>
    <div class="text-xl sm:text-2xl font-bold text-green-600 mt-1"><?= (int) $stats['completed_today'] ?></div>
  </div>
</div>

<!-- Kanban Board -->
<div class="overflow-x-auto -mx-2 px-2 pb-2">
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 min-w-[760px] xl:min-w-0">
    <?php foreach ($columns as $status):
      $info  = $statusLabels[$status];
      $items = $groups[$status];
    ?>
      <div class="bg-gray-100 rounded-2xl p-3">
        <div class="flex items-center justify-between mb-3 px-1">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-<?= e($info['color']) ?>-500"></span>
            <h3 class="text-sm font-bold text-gray-700"><?= e($info['label']) ?></h3>
          </div>
          <span class="text-xs font-semibold text-gray-500 bg-white px-2 py-0.5 rounded-full"><?= count($items) ?></span>
        </div>
        <div class="space-y-2 min-h-[60px]">
          <?php if (empty($items)): ?>
            <div class="text-center text-xs text-gray-400 py-4">Aucune tâche</div>
          <?php else: ?>
            <?php foreach ($items as $t):
              $prio = $priorityLabels[$t['priority']] ?? $priorityLabels['normal'];
            ?>
              <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between gap-2">
                  <div class="min-w-0">
                    <div class="text-sm font-bold text-navy-900">Chambre <?= e($t['room_number']) ?></div>
                    <?php if ($t['room_floor']): ?>
                      <div class="text-[10px] text-gray-400">Étage <?= (int) $t['room_floor'] ?></div>
                    <?php endif; ?>
                  </div>
                  <span class="shrink-0 px-1.5 py-0.5 text-[10px] rounded-full <?= e($prio['class']) ?>"><?= e($prio['label']) ?></span>
                </div>

                <?php if ($t['assigned_name']): ?>
                  <div class="mt-2 text-xs text-gray-600 flex items-center gap-1">
                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="truncate"><?= e($t['assigned_name']) ?></span>
                  </div>
                <?php else: ?>
                  <div class="mt-2 text-xs text-amber-600 font-medium">Non assignée</div>
                <?php endif; ?>

                <?php if ($t['notes']): ?>
                  <div class="mt-2 text-xs text-gray-500 line-clamp-2"><?= e($t['notes']) ?></div>
                <?php endif; ?>

                <div class="mt-2 text-[10px] text-gray-400">
                  <?= e(format_date($t['requested_at'], 'd/m/Y H:i')) ?>
                </div>

                <div class="mt-2 flex gap-1">
                  <?php if ($status === 'pending'): ?>
                    <a href="/housekeeping/assign/<?= (int) $t['id'] ?>" class="flex-1 text-center text-[11px] font-semibold px-2 py-1 rounded-md bg-navy-700 text-white hover:bg-navy-800">Affecter</a>
                  <?php endif; ?>
                  <a href="/housekeeping/update/<?= (int) $t['id'] ?>" class="flex-1 text-center text-[11px] font-semibold px-2 py-1 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200">Modifier</a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Flagged tasks (issues) -->
<?php if (!empty($groups['flagged'])): ?>
  <div class="mt-6 bg-red-50 rounded-2xl p-5 border border-red-200">
    <h3 class="text-sm font-bold text-red-700 mb-3 flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
      </svg>
      Tâches signalées (<?= count($groups['flagged']) ?>)
    </h3>
    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-3">
      <?php foreach ($groups['flagged'] as $t): ?>
        <div class="bg-white rounded-xl p-3 border border-red-100">
          <div class="text-sm font-bold text-navy-900">Chambre <?= e($t['room_number']) ?></div>
          <?php if ($t['flagged_issue']): ?>
            <div class="mt-1 text-xs text-red-600 line-clamp-2"><?= e($t['flagged_issue']) ?></div>
          <?php endif; ?>
          <?php if ($t['assigned_name']): ?>
            <div class="mt-1 text-xs text-gray-500">Affectée à <?= e($t['assigned_name']) ?></div>
          <?php endif; ?>
          <a href="/housekeeping/update/<?= (int) $t['id'] ?>" class="mt-2 inline-block text-xs font-semibold text-navy-700 hover:underline">Voir →</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>
