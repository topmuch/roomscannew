<?php /** @var array $room, $activeTask, $success, $error, $title */ ?>
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
    <h1 class="text-xl font-bold">🧹 Nettoyage</h1>
    <p class="text-white/60 text-sm mt-1">
      Chambre N°<?= e($room['number']) ?> · <?= e($room['hotel_name']) ?>
    </p>
    <?php if ($activeTask): ?>
      <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs bg-amber-400 text-amber-900 font-semibold">
        En cours
      </span>
    <?php elseif ($room['status'] === 'available'): ?>
      <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs bg-green-500 text-white font-semibold">
        Disponible
      </span>
    <?php elseif ($room['status'] === 'occupied'): ?>
      <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs bg-red-500 text-white font-semibold">
        Occupée
      </span>
    <?php elseif ($room['status'] === 'cleaning'): ?>
      <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs bg-amber-500 text-white font-semibold">
        En nettoyage
      </span>
    <?php elseif ($room['status'] === 'maintenance'): ?>
      <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs bg-orange-500 text-white font-semibold">
        Maintenance
      </span>
    <?php endif; ?>
  </div>

  <?php if ($success): ?>
    <div class="max-w-md mx-auto mt-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm text-center"><?= e($success) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="max-w-md mx-auto mt-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm text-center"><?= e($error) ?></div>
  <?php endif; ?>

  <div class="max-w-md mx-auto mt-6 px-4">

    <?php if (!$activeTask && $room['status'] !== 'maintenance'): ?>
      <!-- Start cleaning button -->
      <form method="POST" action="/clean/<?= e($room['qr_token']) ?>">
        <input type="hidden" name="action" value="start_cleaning">
        <button type="submit"
          class="w-full py-4 rounded-xl bg-gold-400 text-white font-bold text-lg hover:bg-gold-500 transition shadow-lg">
          🧹 Démarrer le nettoyage
        </button>
      </form>

      <!-- Flag maintenance -->
      <div class="mt-4">
        <button onclick="document.getElementById('maint-form').classList.toggle('hidden')"
          class="w-full py-3 rounded-xl bg-white border border-orange-300 text-orange-600 font-medium hover:bg-orange-50 transition">
          🔧 Signaler un problème
        </button>
        <form id="maint-form" method="POST" action="/clean/<?= e($room['qr_token']) ?>" class="hidden mt-2 space-y-2">
          <input type="hidden" name="action" value="flag_maintenance">
          <textarea name="issue" rows="2" placeholder="Décrivez le problème (clim, plomberie, électrique...)"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm" required></textarea>
          <button type="submit" class="w-full py-2 rounded-lg bg-orange-500 text-white text-sm font-medium">Confirmer le problème</button>
        </form>
      </div>

    <?php elseif ($activeTask): ?>
      <!-- Checklist form -->
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <h2 class="text-sm font-bold text-navy-900 mb-4">Checklist de nettoyage</h2>
        <p class="text-xs text-gray-400 mb-4">Cochez chaque tâche une fois terminée.</p>

        <form method="POST" action="/clean/<?= e($room['qr_token']) ?>" class="space-y-3">
          <input type="hidden" name="action" value="submit_checklist">
          <input type="hidden" name="hk_id" value="<?= (int)$activeTask['id'] ?>">

          <?php
            $checklist = json_decode($activeTask['checklist'] ?? '{}', true) ?: [];
            $items = [
              'bed_made' => '🛏️ Lit fait',
              'towels_changed' => '🧖 Serviettes changées',
              'floor_cleaned' => '🧹 Sol nettoyé',
              'bathroom_cleaned' => '🚿 Salle de bain nettoyée',
              'trash_emptied' => '🗑️ Poubelle vidée',
              'dusting_done' => '💨 Poussière faite',
              'windows_cleaned' => '🪟 Vitres nettoyées',
              'amenities_replaced' => '🧴 Produits remplacés',
            ];
            foreach ($items as $key => $label):
              $checked = $checklist[$key] ?? false;
          ?>
            <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-gold-400 cursor-pointer transition">
              <input type="checkbox" name="<?= $key ?>" value="1" <?= $checked ? 'checked' : '' ?>
                class="w-5 h-5 rounded text-gold-400 focus:ring-gold-400">
              <span class="text-sm text-gray-700"><?= $label ?></span>
            </label>
          <?php endforeach; ?>

          <!-- Flag issue -->
          <div class="pt-2">
            <label class="text-xs font-medium text-gray-500">Problème signalé (optionnel)</label>
            <input type="text" name="flagged_issue" value="<?= e($activeTask['flagged_issue'] ?? '') ?>"
              placeholder="Ex: Draps manquants, clim ne fonctionne pas..."
              class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 text-sm">
          </div>

          <!-- Notes -->
          <div>
            <label class="text-xs font-medium text-gray-500">Notes</label>
            <textarea name="notes" rows="2" placeholder="Notes additionnelles..."
              class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 text-sm"><?= e($activeTask['notes'] ?? '') ?></textarea>
          </div>

          <button type="submit"
            class="w-full py-3 rounded-xl bg-gold-400 text-white font-bold hover:bg-gold-500 transition shadow-lg">
            ✅ Valider la checklist
          </button>
        </form>
      </div>

      <!-- Flag maintenance from checklist -->
      <div class="mt-4">
        <button onclick="document.getElementById('maint-form2').classList.toggle('hidden')"
          class="w-full py-2 rounded-lg bg-white border border-orange-300 text-orange-600 text-sm font-medium hover:bg-orange-50 transition">
          🔧 Signaler un problème maintenance
        </button>
        <form id="maint-form2" method="POST" action="/clean/<?= e($room['qr_token']) ?>" class="hidden mt-2 space-y-2">
          <input type="hidden" name="action" value="flag_maintenance">
          <textarea name="issue" rows="2" placeholder="Décrivez le problème..."
            class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm" required></textarea>
          <button type="submit" class="w-full py-2 rounded-lg bg-orange-500 text-white text-sm font-medium">Confirmer</button>
        </form>
      </div>

    <?php elseif ($room['status'] === 'maintenance'): ?>
      <div class="bg-orange-50 rounded-xl p-6 text-center border border-orange-200">
        <div class="text-4xl mb-2">🔧</div>
        <h2 class="font-bold text-orange-700">Cette chambre est en maintenance</h2>
        <p class="text-sm text-orange-600 mt-1">Le nettoyage n'est pas disponible jusqu'à résolution du problème.</p>
      </div>
    <?php endif; ?>

  </div>

  <p class="text-center text-xs text-gray-400 mt-8">Propulsé par RoomScan</p>
</body>
</html>
