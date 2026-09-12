<?php /** @var array $rooms, $floors, $types, $statusCounts, $status, $floor, $typeId, $title */ ?>
<?php
$statusLabels = [
  'available'    => 'Disponible',
  'occupied'     => 'Occupée',
  'cleaning'     => 'En nettoyage',
  'maintenance'  => 'Maintenance',
  'out_of_order' => 'Hors service',
];
$statusBadgeBg = [
  'available'    => 'bg-green-500',
  'occupied'     => 'bg-red-500',
  'cleaning'     => 'bg-amber-500',
  'maintenance'  => 'bg-orange-500',
  'out_of_order' => 'bg-gray-400',
];
?>
<div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
  <div>
    <h1 class="text-2xl font-bold text-navy-900">Chambres</h1>
    <p class="text-sm text-gray-500 mt-1"><?= count($rooms) ?> chambre(s) · <?= (int) ($statusCounts['available'] ?? 0) ?> disponible(s)</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="/rooms/types" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-navy-700 rounded-lg font-medium text-sm hover:bg-gray-50 transition">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
      Types
    </a>
    <a href="/rooms/create" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gold-400 text-white rounded-lg font-semibold hover:bg-gold-500 transition shadow-sm">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      Nouvelle chambre
    </a>
  </div>
</div>

<!-- Filters -->
<form method="GET" action="/rooms" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 mb-6 grid sm:grid-cols-3 gap-3">
  <select name="status" onchange="this.form.submit()" class="px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 outline-none">
    <option value="">Tous les statuts</option>
    <?php foreach ($statusLabels as $k => $v): ?>
      <option value="<?= e($k) ?>" <?= $status === $k ? 'selected' : '' ?>><?= e($v) ?> (<?= (int) ($statusCounts[$k] ?? 0) ?>)</option>
    <?php endforeach; ?>
  </select>
  <select name="floor" onchange="this.form.submit()" class="px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 outline-none">
    <option value="">Tous les étages</option>
    <?php foreach ($floors as $f): ?>
      <option value="<?= (int) $f ?>" <?= $floor === (string) $f ? 'selected' : '' ?>>Étage <?= (int) $f ?></option>
    <?php endforeach; ?>
  </select>
  <select name="type_id" onchange="this.form.submit()" class="px-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 outline-none">
    <option value="">Tous les types</option>
    <?php foreach ($types as $t): ?>
      <option value="<?= (int) $t['id'] ?>" <?= $typeId === (string) $t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
    <?php endforeach; ?>
  </select>
</form>

<!-- Grid -->
<?php if (empty($rooms)): ?>
  <div class="bg-white rounded-2xl p-12 shadow-sm border border-gray-100 text-center">
    <div class="text-5xl mb-3">🚪</div>
    <h3 class="text-lg font-semibold text-navy-900">Aucune chambre enregistrée</h3>
    <p class="text-sm text-gray-500 mt-1">Commencez par créer votre première chambre.</p>
    <a href="/rooms/create" class="mt-4 inline-block px-5 py-2.5 bg-gold-400 text-white rounded-lg text-sm font-semibold hover:bg-gold-500">+ Créer une chambre</a>
  </div>
<?php else: ?>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    <?php foreach ($rooms as $r): ?>
      <a href="/rooms/qrcode/<?= (int) $r['id'] ?>" class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:border-gold-400 transition group">
        <div class="flex items-start justify-between mb-3">
          <div>
            <div class="text-xs text-gray-400 uppercase">Étage <?= (int) $r['floor'] ?></div>
            <div class="text-2xl font-bold text-navy-900">N° <?= e($r['number']) ?></div>
          </div>
          <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full text-white <?= $statusBadgeBg[$r['status']] ?? 'bg-gray-400' ?>">
            <span class="w-1.5 h-1.5 rounded-full bg-white/80"></span>
            <?= e($statusLabels[$r['status']] ?? $r['status']) ?>
          </span>
        </div>
        <div class="text-sm text-gray-600 mb-2"><?= e($r['type_name'] ?? 'Sans type') ?> · <?= (int) ($r['type_capacity'] ?? 2) ?> pers.</div>
        <div class="flex items-center justify-between pt-3 border-t border-gray-50">
          <div class="text-sm font-bold text-gold-500"><?= format_price((float) $r['price']) ?><span class="text-xs text-gray-400 font-normal"> /nuit</span></div>
          <span class="text-gray-300 group-hover:text-navy-700 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6.364 1.636l-.707.707M20 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
          </span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
