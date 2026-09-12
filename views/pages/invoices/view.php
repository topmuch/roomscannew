<?php
/** @var array $invoice, $items, $title */
$logo = $invoice['logo'] ?? null;
$statusClass = [
  'paid'      => 'bg-green-100 text-green-700',
  'partial'   => 'bg-amber-100 text-amber-700',
  'unpaid'    => 'bg-red-100 text-red-700',
  'cancelled' => 'bg-gray-100 text-gray-600',
][$invoice['status']] ?? 'bg-gray-100 text-gray-600';
$statusLabel = [
  'paid'      => 'Payée',
  'partial'   => 'Partielle',
  'unpaid'    => 'Impayée',
  'cancelled' => 'Annulée',
][$invoice['status']] ?? $invoice['status'];

$subtotal = (float) $invoice['subtotal'];
$taxRate = (float) $invoice['tax_rate'];
$taxAmount = (float) $invoice['tax_amount'];
$total = (float) $invoice['total'];
$paid = (float) $invoice['paid'];
$balance = max(0, $total - $paid);
?>
<style>
  @media print {
    body * { visibility: hidden !important; }
    .invoice-sheet, .invoice-sheet * { visibility: visible !important; }
    .invoice-sheet { position: absolute; left: 0; top: 0; width: 100%; padding: 0 !important; border: 0 !important; box-shadow: none !important; }
    .no-print { display: none !important; }
    main { padding: 0 !important; max-width: none !important; }
    body { background: white !important; }
  }
</style>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 no-print">
  <div class="flex items-center gap-3">
    <a href="/invoices" class="text-gray-400 hover:text-navy-700 text-sm">← Retour</a>
    <h1 class="text-2xl font-bold text-navy-900">Facture</h1>
    <span class="inline-block px-2.5 py-1 text-xs rounded-full font-medium <?= $statusClass ?>"><?= $statusLabel ?></span>
  </div>
  <div class="flex items-center gap-2">
    <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-navy-700 text-white text-sm font-semibold rounded-lg hover:bg-navy-800 transition">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h14z"/></svg>
      Imprimer
    </button>
    <?php if ($invoice['status'] !== 'paid'): ?>
      <form method="POST" action="/invoices/markPaid/<?= (int) $invoice['id'] ?>" onsubmit="return confirm('Marquer cette facture comme payée ?');">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Marquer payée
        </button>
      </form>
    <?php endif; ?>
    <form method="POST" action="/invoices/delete/<?= (int) $invoice['id'] ?>" onsubmit="return confirm('Supprimer définitivement cette facture ?');" class="inline">
      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
      <button type="submit" class="px-3 py-2 text-red-500 hover:text-red-700 text-sm font-semibold border border-red-200 rounded-lg hover:bg-red-50 transition">Supprimer</button>
    </form>
  </div>
</div>

