<?php /** @var array $guest, $reservations, $totalSpent, $totalStays, $idTypes, $title */ ?>
<div class="mb-6">
  <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
    <a href="/guests" class="hover:text-navy-700">Clients</a>
    <span>/</span>
    <span><?= e($guest['name']) ?></span>
  </div>
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div class="flex items-center gap-4">
      <div class="w-14 h-14 rounded-full bg-navy-700 text-white flex items-center justify-center text-xl font-bold shrink-0">
        <?= strtoupper(substr($guest['name'], 0, 1)) ?>
      </div>
      <div>
        <h1 class="text-2xl font-bold text-navy-900 flex items-center gap-2">
          <?= e($guest['name']) ?>
          <?php if (!empty($guest['vip'])): ?>
            <span class="text-xs font-bold bg-gold-400/10 text-gold-600 px-2 py-0.5 rounded">★ VIP</span>
          <?php endif; ?>
        </h1>
        <p class="text-sm text-gray-500">Client depuis <?= format_date($guest['created_at']) ?></p>
      </div>
    </div>
    <div class="flex gap-2">
      <a href="/guests/edit/<?= (int) $guest['id'] ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-navy-700 text-white text-sm font-medium hover:bg-navy-800">✏️ Modifier</a>
      <a href="/reservations/create?guest_id=<?= (int) $guest['id'] ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gold-400 text-white text-sm font-semibold hover:bg-gold-500">+ Réservation</a>
    </div>
  </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
  <div class="space-y-6">
    <!-- Contact -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-4">Coordonnées</h2>
      <div class="space-y-3 text-sm">
        <div class="flex items-start gap-3">
          <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          <div class="min-w-0">
            <div class="text-xs text-gray-400">Email</div>
            <div class="font-medium text-gray-800 break-all"><?= e($guest['email'] ?: '—') ?></div>
          </div>
        </div>
        <div class="flex items-start gap-3">
          <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
          <div>
            <div class="text-xs text-gray-400">Téléphone</div>
            <div class="font-medium text-gray-800"><?= e($guest['phone'] ?: '—') ?></div>
          </div>
        </div>
        <div class="flex items-start gap-3">
          <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <div>
            <div class="text-xs text-gray-400">Pays</div>
            <div class="font-medium text-gray-800"><?= e($guest['country'] ?: '—') ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- ID -->
    <?php if (!empty($guest['id_type']) || !empty($guest['id_number'])): ?>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-4">Pièce d'identité</h2>
      <div class="space-y-2 text-sm">
        <div class="flex justify-between"><span class="text-gray-500">Type</span><span class="font-medium"><?= e($idTypes[$guest['id_type']] ?? strtoupper($guest['id_type'] ?: '—')) ?></span></div>
        <div class="flex justify-between"><span class="text-gray-500">Numéro</span><span class="font-medium"><?= e($guest['id_number'] ?: '—') ?></span></div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-4">Statistiques</h2>
      <div class="space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-sm text-gray-500">Total séjours</span>
          <span class="text-xl font-bold text-navy-900"><?= $totalStays ?></span>
        </div>
        <div class="flex items-center justify-between pt-3 border-t border-gray-100">
          <span class="text-sm text-gray-500">Total dépensé</span>
          <span class="text-xl font-bold text-gold-500"><?= format_price($totalSpent) ?></span>
        </div>
      </div>
    </div>

    <?php if (!empty($guest['preferences']) || !empty($guest['notes'])): ?>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-3">
      <?php if (!empty($guest['preferences'])): ?>
      <div>
        <h3 class="text-xs font-bold text-gray-400 uppercase mb-1">Préférences</h3>
        <p class="text-sm text-gray-700 whitespace-pre-line"><?= e($guest['preferences']) ?></p>
      </div>
      <?php endif; ?>
      <?php if (!empty($guest['notes'])): ?>
      <div>
        <h3 class="text-xs font-bold text-gray-400 uppercase mb-1">Notes internes</h3>
        <p class="text-sm text-gray-700 whitespace-pre-line"><?= e($guest['notes']) ?></p>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <a href="/guests/delete/<?= (int) $guest['id'] ?>" class="block text-center py-2.5 rounded-lg border border-red-200 text-red-600 text-sm font-medium hover:bg-red-50 transition">🗑️ Supprimer le client</a>
  </div>

  <!-- Reservation history -->
  <div class="lg:col-span-2">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="p-6 pb-3 flex items-center justify-between">
        <h2 class="text-sm font-bold text-navy-900">Historique des réservations</h2>
        <span class="text-xs text-gray-400"><?= count($reservations) ?> réservation(s)</span>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50/50 text-xs text-gray-500 uppercase">
            <tr>
              <th class="px-6 py-2 text-left">#</th>
              <th class="px-6 py-2 text-left">Chambre</th>
              <th class="px-6 py-2 text-left">Arrivée</th>
              <th class="px-6 py-2 text-left">Départ</th>
              <th class="px-6 py-2 text-left">Statut</th>
              <th class="px-6 py-2 text-right">Total</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <?php if (empty($reservations)): ?>
              <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">Aucune réservation.</td></tr>
            <?php else: ?>
              <?php
              $statusLabels = [
                'pending'     => 'En attente','confirmed'   => 'Confirmée','checked_in'  => 'Arrivé',
                'checked_out' => 'Parti','cancelled'   => 'Annulée','no_show'     => 'No-show',
              ];
              $statusColors = [
                'pending'     => 'bg-amber-100 text-amber-700','confirmed'   => 'bg-blue-100 text-blue-700',
                'checked_in'  => 'bg-green-100 text-green-700','checked_out' => 'bg-gray-100 text-gray-600',
                'cancelled'   => 'bg-red-100 text-red-700','no_show'     => 'bg-red-100 text-red-700',
              ];
              foreach ($reservations as $r): ?>
                <tr class="hover:bg-gray-50/50">
                  <td class="px-6 py-3">
                    <a href="/reservations/view/<?= (int) $r['id'] ?>" class="font-medium text-navy-700 hover:underline">#<?= (int) $r['id'] ?></a>
                  </td>
                  <td class="px-6 py-3 text-gray-700">N° <?= e($r['room_number']) ?></td>
                  <td class="px-6 py-3 text-gray-600"><?= format_date($r['check_in']) ?></td>
                  <td class="px-6 py-3 text-gray-600"><?= format_date($r['check_out']) ?></td>
                  <td class="px-6 py-3">
                    <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full <?= $statusColors[$r['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                      <?= e($statusLabels[$r['status']] ?? $r['status']) ?>
                    </span>
                  </td>
                  <td class="px-6 py-3 text-right font-semibold text-navy-900"><?= format_price((float) $r['total']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
