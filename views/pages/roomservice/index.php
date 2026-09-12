<?php
/**
 * @var array  $orders
 * @var string $statusFilter
 */

$statusLabels = [
  'pending'   => ['En attente',     'bg-amber-100 text-amber-700'],
  'confirmed' => ['Confirmée',       'bg-blue-100 text-blue-700'],
  'preparing' => ['En préparation',  'bg-orange-100 text-orange-700'],
  'ready'     => ['Prête',           'bg-green-100 text-green-700'],
  'delivered' => ['Livrée',          'bg-gray-100 text-gray-600'],
  'cancelled' => ['Annulée',         'bg-red-100 text-red-700'],
];

$statusOptions = ['' => 'Tous les statuts'];
foreach ($statusLabels as $key => $info) {
  $statusOptions[$key] = $info[0];
}
?>
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-navy-900">Room Service</h1>
    <p class="text-sm text-gray-500 mt-1">Commandes en cours et historique</p>
  </div>
  <a href="/roomservice/menu" class="px-4 py-2 bg-navy-900 text-white text-sm font-semibold rounded-lg hover:bg-navy-800">Gérer le menu</a>
</div>

<!-- Filter -->
<form method="GET" class="mb-4 flex flex-wrap items-center gap-2">
  <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-navy-700">
    <?php foreach ($statusOptions as $key => $label): ?>
      <option value="<?= e($key) ?>" <?= $statusFilter === $key ? 'selected' : '' ?>><?= e($label) ?></option>
    <?php endforeach; ?>
  </select>
  <?php if ($statusFilter): ?>
    <a href="/roomservice" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-800">Réinitialiser</a>
  <?php endif; ?>
</form>

<!-- Orders table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-xs text-gray-400 uppercase border-b border-gray-100 bg-gray-50">
          <th class="px-4 py-3">N°</th>
          <th class="px-4 py-3">Chambre</th>
          <th class="px-4 py-3">Articles</th>
          <th class="px-4 py-3">Total</th>
          <th class="px-4 py-3">Statut</th>
          <th class="px-4 py-3">Date</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        <?php if (empty($orders)): ?>
          <tr>
            <td colspan="7" class="px-4 py-10 text-center text-gray-400">Aucune commande pour ce filtre.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($orders as $o):
            $sl = $statusLabels[$o['status']] ?? ['?', 'bg-gray-100 text-gray-600'];
          ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3 font-semibold text-navy-900">#<?= (int) $o['id'] ?></td>
              <td class="px-4 py-3 text-gray-700">N°<?= e($o['room_number']) ?></td>
              <td class="px-4 py-3 text-gray-700"><?= (int) ($o['items_count'] ?? 0) ?></td>
              <td class="px-4 py-3 font-semibold text-navy-900"><?= e(format_price((float) $o['total'])) ?></td>
              <td class="px-4 py-3">
                <span class="inline-block px-2 py-0.5 text-xs rounded-full <?= e($sl[1]) ?>"><?= e($sl[0]) ?></span>
              </td>
              <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap"><?= e(format_date($o['created_at'], 'd/m/Y H:i')) ?></td>
              <td class="px-4 py-3 text-right whitespace-nowrap">
                <a href="/roomservice/view/<?= (int) $o['id'] ?>" class="text-navy-700 font-semibold hover:underline text-xs">Détails →</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
