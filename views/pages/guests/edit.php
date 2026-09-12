<?php /** @var array $guest, $form, $errors, $idTypes, $countries, $title */ ?>
<div class="mb-6">
  <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
    <a href="/guests" class="hover:text-navy-700">Clients</a>
    <span>/</span>
    <a href="/guests/view/<?= (int) $guest['id'] ?>" class="hover:text-navy-700"><?= e($guest['name']) ?></a>
    <span>/</span>
    <span>Modifier</span>
  </div>
  <h1 class="text-2xl font-bold text-navy-900">Modifier le client</h1>
</div>

<?php if (!empty($errors)): ?>
  <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
    <ul class="text-sm text-red-700 list-disc list-inside space-y-0.5">
      <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" action="/guests/edit/<?= (int) $guest['id'] ?>" class="max-w-3xl">
  <div class="grid sm:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-4">Identité</h2>
      <div class="space-y-3">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Nom complet *</label>
          <input type="text" name="name" value="<?= e($form['name']) ?>" required class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
          <input type="email" name="email" value="<?= e($form['email']) ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Téléphone</label>
          <input type="text" name="phone" value="<?= e($form['phone']) ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Pays</label>
          <select name="country" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
            <?php foreach ($countries as $c): ?>
              <option value="<?= e($c) ?>" <?= $form['country'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-4">Pièce d'identité & VIP</h2>
      <div class="space-y-3">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Type de pièce</label>
          <select name="id_type" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
            <?php foreach ($idTypes as $k => $v): ?>
              <option value="<?= e($k) ?>" <?= $form['id_type'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Numéro de pièce</label>
          <input type="text" name="id_number" value="<?= e($form['id_number']) ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
        </div>
        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
          <input type="checkbox" name="vip" value="1" <?= !empty($form['vip']) ? 'checked' : '' ?> class="w-4 h-4 accent-gold-500">
          <div>
            <div class="text-sm font-medium text-navy-900">Client VIP ★</div>
            <div class="text-xs text-gray-500">Avantages et traitement prioritaire</div>
          </div>
        </label>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mt-6">
    <h2 class="text-sm font-bold text-navy-900 mb-4">Préférences & notes</h2>
    <div class="space-y-3">
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Préférences</label>
        <textarea name="preferences" rows="3" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none"><?= e($form['preferences']) ?></textarea>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Notes internes</label>
        <textarea name="notes" rows="2" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none"><?= e($form['notes']) ?></textarea>
      </div>
    </div>
  </div>

  <div class="mt-6 flex gap-3">
    <a href="/guests/view/<?= (int) $guest['id'] ?>" class="flex-1 text-center py-2.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50">Annuler</a>
    <button type="submit" class="flex-1 py-2.5 rounded-lg bg-gold-400 text-white text-sm font-semibold hover:bg-gold-500 transition shadow-sm">Enregistrer</button>
  </div>

  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
</form>
