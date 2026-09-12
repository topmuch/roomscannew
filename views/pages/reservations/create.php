<?php /** @var array $guests, $rooms, $form, $errors, $sources, $title */ ?>
<div class="mb-6">
  <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
    <a href="/reservations" class="hover:text-navy-700">Réservations</a>
    <span>/</span>
    <span>Nouvelle</span>
  </div>
  <h1 class="text-2xl font-bold text-navy-900">Nouvelle réservation</h1>
</div>

<?php if (!empty($errors)): ?>
  <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
    <div class="text-sm font-medium text-red-800 mb-1">Veuillez corriger les erreurs suivantes :</div>
    <ul class="text-sm text-red-700 list-disc list-inside space-y-0.5">
      <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" action="/reservations/create" class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 space-y-6">

    <!-- Guest section -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-bold text-navy-900 flex items-center gap-2">
          <svg class="w-4 h-4 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H5a2 2 0 01-2-2v-2a3 3 0 015.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          Client
        </h2>
        <label class="inline-flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
          <input type="checkbox" id="new_guest_toggle" name="new_guest" value="1" <?= !empty($form['new_guest']) ? 'checked' : '' ?> onchange="toggleGuest(this.checked)">
          Nouveau client
        </label>
      </div>

      <!-- Existing guest select -->
      <div id="existing_guest_select" class="<?= !empty($form['new_guest']) ? 'hidden' : '' ?>">
        <select name="guest_id" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
          <option value="0">— Sélectionnez un client —</option>
          <?php foreach ($guests as $g): ?>
            <option value="<?= (int) $g['id'] ?>" <?= (int) $form['guest_id'] === (int) $g['id'] ? 'selected' : '' ?>>
              <?= e($g['name']) ?><?= !empty($g['phone']) ? ' · ' . $g['phone'] : '' ?><?= !empty($g['vip']) ? ' ★ VIP' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
        <p class="text-xs text-gray-400 mt-2">
          Client introuvable ? <a href="/guests/create" target="_blank" class="text-navy-700 hover:underline">Créer un client →</a>
        </p>
      </div>

      <!-- New guest fields -->
      <div id="new_guest_fields" class="<?= empty($form['new_guest']) ? 'hidden' : '' ?> grid sm:grid-cols-2 gap-3">
        <div class="sm:col-span-2">
          <label class="block text-xs font-medium text-gray-600 mb-1">Nom complet *</label>
          <input type="text" name="guest_name" value="<?= e($form['guest_name']) ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
          <input type="email" name="guest_email" value="<?= e($form['guest_email']) ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Téléphone</label>
          <input type="text" name="guest_phone" value="<?= e($form['guest_phone']) ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
        </div>
        <div class="sm:col-span-2">
          <label class="block text-xs font-medium text-gray-600 mb-1">Pays</label>
          <input type="text" name="guest_country" value="<?= e($form['guest_country']) ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
        </div>
      </div>
    </div>

    <!-- Stay section -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 flex items-center gap-2 mb-4">
        <svg class="w-4 h-4 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Séjour
      </h2>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Chambre *</label>
          <select name="room_id" id="room_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none" onchange="updatePrice()">
            <option value="0">— Sélectionnez —</option>
            <?php foreach ($rooms as $rm): ?>
              <option value="<?= (int) $rm['id'] ?>" data-price="<?= (float) $rm['price'] ?>" <?= (int) $form['room_id'] === (int) $rm['id'] ? 'selected' : '' ?>>
                N° <?= e($rm['number']) ?> · <?= e($rm['type_name'] ?? 'Sans type') ?> · Ét. <?= (int) $rm['floor'] ?> · <?= format_price((float) $rm['price']) ?>/nuit
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Source</label>
          <select name="source" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
            <?php foreach ($sources as $k => $v): ?>
              <option value="<?= e($k) ?>" <?= $form['source'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Arrivée *</label>
          <input type="date" name="check_in" id="check_in" value="<?= e($form['check_in']) ?>" onchange="updateTotal()" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Départ *</label>
          <input type="date" name="check_out" id="check_out" value="<?= e($form['check_out']) ?>" onchange="updateTotal()" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Adultes</label>
          <input type="number" name="adults" min="1" value="<?= (int) $form['adults'] ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Enfants</label>
          <input type="number" name="children" min="0" value="<?= (int) $form['children'] ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
        </div>
        <div class="sm:col-span-2">
          <label class="block text-xs font-medium text-gray-600 mb-1">Prix par nuit (FCFA)</label>
          <input type="number" name="room_price" id="room_price" step="100" min="0" value="<?= e($form['room_price']) ?>" oninput="updateTotal()" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
        </div>
        <div class="sm:col-span-2">
          <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
          <textarea name="notes" rows="2" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none"><?= e($form['notes']) ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <!-- Summary -->
  <div class="space-y-6">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 sticky top-6">
      <h2 class="text-sm font-bold text-navy-900 mb-4">Récapitulatif</h2>
      <div class="space-y-3 text-sm">
        <div class="flex items-center justify-between">
          <span class="text-gray-500">Prix / nuit</span>
          <span id="sum_price" class="font-medium text-navy-900"><?= format_price((float) $form['room_price']) ?></span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-gray-500">Nuits</span>
          <span id="sum_nights" class="font-medium text-navy-900">1</span>
        </div>
        <div class="border-t border-gray-100 pt-3 flex items-center justify-between">
          <span class="text-gray-700 font-medium">Total</span>
          <span id="sum_total" class="text-lg font-bold text-gold-500"><?= format_price((float) $form['room_price']) ?></span>
        </div>
      </div>
      <button type="submit" class="mt-6 w-full py-3 rounded-lg bg-gold-400 text-white font-semibold hover:bg-gold-500 transition shadow-sm">
        Créer la réservation
      </button>
      <a href="/reservations" class="mt-2 block text-center text-sm text-gray-500 hover:text-navy-700">Annuler</a>
    </div>
  </div>

  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
</form>

<script>
function toggleGuest(isNew) {
  document.getElementById('existing_guest_select').classList.toggle('hidden', isNew);
  document.getElementById('new_guest_fields').classList.toggle('hidden', !isNew);
}
function updatePrice() {
  const sel = document.getElementById('room_id');
  const opt = sel.options[sel.selectedIndex];
  const price = opt ? opt.dataset.price : 0;
  if (price && !document.getElementById('room_price').value) {
    document.getElementById('room_price').value = price;
  }
  updateTotal();
}
function updateTotal() {
  const price = parseFloat(document.getElementById('room_price').value) || 0;
  const ci = document.getElementById('check_in').value;
  const co = document.getElementById('check_out').value;
  let nights = 1;
  if (ci && co) {
    const d1 = new Date(ci), d2 = new Date(co);
    nights = Math.max(1, Math.round((d2 - d1) / 86400000));
  }
  document.getElementById('sum_nights').textContent = nights;
  document.getElementById('sum_price').textContent = price.toLocaleString('fr-FR') + ' FCFA';
  document.getElementById('sum_total').textContent = (price * nights).toLocaleString('fr-FR') + ' FCFA';
}
updateTotal();
</script>
