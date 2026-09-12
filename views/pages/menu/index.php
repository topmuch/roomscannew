<?php
/**
 * @var array $items, $categories, $categoryFilter, $title
 */
$categoryColors = [
  'breakfast' => 'bg-orange-100 text-orange-700',
  'lunch'     => 'bg-blue-100 text-blue-700',
  'dinner'    => 'bg-purple-100 text-purple-700',
  'drinks'    => 'bg-green-100 text-green-700',
  'snacks'    => 'bg-amber-100 text-amber-700',
];
?>
<div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
  <div>
    <h1 class="text-2xl font-bold text-navy-900">Menu Room Service</h1>
    <p class="text-sm text-gray-500 mt-1"><?= count($items) ?> article(s) · visibles par vos clients via QR code</p>
  </div>
  <a href="/menu/create"
     class="inline-flex items-center gap-2 px-4 py-2.5 bg-gold-400 text-white text-sm font-semibold rounded-lg hover:bg-gold-500 transition shadow-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Nouvel article
  </a>
</div>

<!-- Category filter -->
<div class="flex flex-wrap gap-2 mb-6">
  <a href="/menu"
     class="px-3 py-1.5 text-xs font-semibold rounded-full transition <?= $categoryFilter === '' ? 'bg-navy-700 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:border-navy-700 hover:text-navy-700' ?>">
    Tous
  </a>
  <?php foreach ($categories as $cat): ?>
    <a href="/menu?category=<?= urlencode($cat) ?>"
       class="px-3 py-1.5 text-xs font-semibold rounded-full transition <?= $categoryFilter === $cat ? 'bg-navy-700 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:border-navy-700 hover:text-navy-700' ?>">
      <?= e(ucfirst($cat)) ?>
    </a>
  <?php endforeach; ?>
</div>

<?php if (empty($items)): ?>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
    <div class="text-5xl mb-3">🍽️</div>
    <h3 class="text-lg font-bold text-navy-900">Aucun article</h3>
    <p class="text-sm text-gray-400 mt-1">Le menu room service est vide.</p>
    <a href="/menu/create" class="inline-block mt-4 px-5 py-2 bg-gold-400 text-white text-sm font-semibold rounded-lg hover:bg-gold-500 transition">Ajouter un article</a>
  </div>
<?php else: ?>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    <?php foreach ($items as $item): ?>
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col <?= !$item['available'] ? 'opacity-60' : '' ?>">
        <!-- Image -->
        <div class="aspect-[4/3] bg-gray-100 relative">
          <?php if (!empty($item['image'])): ?>
            <img src="<?= e($item['image']) ?>" alt="" class="w-full h-full object-cover">
          <?php else: ?>
            <div class="w-full h-full flex items-center justify-center text-4xl text-gray-300">🍽️</div>
          <?php endif; ?>
          <!-- Available toggle -->
          <form method="POST" action="/menu/toggle/<?= (int) $item['id'] ?>" class="absolute top-2 right-2">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
            <button type="submit" class="px-2 py-1 text-[10px] font-semibold rounded-full shadow-sm transition <?= $item['available'] ? 'bg-green-500 text-white' : 'bg-gray-500 text-white' ?>">
              <?= $item['available'] ? 'Disponible' : 'Indispo' ?>
            </button>
          </form>
          <!-- Category badge -->
          <?php if ($item['category']): ?>
            <span class="absolute bottom-2 left-2 px-2 py-0.5 text-[10px] font-semibold rounded-full <?= $categoryColors[$item['category']] ?? 'bg-gray-100 text-gray-700' ?>">
              <?= e(ucfirst($item['category'])) ?>
            </span>
          <?php endif; ?>
        </div>

        <!-- Body -->
        <div class="p-4 flex flex-col flex-1">
          <div class="flex items-start justify-between gap-2">
            <h3 class="font-bold text-navy-900 text-sm leading-snug"><?= e($item['name']) ?></h3>
            <div class="font-bold text-gold-500 shrink-0"><?= format_price((float) $item['price']) ?></div>
          </div>
          <?php if ($item['description']): ?>
            <p class="text-xs text-gray-500 mt-1 line-clamp-2"><?= e($item['description']) ?></p>
          <?php endif; ?>
          <div class="mt-2 flex items-center gap-3 text-[10px] text-gray-400">
            <span>⏱️ <?= (int) $item['prep_time'] ?> min</span>
          </div>

          <div class="mt-auto pt-3 flex items-center justify-end gap-3 border-t border-gray-50 mt-3">
            <a href="/menu/edit/<?= (int) $item['id'] ?>" class="text-navy-700 hover:text-navy-900 text-xs font-semibold">Modifier</a>
            <form method="POST" action="/menu/delete/<?= (int) $item['id'] ?>" class="inline" onsubmit="return confirm('Supprimer cet article ?');">
              <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
              <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-semibold">Supprimer</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
