<?php
/**
 * @var array $item
 * @var string $error, $csrf, $title
 */
?>
<div class="mb-6 flex items-center gap-3">
  <a href="/menu" class="text-gray-400 hover:text-navy-700 text-sm">← Retour</a>
  <h1 class="text-2xl font-bold text-navy-900">Modifier article</h1>
</div>

<?php if ($error): ?>
  <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm"><?= e($error) ?></div>
<?php endif; ?>

<form method="POST" action="/menu/edit/<?= (int) $item['id'] ?>" enctype="multipart/form-data" class="grid lg:grid-cols-3 gap-6">
  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrf ?>">

  <!-- Main fields -->
  <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-4">
    <div>
      <label class="block text-xs font-medium text-gray-600 mb-1">Nom de l'article *</label>
      <input type="text" name="name" required value="<?= e($item['name']) ?>"
             class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
      <textarea name="description" rows="3"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none"><?= e($item['description'] ?? '') ?></textarea>
    </div>
    <div class="grid sm:grid-cols-3 gap-4">
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Prix (FCFA) *</label>
        <input type="number" name="price" min="0" step="0.01" required value="<?= e($item['price']) ?>"
               class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Catégorie</label>
        <select name="category" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
          <?php foreach (['breakfast' => 'Petit-déjeuner', 'lunch' => 'Déjeuner', 'dinner' => 'Dîner', 'drinks' => 'Boissons', 'snacks' => 'Snacks'] as $key => $label): ?>
            <option value="<?= $key ?>" <?= ($item['category'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
          <option value="Autre" <?= ($item['category'] ?? '') === 'Autre' ? 'selected' : '' ?>>Autre</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Préparation (min)</label>
        <input type="number" name="prep_time" min="0" step="1" value="<?= (int) $item['prep_time'] ?>"
               class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
      </div>
    </div>
    <label class="flex items-center gap-2 cursor-pointer">
      <input type="checkbox" name="available" value="1" <?= $item['available'] ? 'checked' : '' ?> class="w-4 h-4 accent-navy-700">
      <span class="text-sm text-gray-700">Disponible à la commande</span>
    </label>
  </div>

  <!-- Image upload -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
    <h2 class="text-sm font-bold text-navy-900 mb-3">Image</h2>
    <?php if (!empty($item['image'])): ?>
      <div class="mb-3">
        <img src="<?= e($item['image']) ?>" alt="" class="w-full aspect-[4/3] object-cover rounded-lg border border-gray-200">
        <p class="text-[10px] text-gray-400 mt-1 text-center">Image actuelle</p>
      </div>
    <?php endif; ?>
    <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center">
      <div class="text-3xl text-gray-300 mb-2">🖼️</div>
      <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
             class="text-xs file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-navy-50 file:text-navy-700 file:font-semibold file:cursor-pointer">
      <p class="text-[10px] text-gray-400 mt-2"><?= !empty($item['image']) ? 'Remplacer (optionnel)' : 'jpg, png, webp · max 2 Mo' ?></p>
    </div>
    <button type="submit" class="w-full mt-6 py-2.5 rounded-lg bg-gold-400 text-white text-sm font-semibold hover:bg-gold-500 transition">Enregistrer</button>
  </div>
</form>
