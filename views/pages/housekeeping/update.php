<?php
/**
 * @var array  $task
 * @var array  $checklist
 * @var string $error
 * @var string $csrf
 */

$checklistLabels = [
  'bed_made'           => 'Lits faits',
  'towels_changed'     => 'Serviettes changées',
  'floor_cleaned'      => 'Sol nettoyé',
  'bathroom_cleaned'   => 'Salle de bain nettoyée',
  'trash_emptied'      => 'Poubelles vidées',
  'dusting_done'       => 'Poussières faites',
  'windows_cleaned'    => 'Vitres nettoyées',
  'amenities_replaced' => 'Articles de courtoisie remplacés',
];

$statusLabels = [
  'pending'     => 'En attente',
  'in_progress' => 'En cours',
  'completed'   => 'Terminé',
  'inspected'   => 'Inspecté',
  'flagged'     => 'Problème signalé',
];
?>
<div class="mb-6">
  <a href="/housekeeping" class="text-sm text-gray-500 hover:text-navy-700">← Retour</a>
  <h1 class="text-2xl font-bold text-navy-900 mt-2">Tâche ménage — Chambre <?= e($task['room_number']) ?></h1>
  <p class="text-sm text-gray-500 mt-1">
    Demandée le <?= e(format_date($task['requested_at'], 'd/m/Y à H:i')) ?>
    <?php if ($task['assigned_name']): ?> · Affectée à <?= e($task['assigned_name']) ?><?php endif; ?>
  </p>
</div>

<div class="max-w-2xl bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
  <?php if ($error): ?>
    <div class="mb-4 px-4 py-2.5 rounded-lg bg-red-50 text-red-700 text-sm"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="/housekeeping/update/<?= (int) $task['id'] ?>" class="space-y-5">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
      <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-navy-700 focus:border-transparent">
        <?php foreach ($statusLabels as $key => $label): ?>
          <option value="<?= e($key) ?>" <?= $task['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
      <p class="mt-1 text-xs text-gray-400">Compléter → chambre "en nettoyage" · Inspecter → chambre "disponible".</p>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Checklist</label>
      <div class="grid sm:grid-cols-2 gap-2">
        <?php foreach ($checklistLabels as $key => $label): ?>
          <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
            <input type="checkbox" name="<?= e($key) ?>" value="1" <?= !empty($checklist[$key]) ? 'checked' : '' ?> class="w-4 h-4 rounded text-navy-700 focus:ring-navy-700">
            <span class="text-sm text-gray-700"><?= e($label) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Signaler un problème</label>
      <textarea name="flagged_issue" rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-navy-700 focus:border-transparent" placeholder="Ex: Linge manquant, lampe cassée..."><?= e($task['flagged_issue'] ?? '') ?></textarea>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
      <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-navy-700 focus:border-transparent"><?= e($task['notes'] ?? '') ?></textarea>
    </div>

    <div class="flex flex-wrap gap-2 pt-2">
      <button type="submit" class="px-5 py-2 bg-navy-900 text-white text-sm font-semibold rounded-lg hover:bg-navy-800">Enregistrer</button>
      <a href="/housekeeping/delete/<?= (int) $task['id'] ?>" class="px-5 py-2 text-sm text-red-600 hover:text-red-700 font-medium">Supprimer</a>
      <a href="/housekeeping" class="px-5 py-2 text-sm text-gray-600 hover:text-gray-900 ml-auto">Annuler</a>
    </div>
  </form>
</div>
