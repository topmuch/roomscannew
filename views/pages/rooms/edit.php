<?php /** @var array $room, $types, $statuses, $form, $errors, $title */ ?>
<div class="mb-6">
  <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
    <a href="/rooms" class="hover:text-navy-700">Chambres</a>
    <span>/</span>
    <span>N° <?= e($room['number']) ?></span>
  </div>
  <h1 class="text-2xl font-bold text-navy-900">Modifier la chambre N° <?= e($room['number']) ?></h1>
</div>

<?php if (!empty($errors)): ?>
  <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
    <ul class="text-sm text-red-700 list-disc list-inside space-y-0.5">
      <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" action="/rooms/edit/<?= (int) $room['id'] ?>" class="grid lg:grid-cols-3 gap-6">
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
              <option value="<?= (int) $t['id'] ?>" <?= $form['type_id'] === (string) $t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Étage</label>
          <input type="number" name="floor" min="0" value="<?= e($form['floor']) ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Prix par nuit (FCFA)</label>
          <input type="number" name="price" min="0" step="100" value="<?= e($form['price']) ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
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

  <div class="space-y-4">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-4">Actions</h2>
      <button type="submit" class="w-full py-3 rounded-lg bg-gold-400 text-white font-semibold hover:bg-gold-500 transition shadow-sm">Enregistrer</button>
      <a href="/rooms" class="mt-2 block text-center text-sm text-gray-500 hover:text-navy-700">Annuler</a>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-3">QR Code</h2>
      <div class="text-xs text-gray-500 mb-3">Token : <code class="bg-gray-100 px-1.5 py-0.5 rounded text-navy-700 break-all"><?= e($room['qr_token']) ?></code></div>
      <a href="/rooms/qrcode/<?= (int) $room['id'] ?>" class="block text-center py-2 rounded-lg bg-navy-700 text-white text-sm font-medium hover:bg-navy-800">Voir le QR code</a>
    </div>

    <a href="/rooms/delete/<?= (int) $room['id'] ?>" class="block text-center py-2.5 rounded-lg border border-red-200 text-red-600 text-sm font-medium hover:bg-red-50 transition">🗑️ Supprimer</a>
  </div>

  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
</form>
