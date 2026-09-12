<?php
/**
 * @var array  $items
 * @var string $typeFilter
 * @var string $statusFilter
 * @var string $search
 */

$typeLabels = [
  'lost'  => ['Perdu',   'bg-red-100 text-red-700'],
  'found' => ['Trouvé',  'bg-green-100 text-green-700'],
];

$statusLabels = [
  'open'      => ['Ouvert',   'bg-amber-100 text-amber-700'],
  'claimed'   => ['Réclamé',  'bg-blue-100 text-blue-700'],
  'returned'  => ['Rendu',    'bg-green-100 text-green-700'],
  'disposed'  => ['Éliminé',  'bg-gray-100 text-gray-600'],
];
?>
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-navy-900">Objets trouvés</h1>
    <p class="text-sm text-gray-500 mt-1">Gestion des objets perdus et trouvés</p>
  </div>
  <a href="/lostfound/create" class="px-4 py-2 bg-gold-500 text-white text-sm font-semibold rounded-lg hover:bg-gold-600">+ Signaler un objet</a>
</div>

<!-- Filters -->
<form method="GET" class="mb-4 bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex flex-wrap items-center gap-3">
  <input type="text" name="q" value="<?= e($search) ?>" placeholder="Rechercher par nom d'objet..." class="flex-1 min-w-[200px] px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent">
  <select name="type" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-navy-700">
    <option value="">Tous types</option>
    <option value="lost"  <?= $typeFilter === 'lost'  ? 'selected' : '' ?>>Perdus</option>
    <option value="found" <?= $typeFilter === 'found' ? 'selected' : '' ?>>Trouvés</option>
  </select>
  <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-navy-700">
    <option value="">Tous statuts</option>
    <?php foreach ($statusLabels as $key => $info): ?>
      <option value="<?= e($key) ?>" <?= $statusFilter === $key ? 'selected' : '' ?>><?= e($info[0]) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="px-4 py-2 bg-navy-900 text-white text-sm font-semibold rounded-lg hover:bg-navy-800">Filtrer</button>
  <?php if ($typeFilter || $statusFilter || $search): ?>
    <a href="/lostfound" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-800">Réinitialiser</a>
  <?php endif; ?>
</form>

<!-- Items table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-xs text-gray-400 uppercase border-b border-gray-100 bg-gray-50">
          <th class="px-4 py-3">Type</th>
          <th class="px-4 py-3">Objet</th>
          <th class="px-4 py-3">Chambre</th>
          <th class="px-4 py-3">Lieu</th>
          <th class="px-4 py-3">Statut</th>
          <th class="px-4 py-3">Signalé par</th>
          <th class="px-4 py-3">Date</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        <?php if (empty($items)): ?>
          <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">Aucun objet enregistré pour ces critères.</td></tr>
        <?php else: ?>
          <?php foreach ($items as $item):
            $tl = $typeLabels[$item['type']]   ?? ['?', 'bg-gray-100 text-gray-600'];
            $sl = $statusLabels[$item['status']] ?? ['?', 'bg-gray-100 text-gray-600'];
          ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <span class="inline-block px-2 py-0.5 text-xs rounded-full <?= e($tl[1]) ?>"><?= e($tl[0]) ?></span>
              </td>
              <td class="px-4 py-3 font-medium text-navy-900">
                <div class="flex items-center gap-2">
                  <?php if (!empty($item['photo'])): ?>
                    <img src="<?= e($item['photo']) ?>" alt="" class="w-8 h-8 rounded-md object-cover">
                  <?php endif; ?>
                  <span class="truncate max-w-[200px]"><?= e($item['item_name']) ?></span>
                </div>
              </td>
              <td class="px-4 py-3 text-gray-700"><?= e($item['room_number'] ?? '—') ?></td>
              <td class="px-4 py-3 text-gray-700"><?= e($item['location'] ?: '—') ?></td>
              <td class="px-4 py-3">
                <span class="inline-block px-2 py-0.5 text-xs rounded-full <?= e($sl[1]) ?>"><?= e($sl[0]) ?></span>
              </td>
              <td class="px-4 py-3 text-gray-700"><?= e($item['reported_by'] ?: '—') ?></td>
              <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap"><?= e(format_date($item['created_at'], 'd/m/Y')) ?></td>
              <td class="px-4 py-3 text-right whitespace-nowrap">
                <a href="/lostfound/view/<?= (int) $item['id'] ?>" class="text-navy-700 font-semibold hover:underline text-xs">Voir →</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
