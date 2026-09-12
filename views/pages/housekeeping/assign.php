<?php
/**
 * @var array $task
 * @var array $staff
 * @var string $error
 * @var string $csrf
 */
?>
<div class="mb-6">
  <a href="/housekeeping" class="text-sm text-gray-500 hover:text-navy-700">← Retour</a>
  <h1 class="text-2xl font-bold text-navy-900 mt-2">Affecter la tâche</h1>
  <p class="text-sm text-gray-500 mt-1">
    Chambre <?= e($task['room_number']) ?> —
    <?= e(format_date($task['requested_at'], 'd/m/Y à H:i')) ?>
  </p>
</div>

<div class="max-w-xl bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
  <?php if ($error): ?>
    <div class="mb-4 px-4 py-2.5 rounded-lg bg-red-50 text-red-700 text-sm"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="/housekeeping/assign/<?= (int) $task['id'] ?>" class="space-y-4">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Personne à affecter *</label>
      <select name="assigned_to" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-navy-700 focus:border-transparent">
        <option value="">— Sélectionner —</option>
        <?php foreach ($staff as $s): ?>
          <option value="<?= (int) $s['id'] ?>" <?= $task['assigned_to'] == $s['id'] ? 'selected' : '' ?>>
            <?= e($s['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <?php if (empty($staff)): ?>
        <p class="mt-1 text-xs text-amber-600">Aucun personnel de ménage enregistré. Créez un utilisateur avec le rôle "housekeeping".</p>
      <?php endif; ?>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Priorité</label>
      <select name="priority" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-navy-700 focus:border-transparent">
        <?php foreach (['low' => 'Basse', 'normal' => 'Normale', 'high' => 'Haute', 'urgent' => 'Urgente'] as $key => $label): ?>
          <option value="<?= e($key) ?>" <?= $task['priority'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
      <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-navy-700 focus:border-transparent" placeholder="Instructions spéciales..."><?= e($task['notes'] ?? '') ?></textarea>
    </div>

    <div class="flex gap-2 pt-2">
      <button type="submit" class="px-5 py-2 bg-navy-900 text-white text-sm font-semibold rounded-lg hover:bg-navy-800">Affecter</button>
      <a href="/housekeeping" class="px-5 py-2 text-sm text-gray-600 hover:text-gray-900">Annuler</a>
    </div>
  </form>
</div>
