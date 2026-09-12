<?php
/**
 * @var array  $items
 * @var string $csrf
 */

$categoryLabels = [
  'breakfast' => 'Petit-déjeuner',
  'lunch'     => 'Déjeuner',
  'dinner'    => 'Dîner',
  'drinks'    => 'Boissons',
  'snacks'    => 'Snacks',
];
?>
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
  <div>
    <a href="/roomservice" class="text-sm text-gray-500 hover:text-navy-700">← Commandes</a>
    <h1 class="text-2xl font-bold text-navy-900 mt-2">Menu</h1>
    <p class="text-sm text-gray-500 mt-1">Articles disponibles au room service</p>
  </div>
  <a href="/roomservice/menu/create" class="px-4 py-2 bg-gold-500 text-white text-sm font-semibold rounded-lg hover:bg-gold-600">+ Ajouter un article</a>
</div>

<?php if (empty($items)): ?>
  <div class="bg-white rounded-2xl p-10 shadow-sm border border-gray-100 text-center">
    <p class="text-gray-400 text-sm">Aucun article pour le moment.</p>
    <a href="/roomservice/menu/create" class="mt-3 inline-block text-sm font-semibold text-navy-700 hover:underline">Ajouter le premier article →</a>
  </div>
<?php else: ?>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    <?php foreach ($items as $item): ?>
      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm flex flex-col">
        <div class="aspect-[4/3] bg-gray-100 overflow-hidden">
          <?php if (!empty($item['image'])): ?>
            <img src="<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" class="w-full h-full object-cover">
          <?php else: ?>
            <div class="w-full h-full flex items-center justify-center text-gray-300">
              <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
          <?php endif; ?>
        </div>

        <div class="p-4 flex-1 flex flex-col">
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <div class="text-sm font-bold text-navy-900 truncate"><?= e($item['name']) ?></div>
              <div class="text-[10px] text-gray-400 mt-0.5 uppercase">
                <?= e($categoryLabels[$item['category']] ?? $item['category']) ?> · <?= (int) $item['prep_time'] ?> min
              </div>
            </div>
            <span class="shrink-0 text-sm font-bold text-gold-600 whitespace-nowrap"><?= e(format_price((float) $item['price'])) ?></span>
          </div>

          <?php if (!empty($item['description'])): ?>
            <p class="mt-2 text-xs text-gray-500 line-clamp-2"><?= e($item['description']) ?></p>
          <?php endif; ?>

          <!-- Availability toggle -->
          <form method="POST" class="mt-3 flex items-center gap-2">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
            <input type="hidden" name="available" value="<?= $item['available'] ? '0' : '1' ?>">
            <button type="submit" class="relative inline-flex h-5 w-9 items-center rounded-full transition <?= $item['available'] ? 'bg-green-500' : 'bg-gray-300' ?>" title="<?= $item['available'] ? 'Disponible — cliquer pour désactiver' : 'Indisponible — cliquer pour activer' ?>">
              <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition <?= $item['available'] ? 'translate-x-4' : 'translate-x-0.5' ?>"></span>
            </button>
            <span class="text-xs <?= $item['available'] ? 'text-green-700' : 'text-gray-400' ?>">
              <?= $item['available'] ? 'Disponible' : 'Indisponible' ?>
            </span>
          </form>

          <div class="mt-3 pt-3 border-t border-gray-100 flex gap-2">
            <a href="/roomservice/menu/edit/<?= (int) $item['id'] ?>" class="flex-1 text-center text-xs font-semibold px-2 py-1.5 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200">Modifier</a>
            <form method="POST" action="/roomservice/menu/delete/<?= (int) $item['id'] ?>" onsubmit="return confirm('Supprimer cet article du menu ?');" class="inline">
              <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
              <button type="submit" class="px-3 py-1.5 rounded-md text-xs font-semibold text-red-600 hover:bg-red-50">Supprimer</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
