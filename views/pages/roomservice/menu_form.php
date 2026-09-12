<?php
/**
 * @var array  $item
 * @var bool   $isEdit
 * @var string $error
 * @var string $csrf
 */

$categoryLabels = [
  'breakfast' => 'Petit-déjeuner',
  'lunch'     => 'Déjeuner',
  'dinner'    => 'Dîner',
  'drinks'    => 'Boissons',
  'snacks'    => 'Snacks',
];
?>
<div class="mb-6">
  <a href="/roomservice/menu" class="text-sm text-gray-500 hover:text-navy-700">← Menu</a>
  <h1 class="text-2xl font-bold text-navy-900 mt-2"><?= $isEdit ? 'Modifier l\'article' : 'Ajouter un article' ?></h1>
</div>

<div class="max-w-2xl bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
  <?php if ($error): ?>
    <div class="mb-4 px-4 py-2.5 rounded-lg bg-red-50 text-red-700 text-sm"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="<?= $isEdit ? '/roomservice/menu/edit/' . (int) $item['id'] : '/roomservice/menu/create' ?>" enctype="multipart/form-data" class="space-y-4">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Nom de l'article *</label>
      <input type="text" name="name" value="<?= e($item['name']) ?>" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-navy-700 focus:border-transparent">
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
      <textarea name="description" rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-navy-700 focus:border-transparent" placeholder="Ingrédients, allergènes..."><?= e($item['description']) ?></textarea>
    </div>

    <div class="grid sm:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Prix (FCFA) *</label>
        <input type="number" step="any" name="price" value="<?= e($item['price']) ?>" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-navy-700 focus:border-transparent">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
        <select name="category" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-navy-700 focus:border-transparent">
          <?php foreach ($categoryLabels as $key => $label): ?>
            <option value="<?= e($key) ?>" <?= ($item['category'] ?? '') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Préparation (min)</label>
        <input type="number" name="prep_time" min="0" value="<?= e($item['prep_time']) ?>" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-navy-700 focus:border-transparent">
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
      <?php if (!empty($item['image'])): ?>
        <div class="mb-2 w-24 h-24 rounded-lg overflow-hidden border border-gray-200">
          <img src="<?= e($item['image']) ?>" alt="" class="w-full h-full object-cover">
        </div>
      <?php endif; ?>
      <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
      <p class="mt-1 text-xs text-gray-400">JPG, PNG ou WEBP · 2 Mo max · facultatif</p>
    </div>

    <label class="flex items-center gap-2">
      <input type="checkbox" name="available" value="1" <?= !empty($item['available']) ? 'checked' : '' ?> class="w-4 h-4 rounded text-navy-700 focus:ring-navy-700">
      <span class="text-sm text-gray-700">Disponible à la commande</span>
    </label>

    <div class="flex gap-2 pt-2">
      <button type="submit" class="px-5 py-2 bg-navy-900 text-white text-sm font-semibold rounded-lg hover:bg-navy-800"><?= $isEdit ? 'Enregistrer' : 'Ajouter' ?></button>
      <a href="/roomservice/menu" class="px-5 py-2 text-sm text-gray-600 hover:text-gray-900">Annuler</a>
    </div>
  </form>
</div>
