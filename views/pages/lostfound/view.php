<?php
/**
 * @var array  $item
 * @var string $csrf
 */

$typeLabels = [
  'lost'  => ['Perdu',   'bg-red-100 text-red-700'],
  'found' => ['Trouvé',  'bg-green-100 text-green-700'],
];
$statusLabels = [
  'open'     => ['Ouvert',   'bg-amber-100 text-amber-700'],
  'claimed'  => ['Réclamé',  'bg-blue-100 text-blue-700'],
  'returned' => ['Rendu',    'bg-green-100 text-green-700'],
  'disposed' => ['Éliminé',  'bg-gray-100 text-gray-600'],
];
$tl = $typeLabels[$item['type']]   ?? ['?', 'bg-gray-100 text-gray-600'];
$sl = $statusLabels[$item['status']] ?? ['?', 'bg-gray-100 text-gray-600'];
?>
<div class="mb-6">
  <a href="/lostfound" class="text-sm text-gray-500 hover:text-navy-700">← Retour</a>
  <div class="mt-2 flex flex-wrap items-center gap-3">
    <h1 class="text-2xl font-bold text-navy-900"><?= e($item['item_name']) ?></h1>
    <span class="inline-block px-2.5 py-0.5 text-xs rounded-full <?= e($tl[1]) ?>"><?= e($tl[0]) ?></span>
    <span class="inline-block px-2.5 py-0.5 text-xs rounded-full <?= e($sl[1]) ?>"><?= e($sl[0]) ?></span>
  </div>
  <p class="text-sm text-gray-500 mt-1">Signalé le <?= e(format_date($item['created_at'], 'd/m/Y à H:i')) ?></p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
  <!-- Main column -->
  <div class="lg:col-span-2 space-y-4">
    <?php if (!empty($item['photo'])): ?>
      <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
        <img src="<?= e($item['photo']) ?>" alt="<?= e($item['item_name']) ?>" class="w-full max-h-80 object-contain rounded-lg">
      </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-3">Description</h2>
      <p class="text-sm text-gray-700 whitespace-pre-line"><?= e($item['description'] ?: 'Aucune description fournie.') ?></p>
    </div>

    <?php if (!empty($item['notes'])): ?>
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h2 class="text-sm font-bold text-navy-900 mb-3">Historique / notes</h2>
        <p class="text-sm text-gray-600 whitespace-pre-line"><?= e($item['notes']) ?></p>
      </div>
    <?php endif; ?>
  </div>

  <!-- Sidebar -->
  <div class="space-y-4">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-3">Informations</h2>
      <dl class="space-y-3 text-sm">
        <div>
          <dt class="text-xs text-gray-400 uppercase">Chambre</dt>
          <dd class="font-medium text-gray-800"><?= e($item['room_number'] ?? '—') ?></dd>
        </div>
        <?php if (!empty($item['location'])): ?>
          <div>
            <dt class="text-xs text-gray-400 uppercase">Lieu précis</dt>
            <dd class="font-medium text-gray-800"><?= e($item['location']) ?></dd>
          </div>
        <?php endif; ?>
        <?php if (!empty($item['reported_by'])): ?>
          <div>
            <dt class="text-xs text-gray-400 uppercase">Signalé par</dt>
            <dd class="font-medium text-gray-800"><?= e($item['reported_by']) ?></dd>
          </div>
        <?php endif; ?>
        <?php if (!empty($item['contact'])): ?>
          <div>
            <dt class="text-xs text-gray-400 uppercase">Contact</dt>
            <dd class="font-medium text-gray-800"><?= e($item['contact']) ?></dd>
          </div>
        <?php endif; ?>
      </dl>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-3">
      <h2 class="text-sm font-bold text-navy-900">Actions</h2>

      <?php if ($item['status'] === 'open'): ?>
        <!-- Claim form (requires claimant info) -->
        <form method="POST" action="/lostfound/claim/<?= (int) $item['id'] ?>" class="space-y-2">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="action" value="claim">
          <input type="text" name="claimant_name" placeholder="Nom du réclamant *" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-navy-700">
          <input type="text" name="claimant_contact" placeholder="Téléphone / email" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-navy-700">
          <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700">Marquer comme réclamé</button>
        </form>
      <?php endif; ?>

      <?php if ($item['status'] === 'claimed'): ?>
        <form method="POST" action="/lostfound/claim/<?= (int) $item['id'] ?>">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="action" value="return">
          <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700">Marquer comme rendu</button>
        </form>
        <form method="POST" action="/lostfound/claim/<?= (int) $item['id'] ?>">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="action" value="reopen">
          <button type="submit" class="w-full px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-900">Rouvrir le dossier</button>
        </form>
      <?php endif; ?>

      <?php if (in_array($item['status'], ['open', 'claimed'], true)): ?>
        <form method="POST" action="/lostfound/claim/<?= (int) $item['id'] ?>" onsubmit="return confirm('Marquer cet objet comme éliminé / jeté ?');">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="action" value="dispose">
          <button type="submit" class="w-full px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 rounded-lg">Éliminer / jeter</button>
        </form>
      <?php endif; ?>

      <?php if (in_array($item['status'], ['returned', 'disposed'], true)): ?>
        <form method="POST" action="/lostfound/claim/<?= (int) $item['id'] ?>">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="action" value="reopen">
          <button type="submit" class="w-full px-4 py-2 text-sm font-semibold text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">Rouvrir le dossier</button>
        </form>
      <?php endif; ?>

      <div class="pt-2 border-t border-gray-100 flex flex-col gap-2">
        <a href="/lostfound/edit/<?= (int) $item['id'] ?>" class="w-full text-center px-4 py-2 text-sm font-semibold rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200">Modifier</a>
        <a href="/lostfound/delete/<?= (int) $item['id'] ?>" class="w-full text-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-semibold rounded-lg">Supprimer définitivement</a>
      </div>
    </div>
  </div>
</div>