<!-- Invoice sheet -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 lg:p-12 max-w-3xl mx-auto invoice-sheet">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 pb-6 border-b border-gray-200">
    <div class="flex items-center gap-3">
      <?php if ($logo): ?>
        <img src="<?= e($logo) ?>" alt="" class="w-14 h-14 rounded-lg object-cover">
      <?php else: ?>
        <div class="w-14 h-14 rounded-xl bg-navy-900 flex items-center justify-center">
          <span class="text-2xl font-bold text-white">R</span>
        </div>
      <?php endif; ?>
      <div>
        <div class="font-bold text-navy-900"><?= e($invoice['hotel_name']) ?></div>
        <?php if ($invoice['hotel_email']): ?><div class="text-xs text-gray-500"><?= e($invoice['hotel_email']) ?></div><?php endif; ?>
        <?php if ($invoice['hotel_phone']): ?><div class="text-xs text-gray-500"><?= e($invoice['hotel_phone']) ?></div><?php endif; ?>
        <?php if ($invoice['hotel_address']): ?>
          <div class="text-xs text-gray-500">
            <?= e($invoice['hotel_address']) ?>
            <?php if ($invoice['hotel_city']): ?>, <?= e($invoice['hotel_city']) ?><?php endif; ?>
            <?php if ($invoice['hotel_country']): ?> — <?= e($invoice['hotel_country']) ?><?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="sm:text-right">
      <div class="text-2xl font-bold text-navy-900">FACTURE</div>
      <div class="text-xs text-gray-500 mt-1">N° <span class="font-mono font-semibold text-navy-800"><?= e($invoice['invoice_number']) ?></span></div>
      <div class="text-xs text-gray-500">Date : <?= format_date($invoice['created_at'], 'd/m/Y') ?></div>
      <?php if ($invoice['paid_at']): ?>
        <div class="text-xs text-green-600 font-semibold mt-1">Payée le <?= format_date($invoice['paid_at'], 'd/m/Y') ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Guest info -->
  <div class="mt-6 pb-6 border-b border-gray-200">
    <div class="text-xs uppercase text-gray-400 font-semibold mb-2">Facturé à</div>
    <div class="font-bold text-navy-900"><?= e($invoice['guest_name'] ?? 'Client') ?></div>
    <?php if ($invoice['guest_phone']): ?><div class="text-xs text-gray-600">📞 <?= e($invoice['guest_phone']) ?></div><?php endif; ?>
    <?php if ($invoice['guest_email']): ?><div class="text-xs text-gray-600">✉️ <?= e($invoice['guest_email']) ?></div><?php endif; ?>
    <?php if ($invoice['guest_address']): ?><div class="text-xs text-gray-600">📍 <?= e($invoice['guest_address']) ?></div><?php endif; ?>
  </div>

  <!-- Items table -->
  <div class="mt-6">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-xs text-gray-400 uppercase border-b border-gray-200">
          <th class="py-2 pr-4 font-semibold">Désignation</th>
          <th class="py-2 px-4 font-semibold text-center w-20">Qté</th>
          <th class="py-2 px-4 font-semibold text-right w-32">P.U.</th>
          <th class="py-2 pl-4 font-semibold text-right w-32">Total</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($items)): ?>
          <tr><td colspan="4" class="py-4 text-center text-gray-400 text-xs">Aucune ligne.</td></tr>
        <?php else: ?>
          <?php foreach ($items as $it): ?>
            <tr>
              <td class="py-3 pr-4 text-gray-800"><?= e($it['label'] ?? '') ?></td>
              <td class="py-3 px-4 text-center text-gray-600"><?= (int) ($it['qty'] ?? 0) ?></td>
              <td class="py-3 px-4 text-right text-gray-600"><?= format_price((float) ($it['price'] ?? 0)) ?></td>
              <td class="py-3 pl-4 text-right font-semibold text-navy-900"><?= format_price((float) ($it['qty'] ?? 0) * (float) ($it['price'] ?? 0)) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Totals -->
  <div class="mt-6 flex justify-end">
    <div class="w-full sm:w-72 space-y-2 text-sm">
      <div class="flex items-center justify-between">
        <span class="text-gray-500">Sous-total</span>
        <span class="font-semibold text-gray-800"><?= format_price($subtotal) ?></span>
      </div>
      <div class="flex items-center justify-between">
        <span class="text-gray-500">Taxe (<?= number_format($taxRate, 2) ?>%)</span>
        <span class="font-semibold text-gray-800"><?= format_price($taxAmount) ?></span>
      </div>
      <div class="flex items-center justify-between border-t border-gray-200 pt-2 mt-2">
        <span class="font-bold text-navy-900">Total</span>
        <span class="font-bold text-lg text-gold-500"><?= format_price($total) ?></span>
      </div>
      <div class="flex items-center justify-between pt-2">
        <span class="text-gray-500">Payé</span>
        <span class="font-semibold text-green-600"><?= format_price($paid) ?></span>
      </div>
      <?php if ($balance > 0): ?>
        <div class="flex items-center justify-between border-t border-gray-200 pt-2 mt-2">
          <span class="font-bold text-navy-900">Reste à payer</span>
          <span class="font-bold text-red-600"><?= format_price($balance) ?></span>
        </div>
      <?php endif; ?>
      <?php if ($invoice['payment_method']): ?>
        <div class="flex items-center justify-between text-xs text-gray-400 pt-2">
          <span>Méthode</span>
          <span class="uppercase"><?= e($invoice['payment_method']) ?></span>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Footer note -->
  <div class="mt-10 pt-6 border-t border-gray-200 text-center text-xs text-gray-400">
    Merci de votre confiance. Cette facture est émise par <?= e($invoice['hotel_name']) ?>.
    <br>Pour toute réclamation, contactez-nous au <?= e($invoice['hotel_phone'] ?? '') ?> ou <?= e($invoice['hotel_email'] ?? '') ?>.
  </div>
</div>
