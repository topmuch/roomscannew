<?php /** @var array $types, $title */ ?>
<div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
  <div>
    <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
      <a href="/rooms" class="hover:text-navy-700">Chambres</a>
      <span>/</span>
      <span>Types</span>
    </div>
    <h1 class="text-2xl font-bold text-navy-900">Types de chambres</h1>
    <p class="text-sm text-gray-500 mt-1"><?= count($types) ?> type(s) défini(s)</p>
  </div>
  <a href="/rooms/typesCreate" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gold-400 text-white rounded-lg font-semibold hover:bg-gold-500 transition shadow-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Nouveau type
  </a>
</div>

<?php if (empty($types)): ?>
  <div class="bg-white rounded-2xl p-12 shadow-sm border border-gray-100 text-center">
    <div class="text-5xl mb-3">🛏️</div>
    <h3 class="text-lg font-semibold text-navy-900">Aucun type de chambre</h3>
    <p class="text-sm text-gray-500 mt-1 mb-4">Les types de chambres facilitent la classification et la tarification.</p>
    <a href="/rooms/typesCreate" class="inline-block px-5 py-2.5 bg-gold-400 text-white rounded-lg text-sm font-semibold hover:bg-gold-500">+ Créer un type</a>
  </div>
<?php else: ?>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($types as $t): ?>
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-start justify-between mb-3">
          <div>
            <h3 class="font-bold text-navy-900 text-lg"><?= e($t['name']) ?></h3>
            <div class="text-xs text-gray-400 mt-0.5"><?= (int) $t['rooms_count'] ?> chambre(s) · <?= (int) $t['capacity'] ?> pers.</div>
          </div>
          <span class="text-lg font-bold text-gold-500"><?= format_price((float) $t['base_price']) ?></span>
        </div>
        <?php if (!empty($t['description'])): ?>
          <p class="text-sm text-gray-600 mb-3"><?= nl2br(e($t['description'])) ?></p>
        <?php endif; ?>
        <?php if (!empty($t['amenities'])): ?>
          <div class="flex flex-wrap gap-1 mb-3">
            <?php foreach (array_filter(array_map('trim', explode(',', $t['amenities']))) as $a): ?>
              <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded"><?= e($a) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <div class="flex gap-2 pt-3 border-t border-gray-50">
          <a href="/rooms/typesEdit/<?= (int) $t['id'] ?>" class="flex-1 text-center py-1.5 rounded-lg bg-navy-700 text-white text-xs font-medium hover:bg-navy-800">Modifier</a>
          <form method="POST" action="/rooms/typesDelete/<?= (int) $t['id'] ?>" onsubmit="return confirm('Supprimer ce type ? Les chambres associées perdront leur type.')">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
            <button type="submit" class="px-3 py-1.5 rounded-lg border border-red-200 text-red-600 text-xs font-medium hover:bg-red-50">Suppr.</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
