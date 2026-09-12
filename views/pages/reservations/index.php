<?php /** @var array $reservations, $statusCounts, $status, $search, $title */ ?>
<div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
  <div>
    <h1 class="text-2xl font-bold text-navy-900">Réservations</h1>
    <p class="text-sm text-gray-500 mt-1"><?= count($reservations) ?> réservation(s) · <?= date('d/m/Y') ?></p>
  </div>
  <a href="/reservations/create" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gold-400 text-white rounded-lg font-semibold hover:bg-gold-500 transition shadow-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Nouvelle réservation
  </a>
</div>

<!-- Filters -->
<form method="GET" action="/reservations" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 mb-6 flex flex-col sm:flex-row gap-3">
  <div class="flex-1 relative">
    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Rechercher par nom de client…" class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
  </div>
  <select name="status" onchange="this.form.submit()" class="px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
    <option value="">Tous les statuts</option>
    <?php
    $labels = ['pending'=>'En attente','confirmed'=>'Confirmée','checked_in'=>'Arrivé','checked_out'=>'Parti','cancelled'=>'Annulée','no_show'=>'No-show'];
    foreach ($labels as $k => $v): ?>
      <option value="<?= e($k) ?>" <?= $status === $k ? 'selected' : '' ?>><?= e($v) ?> (<?= (int) ($statusCounts[$k] ?? 0) ?>)</option>
    <?php endforeach; ?>
  </select>
  <?php if ($status || $search): ?>
    <a href="/reservations" class="px-4 py-2 text-sm text-gray-500 hover:text-navy-700">Réinitialiser</a>
  <?php endif; ?>
</form>

<!-- Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50/50">
        <tr class="text-left text-xs text-gray-500 uppercase tracking-wide">
          <th class="px-4 py-3">Client</th>
          <th class="px-4 py-3">Chambre</th>
          <th class="px-4 py-3">Arrivée</th>
          <th class="px-4 py-3">Départ</th>
          <th class="px-4 py-3">Statut</th>
          <th class="px-4 py-3 text-right">Total</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        <?php if (empty($reservations)): ?>
          <tr>
            <td colspan="7" class="px-4 py-12 text-center text-gray-400">
              <div class="text-4xl mb-2">📭</div>
              Aucune réservation trouvée.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($reservations as $r):
            $statusClasses = [
              'pending'     => 'bg-amber-100 text-amber-700',
              'confirmed'   => 'bg-blue-100 text-blue-700',
              'checked_in'  => 'bg-green-100 text-green-700',
              'checked_out' => 'bg-gray-100 text-gray-600',
              'cancelled'   => 'bg-red-100 text-red-700',
              'no_show'     => 'bg-red-100 text-red-700',
            ];
            $sc = $statusClasses[$r['status']] ?? 'bg-gray-100 text-gray-600';
            $statusLabel = $labels[$r['status']] ?? $r['status'];
          ?>
            <tr class="hover:bg-gray-50/50">
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <div class="min-w-0">
                    <a href="/reservations/view/<?= (int) $r['id'] ?>" class="font-medium text-navy-900 hover:underline truncate block"><?= e($r['guest_name']) ?></a>
                    <?php if (!empty($r['guest_phone'])): ?>
                      <div class="text-xs text-gray-400"><?= e($r['guest_phone']) ?></div>
                    <?php endif; ?>
                  </div>
                  <?php if (!empty($r['guest_vip'])): ?>
                    <span class="shrink-0 inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[10px] font-bold bg-gold-400/10 text-gold-600 rounded">★ VIP</span>
                  <?php endif; ?>
                </div>
              </td>
              <td class="px-4 py-3 text-gray-700">N° <?= e($r['room_number']) ?> <span class="text-gray-400">· Ét. <?= (int) $r['room_floor'] ?></span></td>
              <td class="px-4 py-3 text-gray-600"><?= format_date($r['check_in']) ?></td>
              <td class="px-4 py-3 text-gray-600"><?= format_date($r['check_out']) ?></td>
              <td class="px-4 py-3">
                <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full <?= $sc ?>"><?= e($statusLabel) ?></span>
              </td>
              <td class="px-4 py-3 text-right font-semibold text-navy-900"><?= format_price((float) $r['total']) ?></td>
              <td class="px-4 py-3 text-right whitespace-nowrap">
                <a href="/reservations/view/<?= (int) $r['id'] ?>" class="text-navy-700 hover:underline text-xs font-medium">Voir</a>
                <span class="text-gray-300">·</span>
                <a href="/reservations/edit/<?= (int) $r['id'] ?>" class="text-gray-600 hover:text-navy-700 text-xs font-medium">Modifier</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
