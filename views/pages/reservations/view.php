<?php /** @var array $reservation, $charges, $extrasTotal, $nights, $roomTotal, $grandTotal, $title */ ?>
<?php
$statusClasses = [
  'pending'     => 'bg-amber-100 text-amber-700',
  'confirmed'   => 'bg-blue-100 text-blue-700',
  'checked_in'  => 'bg-green-100 text-green-700',
  'checked_out' => 'bg-gray-100 text-gray-600',
  'cancelled'   => 'bg-red-100 text-red-700',
  'no_show'     => 'bg-red-100 text-red-700',
];
$statusLabels = [
  'pending'     => 'En attente',
  'confirmed'   => 'Confirmée',
  'checked_in'  => 'Arrivé',
  'checked_out' => 'Parti',
  'cancelled'   => 'Annulée',
  'no_show'     => 'No-show',
];
$sc = $statusClasses[$reservation['status']] ?? 'bg-gray-100 text-gray-600';
?>
<div class="mb-6">
  <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
    <a href="/reservations" class="hover:text-navy-700">Réservations</a>
    <span>/</span>
    <span>#<?= (int) $reservation['id'] ?></span>
  </div>
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold text-navy-900">Réservation #<?= (int) $reservation['id'] ?></h1>
      <p class="text-sm text-gray-500 mt-1">
        Créée le <?= format_date($reservation['created_at'], 'd/m/Y à H:i') ?> ·
        Source : <?= e(ucfirst($reservation['source'])) ?>
      </p>
    </div>
    <span class="inline-block self-start px-3 py-1 text-xs font-semibold rounded-full <?= $sc ?>"><?= e($statusLabels[$reservation['status']] ?? $reservation['status']) ?></span>
  </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 space-y-6">

    <!-- Guest + Room info -->
    <div class="grid sm:grid-cols-2 gap-6">
      <!-- Guest -->
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <h2 class="text-xs font-bold text-gray-400 uppercase mb-3">Client</h2>
        <div class="flex items-center gap-3 mb-3">
          <div class="w-10 h-10 rounded-full bg-navy-700 text-white flex items-center justify-center font-bold text-sm shrink-0">
            <?= strtoupper(substr($reservation['guest_name'], 0, 1)) ?>
          </div>
          <div class="min-w-0">
            <div class="font-semibold text-navy-900 flex items-center gap-1">
              <?= e($reservation['guest_name']) ?>
              <?php if (!empty($reservation['guest_vip'])): ?>
                <span class="text-[10px] font-bold bg-gold-400/10 text-gold-600 px-1.5 py-0.5 rounded">VIP</span>
              <?php endif; ?>
            </div>
            <div class="text-xs text-gray-400 truncate"><?= e($reservation['guest_email'] ?: '—') ?></div>
          </div>
        </div>
        <div class="space-y-1.5 text-sm">
          <div class="flex justify-between"><span class="text-gray-500">Téléphone</span><span class="font-medium text-gray-700"><?= e($reservation['guest_phone'] ?: '—') ?></span></div>
          <div class="flex justify-between"><span class="text-gray-500">Pays</span><span class="font-medium text-gray-700"><?= e($reservation['guest_country'] ?: '—') ?></span></div>
          <?php if (!empty($reservation['guest_id_type'])): ?>
            <div class="flex justify-between"><span class="text-gray-500"><?= e(strtoupper($reservation['guest_id_type'])) ?></span><span class="font-medium text-gray-700"><?= e($reservation['guest_id_number']) ?></span></div>
          <?php endif; ?>
        </div>
        <a href="/guests/view/<?= (int) $reservation['guest_id'] ?>" class="mt-3 inline-block text-xs text-navy-700 hover:underline font-medium">Fiche client →</a>
      </div>

      <!-- Room -->
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <h2 class="text-xs font-bold text-gray-400 uppercase mb-3">Chambre</h2>
        <div class="flex items-center gap-3 mb-3">
          <div class="w-10 h-10 rounded-lg bg-gold-400/10 text-gold-600 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3"/></svg>
          </div>
          <div>
            <div class="font-semibold text-navy-900">N° <?= e($reservation['room_number']) ?></div>
            <div class="text-xs text-gray-400"><?= e($reservation['room_type_name'] ?? 'Sans type') ?> · Étage <?= (int) $reservation['room_floor'] ?></div>
          </div>
        </div>
        <div class="space-y-1.5 text-sm">
          <div class="flex justify-between"><span class="text-gray-500">Capacité</span><span class="font-medium text-gray-700"><?= (int) $reservation['room_capacity'] ?> pers.</span></div>
          <div class="flex justify-between"><span class="text-gray-500">Statut chambre</span>
            <span class="inline-block px-2 py-0.5 text-xs rounded-full bg-<?= room_status_color($reservation['room_status']) ?>-100 text-<?= room_status_color($reservation['room_status']) ?>-700"><?= room_status_label($reservation['room_status']) ?></span>
          </div>
          <?php if (!empty($reservation['room_amenities'])): ?>
            <div class="pt-2 text-xs text-gray-500"><?= e($reservation['room_amenities']) ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Stay details -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-4">Détails du séjour</h2>
      <div class="grid sm:grid-cols-4 gap-4 text-sm">
        <div>
          <div class="text-xs text-gray-400 uppercase mb-1">Arrivée</div>
          <div class="font-medium text-navy-900"><?= format_date($reservation['check_in']) ?></div>
        </div>
        <div>
          <div class="text-xs text-gray-400 uppercase mb-1">Départ</div>
          <div class="font-medium text-navy-900"><?= format_date($reservation['check_out']) ?></div>
        </div>
        <div>
          <div class="text-xs text-gray-400 uppercase mb-1">Nuits</div>
          <div class="font-medium text-navy-900"><?= $nights ?></div>
        </div>
        <div>
          <div class="text-xs text-gray-400 uppercase mb-1">Occupants</div>
          <div class="font-medium text-navy-900"><?= (int) $reservation['adults'] ?> adulte(s)<?= $reservation['children'] ? ', ' . (int) $reservation['children'] . ' enfant(s)' : '' ?></div>
        </div>
      </div>
      <?php if (!empty($reservation['notes'])): ?>
        <div class="mt-4 p-3 bg-gray-50 rounded-lg text-sm text-gray-600">
          <div class="text-xs font-medium text-gray-400 uppercase mb-1">Notes</div>
          <?= nl2br(e($reservation['notes'])) ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Charges -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="p-6 pb-3">
        <h2 class="text-sm font-bold text-navy-900">Charges & extras</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50/50 text-xs text-gray-500 uppercase">
            <tr>
              <th class="px-6 py-2 text-left">Article</th>
              <th class="px-6 py-2 text-left">Date</th>
              <th class="px-6 py-2 text-left">Statut</th>
              <th class="px-6 py-2 text-right">Montant</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr>
              <td class="px-6 py-3">
                <div class="font-medium text-gray-800">Hébergement (<?= $nights ?> nuit(s) × <?= format_price((float) $reservation['room_price']) ?>)</div>
                <div class="text-xs text-gray-400">Chambre N° <?= e($reservation['room_number']) ?></div>
              </td>
              <td class="px-6 py-3 text-gray-600"><?= format_date($reservation['check_in']) ?></td>
              <td class="px-6 py-3"><span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded">Forfait</span></td>
              <td class="px-6 py-3 text-right font-medium text-navy-900"><?= format_price($roomTotal) ?></td>
            </tr>
            <?php foreach ($charges as $c): ?>
              <tr>
                <td class="px-6 py-3">
                  <div class="font-medium text-gray-800">Room Service</div>
                  <div class="text-xs text-gray-400"><?= e(mb_substr($c['items'] ?? '', 0, 60)) ?><?= !empty($c['notes']) ? ' · ' . e($c['notes']) : '' ?></div>
                </td>
                <td class="px-6 py-3 text-gray-600"><?= format_date($c['created_at'], 'd/m/Y H:i') ?></td>
                <td class="px-6 py-3"><span class="text-xs px-2 py-0.5 bg-amber-100 text-amber-700 rounded"><?= e($c['status']) ?></span></td>
                <td class="px-6 py-3 text-right font-medium text-navy-900"><?= format_price((float) $c['total']) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($charges)): ?>
              <tr>
                <td colspan="4" class="px-6 py-6 text-center text-gray-400 text-sm">Aucune charge room service sur cette réservation.</td>
              </tr>
            <?php endif; ?>
          </tbody>
          <tfoot>
            <tr class="border-t-2 border-gray-100 bg-gray-50/50">
              <td colspan="3" class="px-6 py-3 text-right font-semibold text-gray-700">Total</td>
              <td class="px-6 py-3 text-right text-lg font-bold text-gold-500"><?= format_price($grandTotal) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="space-y-4">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-4">Actions</h2>
      <div class="space-y-2">
        <a href="/reservations/edit/<?= (int) $reservation['id'] ?>" class="block w-full text-center py-2.5 rounded-lg bg-navy-700 text-white text-sm font-medium hover:bg-navy-800 transition">
          ✏️ Modifier
        </a>

        <?php if (in_array($reservation['status'], ['pending', 'confirmed'], true)): ?>
          <form method="POST" action="/reservations/checkin/<?= (int) $reservation['id'] ?>">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
            <button type="submit" class="w-full py-2.5 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700 transition">
              ✅ Check-in
            </button>
          </form>
        <?php endif; ?>

        <?php if ($reservation['status'] === 'checked_in'): ?>
          <form method="POST" action="/reservations/checkout/<?= (int) $reservation['id'] ?>">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
            <button type="submit" class="w-full py-2.5 rounded-lg bg-amber-500 text-white text-sm font-medium hover:bg-amber-600 transition" onclick="return confirm('Confirmer le départ ? La chambre passera en nettoyage.')">
              🚪 Check-out
            </button>
          </form>
        <?php endif; ?>

        <a href="/reservations/delete/<?= (int) $reservation['id'] ?>" class="block w-full text-center py-2.5 rounded-lg border border-red-200 text-red-600 text-sm font-medium hover:bg-red-50 transition">
          🗑️ Supprimer
        </a>
      </div>
    </div>

    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-3">Paiement</h2>
      <div class="space-y-2 text-sm">
        <div class="flex justify-between"><span class="text-gray-500">Statut</span><span class="font-medium"><?= e($reservation['payment_status']) ?></span></div>
        <div class="flex justify-between"><span class="text-gray-500">Payé</span><span class="font-medium text-navy-900"><?= format_price((float) $reservation['paid']) ?></span></div>
        <div class="flex justify-between"><span class="text-gray-500">Reste dû</span><span class="font-medium text-red-600"><?= format_price(max(0, $grandTotal - (float) $reservation['paid'])) ?></span></div>
      </div>
    </div>
  </div>
</div>
