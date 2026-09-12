<?php
/**
 * @var array  $item
 * @var array  $rooms
 * @var bool   $isEdit
 * @var string $error
 * @var string $csrf
 */

$typeLabels = ['lost' => 'Perdu', 'found' => 'Trouvé'];
?>
<div class="mb-6">
  <a href="/lostfound" class="text-sm text-gray-500 hover:text-navy-700">← Retour</a>
  <h1 class="text-2xl font-bold text-navy-900 mt-2"><?= $isEdit ? 'Modifier l\'objet' : 'Signaler un objet' ?></h1>
</div>

<div class="max-w-2xl bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
  <?php if ($error): ?>
    <div class="mb-4 px-4 py-2.5 rounded-lg bg-red-50 text-red-700 text-sm"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="<?= $isEdit ? '/lostfound/edit/' . (int) $item['id'] : '/lostfound/create' ?>" enctype="multipart/form-data" class="space-y-4">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
      <div class="grid grid-cols-2 gap-2">
        <?php foreach ($typeLabels as $key => $label): ?>
          <label class="flex items-center justify-center gap-2 px-4 py-2.5 border rounded-lg cursor-pointer hover:bg-gray-50 transition
            <?= ($item['type'] ?? '') === $key ? 'border-navy-700 bg-navy-50' : 'border-gray-200' ?>">
            <input type="radio" name="type" value="<?= e($key) ?>" <?= ($item['type'] ?? '') === $key ? 'checked' : '' ?> class="w-4 h-4 text-navy-700 focus:ring-navy-700">
            <span class="text-sm font-medium text-gray-700"><?= e($label) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Nom de l'objet *</label>
      <input type="text" name="item_name" value="<?= e($item['item_name']) ?>" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-navy-700 focus:border-transparent" placeholder="Ex: Téléphone Samsung noir">
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
      <textarea name="description" rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-navy-700 focus:border-transparent" placeholder="Couleur, marque, état..."><?= e($item['description']) ?></textarea>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Chambre</label>
        <select name="room_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-navy-700 focus:border-transparent">
          <option value="">— Aucune / inconnue —</option>
          <?php foreach ($rooms as $r): ?>
            <option value="<?= (int) $r['id'] ?>" <?= $item['room_id'] == $r['id'] ? 'selected' : '' ?>>Chambre <?= e($r['number']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Lieu précis</label>
        <input type="text" name="location" value="<?= e($item['location']) ?>" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-navy-700 focus:border-transparent" placeholder="Ex: Sous le lit, réception...">
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Photo</label>
      <?php if (!empty($item['photo'])): ?>
        <div class="mb-2 w-24 h-24 rounded-lg overflow-hidden border border-gray-200">
          <img src="<?= e($item['photo']) ?>" alt="" class="w-full h-full object-cover">
        </div>
      <?php endif; ?>
      <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
      <p class="mt-1 text-xs text-gray-400">JPG, PNG ou WEBP · 2 Mo max · facultatif</p>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Signalé par</label>
        <input type="text" name="reported_by" value="<?= e($item['reported_by']) ?>" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-navy-700 focus:border-transparent" placeholder="Nom du client ou du personnel">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Contact</label>
        <input type="text" name="contact" value="<?= e($item['contact']) ?>" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-navy-700 focus:border-transparent" placeholder="Téléphone ou email">
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Notes internes</label>
      <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-navy-700 focus:border-transparent"><?= e($item['notes']) ?></textarea>
    </div>

    <div class="flex gap-2 pt-2">
      <button type="submit" class="px-5 py-2 bg-navy-900 text-white text-sm font-semibold rounded-lg hover:bg-navy-800"><?= $isEdit ? 'Enregistrer' : 'Signaler' ?></button>
      <a href="/lostfound" class="px-5 py-2 text-sm text-gray-600 hover:text-gray-900">Annuler</a>
    </div>
  </form>
</div>
