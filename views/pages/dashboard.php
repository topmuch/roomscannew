<?php /** @var array $stats, $recentReservations, $roomStatuses */ ?>
<div class="mb-6">
  <h1 class="text-2xl font-bold text-navy-900">Tableau de bord</h1>
  <p class="text-sm text-gray-500 mt-1">Vue d'ensemble de votre hôtel — <?= date('d/m/Y') ?></p>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <!-- Occupancy -->
  <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Occupation</span>
      <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-6 0H4m0 0H2m14-7l-3 3-3-3"/></svg>
      </div>
    </div>
    <div class="text-2xl font-bold text-navy-900"><?= $stats['occupied'] ?>/<?= $stats['total_rooms'] ?></div>
    <div class="text-xs text-gray-500 mt-1"><?= $stats['occupancy_rate'] ?>% d'occupation</div>
  </div>

  <!-- Check-ins today -->
  <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Arrivées</span>
      <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      </div>
    </div>
    <div class="text-2xl font-bold text-navy-900"><?= $stats['checkins_today'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Check-in aujourd'hui</div>
  </div>

  <!-- Revenue -->
  <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Revenus</span>
      <div class="w-8 h-8 rounded-lg bg-gold-400/10 flex items-center justify-center">
        <svg class="w-4 h-4 text-gold-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
      </div>
    </div>
    <div class="text-2xl font-bold text-navy-900"><?= format_price($stats['revenue_month']) ?></div>
    <div class="text-xs text-gray-500 mt-1">Ce mois-ci</div>
  </div>

  <!-- Pending orders -->
  <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">En attente</span>
      <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
    </div>
    <div class="text-2xl font-bold text-navy-900"><?= $stats['pending_orders'] + $stats['service_requests'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Commandes + requêtes</div>
  </div>
</div>

<!-- Room Status + Recent Reservations -->
<div class="grid lg:grid-cols-3 gap-6">
  <!-- Room status -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
    <h2 class="text-sm font-bold text-navy-900 mb-4">Statut des chambres</h2>
    <?php if (empty($roomStatuses)): ?>
      <p class="text-sm text-gray-400">Aucune chambre enregistrée.</p>
    <?php else: ?>
      <div class="space-y-2">
        <?php foreach ($roomStatuses as $rs): ?>
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-<?= room_status_color($rs['status']) ?>-500"></span>
              <span class="text-sm text-gray-700"><?= room_status_label($rs['status']) ?></span>
            </div>
            <span class="text-sm font-bold text-navy-900"><?= $rs['count'] ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Recent reservations -->
  <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-sm font-bold text-navy-900">Réservations récentes</h2>
      <a href="/reservations" class="text-xs text-navy-700 font-semibold hover:underline">Voir tout →</a>
    </div>
    <?php if (empty($recentReservations)): ?>
      <p class="text-sm text-gray-400 py-8 text-center">Aucune réservation pour le moment.</p>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs text-gray-400 uppercase border-b border-gray-100">
              <th class="pb-2">Client</th>
              <th class="pb-2">Chambre</th>
              <th class="pb-2">Arrivée</th>
              <th class="pb-2">Départ</th>
              <th class="pb-2">Statut</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <?php foreach ($recentReservations as $r): ?>
              <tr>
                <td class="py-2.5 font-medium text-gray-800"><?= e($r['guest_name']) ?></td>
                <td class="py-2.5 text-gray-600">N°<?= e($r['room_number']) ?></td>
                <td class="py-2.5 text-gray-600"><?= format_date($r['check_in']) ?></td>
                <td class="py-2.5 text-gray-600"><?= format_date($r['check_out']) ?></td>
                <td class="py-2.5">
                  <span class="inline-block px-2 py-0.5 text-xs rounded-full
                    <?= $r['status'] === 'checked_in' ? 'bg-green-100 text-green-700' : '' ?>
                    <?= $r['status'] === 'confirmed' ? 'bg-blue-100 text-blue-700' : '' ?>
                    <?= $r['status'] === 'checked_out' ? 'bg-gray-100 text-gray-600' : '' ?>
                    <?= $r['status'] === 'cancelled' ? 'bg-red-100 text-red-700' : '' ?>
                  "><?= e($r['status']) ?></span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
