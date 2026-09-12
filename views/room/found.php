<?php /** @var array $room, $recentItems, $success, $error, $title */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { theme: { extend: { colors: {
      navy: { 900:'#0c1f4a',800:'#14306e',700:'#1e3a8a'},
      gold: { 400:'#ca8a04',500:'#d4a017'}
    }}} 
  </script>
</head>
<body class="bg-gray-50 min-h-screen pb-12">

  <!-- Header -->
  <div class="bg-navy-900 text-white px-4 py-6 text-center">
    <div class="w-12 h-12 rounded-xl bg-white mx-auto mb-3 flex items-center justify-center">
      <span class="text-2xl font-bold text-navy-900">R</span>
    </div>
    <h1 class="text-xl font-bold">🔍 Objets trouvés</h1>
    <p class="text-white/60 text-sm mt-1">
      <?= e($room['hotel_name']) ?> · Chambre N°<?= e($room['number']) ?>
    </p>
  </div>

  <?php if ($success): ?>
    <div class="max-w-md mx-auto mt-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm text-center"><?= e($success) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="max-w-md mx-auto mt-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm text-center"><?= e($error) ?></div>
  <?php endif; ?>

  <!-- Report form -->
  <div class="max-w-md mx-auto mt-6 px-4">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-4">Signaler un objet</h2>

      <form method="POST" action="/found/<?= e($room['qr_token']) ?>" enctype="multipart/form-data" class="space-y-3">
        <!-- Type -->
        <div class="grid grid-cols-2 gap-3">
          <label class="flex items-center justify-center gap-2 p-3 rounded-lg border-2 border-gold-400 bg-gold-400/5 cursor-pointer">
            <input type="radio" name="lf_type" value="found" checked class="text-gold-400">
            <span class="text-sm font-medium">📦 Objet trouvé</span>
          </label>
          <label class="flex items-center justify-center gap-2 p-3 rounded-lg border-2 border-gray-200 cursor-pointer">
            <input type="radio" name="lf_type" value="lost" class="text-gold-400">
            <span class="text-sm font-medium">❓ Objet perdu</span>
          </label>
        </div>

        <!-- Item name -->
        <div>
          <label class="text-xs font-medium text-gray-500">Nom de l'objet *</label>
          <input type="text" name="item_name" required
            placeholder="Ex: iPhone 15 noir, lunettes, portefeuille..."
            class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 text-sm">
        </div>

        <!-- Description -->
        <div>
          <label class="text-xs font-medium text-gray-500">Description</label>
          <textarea name="description" rows="2"
            placeholder="Couleur, marque, état, détails distinctifs..."
            class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 text-sm"></textarea>
        </div>

        <!-- Photo -->
        <div>
          <label class="text-xs font-medium text-gray-500">Photo (optionnel)</label>
          <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
            class="w-full mt-1 text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-navy-700 file:text-white file:font-medium file:cursor-pointer">
        </div>

        <!-- Location -->
        <div>
          <label class="text-xs font-medium text-gray-500">Lieu précis</label>
          <input type="text" name="location" value="Chambre N°<?= e($room['number']) ?>"
            class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 text-sm">
        </div>

        <!-- Reported by -->
        <div>
          <label class="text-xs font-medium text-gray-500">Votre nom</label>
          <input type="text" name="reported_by"
            placeholder="Ex: Awa Diop / Réception"
            class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 text-sm">
        </div>

        <!-- Contact -->
        <div>
          <label class="text-xs font-medium text-gray-500">Contact (téléphone ou email)</label>
          <input type="text" name="contact"
            placeholder="Ex: +221 77 123 45 67"
            class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 text-sm">
        </div>

        <!-- Notes -->
        <div>
          <label class="text-xs font-medium text-gray-500">Notes</label>
          <textarea name="notes" rows="2"
            placeholder="Informations complémentaires..."
            class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 text-sm"></textarea>
        </div>

        <button type="submit"
          class="w-full py-3 rounded-xl bg-gold-400 text-white font-bold hover:bg-gold-500 transition shadow-lg">
          📤 Signaler
        </button>
      </form>
    </div>

    <!-- Recent items for this room -->
    <?php if (!empty($recentItems)): ?>
      <div class="mt-6">
        <h3 class="text-xs font-bold text-gray-400 uppercase mb-2">Récents — Chambre N°<?= e($room['number']) ?></h3>
        <div class="space-y-2">
          <?php foreach ($recentItems as $item): ?>
            <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100 flex items-center gap-3">
              <?php if ($item['photo']): ?>
                <img src="<?= e($item['photo']) ?>" alt="" class="w-12 h-12 rounded-lg object-cover shrink-0">
              <?php else: ?>
                <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                  <span class="text-2xl"><?= $item['type'] === 'found' ? '📦' : '❓' ?></span>
                </div>
              <?php endif; ?>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <span class="text-sm font-medium text-gray-800"><?= e($item['item_name']) ?></span>
                  <?php if ($item['type'] === 'found'): ?>
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-green-100 text-green-700 font-medium">Trouvé</span>
                  <?php else: ?>
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700 font-medium">Perdu</span>
                  <?php endif; ?>
                </div>
                <div class="text-xs text-gray-400 mt-0.5">
                  <?= e($item['description']) ?> · <?= format_date($item['created_at'], 'd/m H:i') ?>
                </div>
              </div>
              <span class="text-[10px] px-2 py-0.5 rounded-full
                <?= $item['status'] === 'open' ? 'bg-blue-100 text-blue-700' : '' ?>
                <?= $item['status'] === 'claimed' ? 'bg-green-100 text-green-700' : '' ?>
                <?= $item['status'] === 'returned' ? 'bg-gray-100 text-gray-600' : '' ?>
                font-medium"><?= e($item['status']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <p class="text-center text-xs text-gray-400 mt-8">Propulsé par RoomScan</p>
</body>
</html>
