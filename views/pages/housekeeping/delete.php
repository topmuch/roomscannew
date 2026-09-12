<?php
/**
 * @var array  $task
 * @var string $error
 * @var string $csrf
 */
?>
<div class="mb-6">
  <a href="/housekeeping" class="text-sm text-gray-500 hover:text-navy-700">← Retour</a>
  <h1 class="text-2xl font-bold text-navy-900 mt-2">Supprimer la tâche</h1>
</div>

<div class="max-w-xl bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
  <?php if ($error): ?>
    <div class="mb-4 px-4 py-2.5 rounded-lg bg-red-50 text-red-700 text-sm"><?= e($error) ?></div>
  <?php endif; ?>

  <p class="text-sm text-gray-600 mb-4">
    Êtes-vous sûr de vouloir supprimer la tâche de ménage pour la chambre
    <strong class="text-navy-900"><?= e($task['room_number']) ?></strong> ?
    Cette action est irréversible.
  </p>

  <form method="POST" action="/housekeeping/delete/<?= (int) $task['id'] ?>" class="flex gap-2">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <button type="submit" class="px-5 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700">Supprimer</button>
    <a href="/housekeeping" class="px-5 py-2 text-sm text-gray-600 hover:text-gray-900">Annuler</a>
  </form>
</div>
