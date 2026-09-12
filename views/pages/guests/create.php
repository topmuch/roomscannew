<?php /** @var array $form, $errors, $idTypes, $countries, $title */ ?>
<div class="mb-6">
  <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
    <a href="/guests" class="hover:text-navy-700">Clients</a>
    <span>/</span>
    <span>Nouveau</span>
  </div>
  <h1 class="text-2xl font-bold text-navy-900">Nouveau client</h1>
</div>

<?php if (!empty($errors)): ?>
  <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
    <ul class="text-sm text-red-700 list-disc list-inside space-y-0.5">
      <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" action="/guests/create" class="max-w-3xl">
  <div class="grid sm:grid-cols-2 gap-6">
    <!-- Identity -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 flex items-center gap-2 mb-4">
        <svg class="w-4 h-4 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        Identité
      </h2>
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

    <!-- ID & VIP -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 flex items-center gap-2 mb-4">
        <svg class="w-4 h-4 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Pièce d'identité & VIP
      </h2>
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

  <!-- Preferences & notes -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mt-6">
    <h2 class="text-sm font-bold text-navy-900 mb-4">Préférences & notes</h2>
    <div class="space-y-3">
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Préférences (allergies, étage, lit…)</label>
        <textarea name="preferences" rows="3" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none"><?= e($form['preferences']) ?></textarea>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Notes internes</label>
        <textarea name="notes" rows="2" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none"><?= e($form['notes']) ?></textarea>
      </div>
    </div>
  </div>

  <div class="mt-6 flex gap-3">
    <a href="/guests" class="flex-1 text-center py-2.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50">Annuler</a>
    <button type="submit" class="flex-1 py-2.5 rounded-lg bg-gold-400 text-white text-sm font-semibold hover:bg-gold-500 transition shadow-sm">Créer le client</button>
  </div>

  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
</form>
