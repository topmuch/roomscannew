<?php
/**
 * @var array  $order
 * @var string $csrf
 */

$statusLabels = [
  'pending'   => ['En attente',     'bg-amber-100 text-amber-700'],
  'confirmed' => ['Confirmée',       'bg-blue-100 text-blue-700'],
  'preparing' => ['En préparation',  'bg-orange-100 text-orange-700'],
  'ready'     => ['Prête',           'bg-green-100 text-green-700'],
  'delivered' => ['Livrée',          'bg-gray-100 text-gray-600'],
  'cancelled' => ['Annulée',         'bg-red-100 text-red-700'],
];

$sl = $statusLabels[$order['status']] ?? ['?', 'bg-gray-100 text-gray-600'];

// Status flow: pending → confirmed → preparing → ready → delivered
$nextActions = [
  'pending'   => [['confirmed', 'Confirmer'], ['cancelled', 'Annuler']],
  'confirmed' => [['preparing', 'Commencer la préparation'], ['cancelled', 'Annuler']],
  'preparing' => [['ready', 'Marquer comme prête']],
  'ready'     => [['delivered', 'Marquer comme livrée']],
];
$actions = $nextActions[$order['status']] ?? [];
?>
<div class="mb-6">
  <a href="/roomservice" class="text-sm text-gray-500 hover:text-navy-700">← Retour aux commandes</a>
  <div class="mt-2 flex flex-wrap items-center gap-3">
    <h1 class="text-2xl font-bold text-navy-900">Commande #<?= (int) $order['id'] ?></h1>
    <span class="inline-block px-2.5 py-0.5 text-xs rounded-full <?= e($sl[1]) ?>"><?= e($sl[0]) ?></span>
  </div>
  <p class="text-sm text-gray-500 mt-1"><?= e(format_date($order['created_at'], 'd/m/Y à H:i')) ?></p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
  <!-- Items -->
  <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
    <h2 class="text-sm font-bold text-navy-900 mb-4">Articles commandés</h2>
    <div class="divide-y divide-gray-50">
      <?php foreach ($order['items_parsed'] as $item): ?>
        <div class="py-3 flex items-center justify-between gap-4">
          <div class="min-w-0">
            <div class="text-sm font-medium text-gray-800"><?= e($item['name']) ?></div>
            <div class="text-xs text-gray-400">
              <?= e($item['category']) ?> · <?= (int) $item['qty'] ?> × <?= e(format_price($item['price'])) ?>
            </div>
          </div>
          <div class="text-sm font-semibold text-navy-900 whitespace-nowrap"><?= e(format_price($item['subtotal'])) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
      <span class="text-sm font-bold text-navy-900">Total</span>
      <span class="text-lg font-bold text-navy-900"><?= e(format_price((float) $order['total'])) ?></span>
    </div>

    <?php if (!empty($order['notes'])): ?>
      <div class="mt-4 p-3 bg-amber-50 rounded-lg">
        <div class="text-xs font-semibold text-amber-700 mb-1">Note du client</div>
        <div class="text-sm text-amber-800 whitespace-pre-line"><?= e($order['notes']) ?></div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Sidebar -->
  <div class="space-y-4">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-3">Informations</h2>
      <dl class="space-y-2 text-sm">
        <div class="flex justify-between gap-2">
          <dt class="text-gray-500">Chambre</dt>
          <dd class="font-semibold text-gray-800 text-right">
            N°<?= e($order['room_number']) ?>
            <?php if (!empty($order['room_floor'])): ?> · Ét. <?= (int) $order['room_floor'] ?><?php endif; ?>
          </dd>
        </div>
        <?php if (!empty($order['guest_name'])): ?>
          <div class="flex justify-between gap-2"><dt class="text-gray-500">Client</dt><dd class="font-medium text-gray-800 text-right"><?= e($order['guest_name']) ?></dd></div>
        <?php endif; ?>
        <?php if (!empty($order['guest_phone'])): ?>
          <div class="flex justify-between gap-2"><dt class="text-gray-500">Téléphone</dt><dd class="font-medium text-gray-800 text-right"><?= e($order['guest_phone']) ?></dd></div>
        <?php endif; ?>
      </dl>
    </div>

    <!-- Status actions -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-3">Changer le statut</h2>
      <?php if (empty($actions)): ?>
        <p class="text-xs text-gray-400">Aucune action disponible pour ce statut.</p>
      <?php else: ?>
        <div class="space-y-2">
          <?php foreach ($actions as [$status, $label]):
            $isCancel = $status === 'cancelled';
          ?>
            <form method="POST" action="/roomservice/update/<?= (int) $order['id'] ?>">
              <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
              <input type="hidden" name="status" value="<?= e($status) ?>">
              <button type="submit" class="w-full px-4 py-2 text-sm font-semibold rounded-lg
                <?= $isCancel ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-navy-900 text-white hover:bg-navy-800' ?>">
                <?= e($label) ?>
              </button>
            </form>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="mt-4 pt-4 border-t border-gray-100">
        <a href="/roomservice/delete/<?= (int) $order['id'] ?>" class="block text-center text-xs text-red-600 hover:text-red-700 font-medium">Supprimer la commande</a>
      </div>
    </div>
  </div>
</div>
