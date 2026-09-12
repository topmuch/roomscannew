<?php /** @var array $reservations, $guests, $error, $csrf, $title */ ?>
<div class="mb-6 flex items-center gap-3">
  <a href="/invoices" class="text-gray-400 hover:text-navy-700 text-sm">← Retour</a>
  <h1 class="text-2xl font-bold text-navy-900">Nouvelle facture</h1>
</div>

<?php if ($error): ?>
  <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm"><?= e($error) ?></div>
<?php endif; ?>

<form method="POST" action="/invoices/create" id="invoice-form" class="grid lg:grid-cols-3 gap-6">
  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrf ?>">
  <input type="hidden" name="items_json" id="items_json" value="[]">

  <!-- LEFT — main form -->
  <div class="lg:col-span-2 space-y-6">

    <!-- Reservation + Guest -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-4">Client & Réservation</h2>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Réservation (optionnel)</label>
          <select name="reservation_id" id="reservation_id" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
            <option value="">— Aucune —</option>
            <?php foreach ($reservations as $r): ?>
              <option value="<?= (int) $r['id'] ?>" data-guest-id="<?= (int) $r['guest_id'] ?>">
                <?= e($r['guest_name']) ?> · Ch. <?= e($r['room_number']) ?> · <?= format_date($r['check_in']) ?> → <?= format_date($r['check_out']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Client *</label>
          <select name="guest_id" id="guest_id" required class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
            <option value="">— Sélectionner —</option>
            <?php foreach ($guests as $g): ?>
              <option value="<?= (int) $g['id'] ?>"><?= e($g['name']) ?> · <?= e($g['phone'] ?? $g['email'] ?? '') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <!-- Line items -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-bold text-navy-900">Lignes de la facture</h2>
        <button type="button" id="add-row" class="inline-flex items-center gap-1 px-3 py-1.5 bg-navy-700 text-white text-xs font-semibold rounded-lg hover:bg-navy-800 transition">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Ajouter une ligne
        </button>
      </div>

      <div class="overflow-x-auto -mx-2">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs text-gray-400 uppercase border-b border-gray-100">
              <th class="px-2 py-2 font-semibold">Désignation</th>
              <th class="px-2 py-2 font-semibold w-20">Qté</th>
              <th class="px-2 py-2 font-semibold w-32">Prix unitaire</th>
              <th class="px-2 py-2 font-semibold w-32 text-right">Total ligne</th>
              <th class="px-2 py-2 w-10"></th>
            </tr>
          </thead>
          <tbody id="items-body" class="divide-y divide-gray-50">
            <!-- Rows injected by JS -->
          </tbody>
        </table>
      </div>
      <p id="items-empty" class="text-center text-xs text-gray-400 py-6">Aucune ligne. Cliquez sur « Ajouter une ligne ».</p>
    </div>

    <!-- Payment -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-4">Paiement initial</h2>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Méthode de paiement</label>
          <select name="payment_method" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
            <option value="cash">Espèces</option>
            <option value="card">Carte bancaire</option>
            <option value="mobile_money">Mobile money</option>
            <option value="bank_transfer">Virement</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Montant payé (initial)</label>
          <input type="number" name="initial_payment" id="initial_payment" step="0.01" min="0" value="0"
                 class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
        </div>
      </div>
    </div>
  </div>

  <!-- RIGHT — totals sidebar -->
  <div class="lg:col-span-1">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 sticky top-6">
      <h2 class="text-sm font-bold text-navy-900 mb-4">Totaux</h2>
      <div class="space-y-3 mb-4">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Taux de taxe (%)</label>
          <input type="number" name="tax_rate" id="tax_rate" step="0.01" min="0" max="100" value="0"
                 class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
        </div>
      </div>

      <div class="border-t border-gray-100 pt-4 space-y-2 text-sm">
        <div class="flex items-center justify-between">
          <span class="text-gray-500">Sous-total</span>
          <span class="font-semibold text-gray-800" id="subtotal-display">0 FCFA</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-gray-500">Taxe (<span id="tax-rate-display">0</span>%)</span>
          <span class="font-semibold text-gray-800" id="tax-display">0 FCFA</span>
        </div>
        <div class="flex items-center justify-between border-t border-gray-100 pt-2 mt-2">
          <span class="text-base font-bold text-navy-900">Total</span>
          <span class="text-base font-bold text-gold-500" id="total-display">0 FCFA</span>
        </div>
        <div class="flex items-center justify-between pt-2 mt-2 text-xs text-gray-400">
          <span>Reste à payer</span>
          <span id="balance-display">0 FCFA</span>
        </div>
      </div>

      <button type="submit"
              class="w-full mt-6 py-3 rounded-lg bg-gold-400 text-white font-semibold hover:bg-gold-500 transition shadow-sm">
        Générer la facture
      </button>
    </div>
  </div>
</form>

<script>
(function () {
  const body = document.getElementById('items-body');
  const emptyHint = document.getElementById('items-empty');
  const itemsJson = document.getElementById('items_json');
  const taxRateInput = document.getElementById('tax_rate');
  const taxRateDisplay = document.getElementById('tax-rate-display');
  const initialPaymentInput = document.getElementById('initial_payment');

  function fmt(n) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA';
  }

  function recompute() {
    let subtotal = 0;
    body.querySelectorAll('tr.line-row').forEach(tr => {
      const qty = parseFloat(tr.querySelector('.qty-input').value) || 0;
      const price = parseFloat(tr.querySelector('.price-input').value) || 0;
      const lineTotal = qty * price;
      tr.querySelector('.line-total-display').textContent = fmt(lineTotal);
      subtotal += lineTotal;
    });

    const taxRate = parseFloat(taxRateInput.value) || 0;
    const taxAmount = subtotal * taxRate / 100;
    const total = subtotal + taxAmount;
    const paid = parseFloat(initialPaymentInput.value) || 0;

    document.getElementById('subtotal-display').textContent = fmt(subtotal);
    document.getElementById('tax-display').textContent = fmt(taxAmount);
    document.getElementById('total-display').textContent = fmt(total);
    document.getElementById('balance-display').textContent = fmt(Math.max(0, total - paid));
    taxRateDisplay.textContent = taxRate;

    syncJson();
    emptyHint.style.display = body.querySelectorAll('tr.line-row').length ? 'none' : '';
  }

  function syncJson() {
    const items = [];
    body.querySelectorAll('tr.line-row').forEach(tr => {
      items.push({
        label: tr.querySelector('.label-input').value,
        qty: parseFloat(tr.querySelector('.qty-input').value) || 0,
        price: parseFloat(tr.querySelector('.price-input').value) || 0
      });
    });
    itemsJson.value = JSON.stringify(items);
  }

  function addRow(label = '', qty = 1, price = 0) {
    const tr = document.createElement('tr');
    tr.className = 'line-row';
    tr.innerHTML = `
      <td class="px-2 py-2"><input type="text" class="label-input w-full px-2 py-1.5 rounded border border-gray-300 text-sm focus:ring-1 focus:ring-navy-700 outline-none" placeholder="Ex: Chambre 101 — 2 nuits" value="${label}"></td>
      <td class="px-2 py-2"><input type="number" min="0" step="1" class="qty-input w-full px-2 py-1.5 rounded border border-gray-300 text-sm focus:ring-1 focus:ring-navy-700 outline-none" value="${qty}"></td>
      <td class="px-2 py-2"><input type="number" min="0" step="0.01" class="price-input w-full px-2 py-1.5 rounded border border-gray-300 text-sm focus:ring-1 focus:ring-navy-700 outline-none" value="${price}"></td>
      <td class="px-2 py-2 text-right font-semibold text-navy-900 line-total-display">0 FCFA</td>
      <td class="px-2 py-2 text-center"><button type="button" class="remove-row text-red-400 hover:text-red-600 text-lg leading-none">×</button></td>
    `;
    body.appendChild(tr);
    tr.addEventListener('input', recompute);
    tr.querySelector('.remove-row').addEventListener('click', () => { tr.remove(); recompute(); });
    recompute();
  }

  document.getElementById('add-row').addEventListener('click', () => addRow());
  taxRateInput.addEventListener('input', recompute);
  initialPaymentInput.addEventListener('input', recompute);

  // When reservation is selected, auto-select matching guest
  const reservationSel = document.getElementById('reservation_id');
  const guestSel = document.getElementById('guest_id');
  reservationSel.addEventListener('change', () => {
    const opt = reservationSel.options[reservationSel.selectedIndex];
    const gid = opt.getAttribute('data-guest-id');
    if (gid) guestSel.value = gid;
  });

  document.getElementById('invoice-form').addEventListener('submit', (e) => {
    syncJson();
    if (JSON.parse(itemsJson.value).length === 0) {
      e.preventDefault();
      alert('Ajoutez au moins une ligne à la facture.');
    }
  });

  // Bootstrap with one empty row
  addRow();
})();
</script>
