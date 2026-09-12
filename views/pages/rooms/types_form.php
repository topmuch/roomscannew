<?php
/**
 * Shared form for create / edit room type.
 * Variables: $form, $errors, $title, $formAction, $isEdit
 */
?>
<div class="mb-6">
  <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
    <a href="/rooms" class="hover:text-navy-700">Chambres</a>
    <span>/</span>
    <a href="/rooms/types" class="hover:text-navy-700">Types</a>
    <span>/</span>
    <span><?= $isEdit ? 'Modifier' : 'Nouveau' ?></span>
  </div>
  <h1 class="text-2xl font-bold text-navy-900"><?= $isEdit ? 'Modifier le type' : 'Nouveau type de chambre' ?></h1>
</div>

<?php if (!empty($errors)): ?>
  <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
    <ul class="text-sm text-red-700 list-disc list-inside space-y-0.5">
      <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" action="<?= e($formAction) ?>" class="max-w-2xl">
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-4">
    <div>
      <label class="block text-xs font-medium text-gray-600 mb-1">Nom *</label>
      <input type="text" name="name" value="<?= e($form['name']) ?>" required placeholder="Ex. Standard, Deluxe, Suite…" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
      <textarea name="description" rows="3" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none"><?= e($form['description']) ?></textarea>
    </div>
    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Prix de base (FCFA)</label>
        <input type="number" name="base_price" min="0" step="100" value="<?= e($form['base_price']) ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Capacité (pers.)</label>
        <input type="number" name="capacity" min="1" value="<?= e($form['capacity']) ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
      </div>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 mb-1">Équipements <span class="text-gray-400 font-normal">(séparés par des virgules)</span></label>
      <input type="text" name="amenities" value="<?= e($form['amenities']) ?>" placeholder="wifi, tv, climatisation, minibar, coffre, balcon" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
    </div>
  </div>

  <div class="mt-6 flex gap-3">
    <a href="/rooms/types" class="flex-1 text-center py-2.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50">Annuler</a>
    <button type="submit" class="flex-1 py-2.5 rounded-lg bg-gold-400 text-white text-sm font-semibold hover:bg-gold-500 transition shadow-sm"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
  </div>

  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
</form>
