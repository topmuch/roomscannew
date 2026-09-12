<?php /** @var array $invoices, $search, $title */ ?>
<div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
  <div>
    <h1 class="text-2xl font-bold text-navy-900">Factures</h1>
    <p class="text-sm text-gray-500 mt-1"><?= count($invoices) ?> facture(s) · <?= date('d/m/Y') ?></p>
  </div>
  <a href="/invoices/create"
     class="inline-flex items-center gap-2 px-4 py-2.5 bg-gold-400 text-white text-sm font-semibold rounded-lg hover:bg-gold-500 transition shadow-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Nouvelle facture
  </a>
</div>

<!-- Search -->
<form method="GET" action="/invoices" class="mb-4">
  <div class="relative max-w-md">
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Rechercher par numéro ou client..."
      class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
    <svg class="w-4 h-4 absolute left-3 top-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
  </div>
</form>

<!-- Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-xs text-gray-400 uppercase border-b border-gray-100 bg-gray-50">
          <th class="px-4 py-3 font-semibold">N° Facture</th>
          <th class="px-4 py-3 font-semibold">Client</th>
          <th class="px-4 py-3 font-semibold">Date</th>
          <th class="px-4 py-3 font-semibold text-right">Total</th>
          <th class="px-4 py-3 font-semibold text-right">Payé</th>
          <th class="px-4 py-3 font-semibold">Statut</th>
          <th class="px-4 py-3 font-semibold text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        <?php if (empty($invoices)): ?>
          <tr>
            <td colspan="7" class="px-4 py-12 text-center text-gray-400">
              <div class="text-4xl mb-2">🧾</div>
              Aucune facture pour le moment.
              <a href="/invoices/create" class="block mt-3 text-navy-700 font-semibold hover:underline">Créer la première facture</a>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($invoices as $inv): ?>
            <?php
              $statusClass = [
                'paid'      => 'bg-green-100 text-green-700',
                'partial'   => 'bg-amber-100 text-amber-700',
                'unpaid'    => 'bg-red-100 text-red-700',
                'cancelled' => 'bg-gray-100 text-gray-600',
              ][$inv['status']] ?? 'bg-gray-100 text-gray-600';
              $statusLabel = [
                'paid'      => 'Payée',
                'partial'   => 'Partielle',
                'unpaid'    => 'Impayée',
                'cancelled' => 'Annulée',
              ][$inv['status']] ?? $inv['status'];
            ?>
            <tr class="hover:bg-gray-50 transition">
              <td class="px-4 py-3 font-mono text-xs text-navy-800 font-semibold">
                <a href="/invoices/view/<?= (int) $inv['id'] ?>" class="hover:underline">
                  <?= e($inv['invoice_number']) ?>
                </a>
              </td>
              <td class="px-4 py-3">
                <div class="font-medium text-gray-800"><?= e($inv['guest_name'] ?? '—') ?></div>
                <?php if (!empty($inv['guest_phone'])): ?>
                  <div class="text-xs text-gray-400"><?= e($inv['guest_phone']) ?></div>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3 text-gray-600"><?= format_date($inv['created_at'], 'd/m/Y') ?></td>
              <td class="px-4 py-3 text-right font-semibold text-navy-900"><?= format_price((float) $inv['total']) ?></td>
              <td class="px-4 py-3 text-right text-gray-600"><?= format_price((float) $inv['paid']) ?></td>
              <td class="px-4 py-3">
                <span class="inline-block px-2.5 py-1 text-xs rounded-full font-medium <?= $statusClass ?>">
                  <?= $statusLabel ?>
                </span>
              </td>
              <td class="px-4 py-3 text-right whitespace-nowrap">
                <a href="/invoices/view/<?= (int) $inv['id'] ?>"
                   class="text-navy-700 hover:text-navy-900 text-xs font-semibold mr-3">Voir</a>
                <form method="POST" action="/invoices/delete/<?= (int) $inv['id'] ?>" class="inline"
                      onsubmit="return confirm('Supprimer cette facture ?');">
                  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                  <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-semibold">Supprimer</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
