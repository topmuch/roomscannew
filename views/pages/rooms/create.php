<?php /** @var array $types, $statuses, $form, $errors, $title */ ?>
<div class="mb-6">
  <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
    <a href="/rooms" class="hover:text-navy-700">Chambres</a>
    <span>/</span>
    <span>Nouvelle</span>
  </div>
  <h1 class="text-2xl font-bold text-navy-900">Nouvelle chambre</h1>
  <p class="text-sm text-gray-500 mt-1">Un QR code unique sera généré automatiquement.</p>
</div>

<?php if (!empty($errors)): ?>
  <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
    <ul class="text-sm text-red-700 list-disc list-inside space-y-0.5">
      <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" action="/rooms/create" class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Numéro *</label>
          <input type="text" name="number" value="<?= e($form['number']) ?>" required class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
          <select name="type_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
            <option value="">— Aucun —</option>
            <?php foreach ($types as $t): ?>
              <option value="<?= (int) $t['id'] ?>" data-price="<?= (float) $t['base_price'] ?>" <?= $form['type_id'] === (string) $t['id'] ? 'selected' : '' ?>>
                <?= e($t['name']) ?> · <?= format_price((float) $t['base_price']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Étage</label>
          <input type="number" name="floor" min="0" value="<?= e($form['floor']) ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Prix par nuit (FCFA)</label>
          <input type="number" name="price" id="price" min="0" step="100" value="<?= e($form['price']) ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
        </div>
        <div class="sm:col-span-2">
          <label class="block text-xs font-medium text-gray-600 mb-1">Statut</label>
          <select name="status" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
            <?php foreach ($statuses as $k => $v): ?>
              <option value="<?= e($k) ?>" <?= $form['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="sm:col-span-2">
          <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
          <textarea name="notes" rows="2" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none"><?= e($form['notes']) ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-4">Aperçu</h2>
      <div class="text-center py-6">
        <div class="w-20 h-20 mx-auto rounded-2xl bg-navy-700 text-white flex items-center justify-center text-3xl font-bold mb-3">
          <?= e($form['number'] ?: '?') ?>
        </div>
        <div class="text-xs text-gray-400">Le QR code sera généré après création.</div>
      </div>
      <button type="submit" class="w-full py-3 rounded-lg bg-gold-400 text-white font-semibold hover:bg-gold-500 transition shadow-sm">Créer la chambre</button>
      <a href="/rooms" class="mt-2 block text-center text-sm text-gray-500 hover:text-navy-700">Annuler</a>
    </div>
  </div>

  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
</form>

<script>
document.querySelector('select[name="type_id"]').addEventListener('change', function() {
  const opt = this.options[this.selectedIndex];
  const price = opt.dataset.price;
  if (price) document.getElementById('price').value = price;
});
</script>
