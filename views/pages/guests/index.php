<?php /** @var array $guests, $search, $title */ ?>
<?php
$search = trim((string) ($search ?? ''));
?>
<div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
  <div>
    <h1 class="text-2xl font-bold text-navy-900">Clients</h1>
    <p class="text-sm text-gray-500 mt-1"><?= count($guests) ?> client(s) enregistré(s)</p>
  </div>
  <a href="/guests/create" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gold-400 text-white rounded-lg font-semibold hover:bg-gold-500 transition shadow-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Nouveau client
  </a>
</div>

<form method="GET" action="/guests" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 mb-6 flex flex-col sm:flex-row gap-3">
  <div class="flex-1 relative">
    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Rechercher par nom, email ou téléphone…" class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-navy-700 outline-none">
  </div>
  <label class="inline-flex items-center gap-2 px-3 text-sm text-gray-600">
    <input type="checkbox" name="vip" value="1" <?= input('vip') === '1' ? 'checked' : '' ?> onchange="this.form.submit()">
    VIP uniquement
  </label>
  <?php if ($search || input('vip') === '1'): ?>
    <a href="/guests" class="px-4 py-2 text-sm text-gray-500 hover:text-navy-700">Réinitialiser</a>
  <?php endif; ?>
</form>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50/50">
        <tr class="text-left text-xs text-gray-500 uppercase tracking-wide">
          <th class="px-4 py-3">Client</th>
          <th class="px-4 py-3">Contact</th>
          <th class="px-4 py-3">Pays</th>
          <th class="px-4 py-3 text-center">Séjours</th>
          <th class="px-4 py-3 text-right">Total dépensé</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        <?php if (empty($guests)): ?>
          <tr>
            <td colspan="6" class="px-4 py-12 text-center text-gray-400">
              <div class="text-4xl mb-2">👤</div>
              Aucun client trouvé.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($guests as $g): ?>
            <tr class="hover:bg-gray-50/50">
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-full bg-navy-700 text-white flex items-center justify-center text-xs font-bold shrink-0">
                    <?= strtoupper(substr($g['name'], 0, 1)) ?>
                  </div>
                  <div class="min-w-0">
                    <a href="/guests/view/<?= (int) $g['id'] ?>" class="font-medium text-navy-900 hover:underline"><?= e($g['name']) ?></a>
                    <?php if (!empty($g['vip'])): ?>
                      <span class="ml-1 inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[10px] font-bold bg-gold-400/10 text-gold-600 rounded">★ VIP</span>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 text-gray-600">
                <?php if (!empty($g['email'])): ?><div class="truncate max-w-[180px]"><?= e($g['email']) ?></div><?php endif; ?>
                <?php if (!empty($g['phone'])): ?><div class="text-xs text-gray-400"><?= e($g['phone']) ?></div><?php endif; ?>
              </td>
              <td class="px-4 py-3 text-gray-600"><?= e($g['country'] ?: '—') ?></td>
              <td class="px-4 py-3 text-center font-medium text-gray-700"><?= (int) ($g['stays_count'] ?? $g['total_stays']) ?></td>
              <td class="px-4 py-3 text-right font-semibold text-navy-900"><?= format_price((float) $g['total_spent']) ?></td>
              <td class="px-4 py-3 text-right whitespace-nowrap">
                <a href="/guests/view/<?= (int) $g['id'] ?>" class="text-navy-700 hover:underline text-xs font-medium">Voir</a>
                <span class="text-gray-300">·</span>
                <a href="/guests/edit/<?= (int) $g['id'] ?>" class="text-gray-600 hover:text-navy-700 text-xs font-medium">Modifier</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
