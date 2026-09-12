<?php /** @var array $room, $menuByCategory, $success, $error, $title */ ?>
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
    <h1 class="text-xl font-bold"><?= e($room['hotel_name']) ?></h1>
    <p class="text-white/60 text-sm mt-1">Chambre N°<?= e($room['number']) ?> · Étage <?= e($room['floor']) ?></p>
  </div>

  <?php if ($success): ?>
    <div class="max-w-md mx-auto mt-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm text-center"><?= e($success) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="max-w-md mx-auto mt-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm text-center"><?= e($error) ?></div>
  <?php endif; ?>

  <!-- Quick Services -->
  <div class="max-w-md mx-auto mt-6 px-4">
    <h2 class="text-sm font-bold text-navy-900 mb-3">Services rapides</h2>
    <div class="grid grid-cols-2 gap-3">
      <form method="POST" action="/room/<?= e($room['qr_token']) ?>">
        <input type="hidden" name="action" value="service">
        <input type="hidden" name="service_type" value="housekeeping">
        <button class="w-full p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-gold-400 transition text-center">
          <div class="text-2xl mb-1">🧹</div>
          <div class="text-sm font-medium text-gray-700">Ménage</div>
        </button>
      </form>
      <form method="POST" action="/room/<?= e($room['qr_token']) ?>">
        <input type="hidden" name="action" value="service">
        <input type="hidden" name="service_type" value="towels">
        <button class="w-full p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-gold-400 transition text-center">
          <div class="text-2xl mb-1">🧖</div>
          <div class="text-sm font-medium text-gray-700">Serviettes</div>
        </button>
      </form>
      <form method="POST" action="/room/<?= e($room['qr_token']) ?>">
        <input type="hidden" name="action" value="service">
        <input type="hidden" name="service_type" value="maintenance">
        <button class="w-full p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-gold-400 transition text-center">
          <div class="text-2xl mb-1">🔧</div>
          <div class="text-sm font-medium text-gray-700">Maintenance</div>
        </button>
      </form>
      <form method="POST" action="/room/<?= e($room['qr_token']) ?>">
        <input type="hidden" name="action" value="service">
        <input type="hidden" name="service_type" value="reception">
        <button class="w-full p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-gold-400 transition text-center">
          <div class="text-2xl mb-1">🛎️</div>
          <div class="text-sm font-medium text-gray-700">Réception</div>
        </button>
      </form>
    </div>
  </div>

  <!-- Room Service Menu -->
  <?php if (!empty($menuByCategory)): ?>
  <div class="max-w-md mx-auto mt-8 px-4">
    <h2 class="text-sm font-bold text-navy-900 mb-3">Room Service</h2>
    <div class="space-y-4">
      <?php foreach ($menuByCategory as $category => $items): ?>
        <div>
          <h3 class="text-xs font-semibold text-gray-400 uppercase mb-2"><?= e($category) ?></h3>
          <div class="space-y-2">
            <?php foreach ($items as $item): ?>
              <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100 flex items-center gap-3">
                <?php if ($item['image']): ?>
                  <img src="<?= e($item['image']) ?>" alt="" class="w-12 h-12 rounded-lg object-cover shrink-0">
                <?php endif; ?>
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-medium text-gray-800"><?= e($item['name']) ?></div>
                  <div class="text-xs text-gray-400"><?= e($item['description']) ?></div>
                </div>
                <div class="text-sm font-bold text-gold-500"><?= format_price($item['price']) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Order form -->
    <form method="POST" action="/room/<?= e($room['qr_token']) ?>" class="mt-4 space-y-3">
      <input type="hidden" name="action" value="order">
      <input type="hidden" name="items" id="order-items" value="[]">
      <input type="text" name="guest_name" placeholder="Votre nom" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm">
      <input type="text" name="guest_phone" placeholder="Téléphone (optionnel)" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm">
      <textarea name="notes" placeholder="Notes (allergies, préférences...)" rows="2" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-sm"></textarea>
      <button type="submit" class="w-full py-2.5 rounded-lg bg-gold-400 text-white font-semibold hover:bg-gold-500 transition">Commander</button>
    </form>
  </div>
  <?php endif; ?>

  <!-- Lost & Found — link to dedicated QR page -->
  <div class="max-w-md mx-auto mt-8 px-4">
    <a href="/found/<?= e($room['qr_token']) ?>"
       class="block bg-white rounded-xl p-4 border border-gray-100 hover:border-gold-400 transition text-center">
      <div class="text-3xl mb-2">🔍</div>
      <div class="text-sm font-bold text-navy-900">Objet perdu ou trouvé ?</div>
      <div class="text-xs text-gray-400 mt-1">Signalez-le ici (avec photo)</div>
    </a>
  </div>

  <!-- Hotel Info -->
  <div class="max-w-md mx-auto mt-8 px-4">
    <h2 class="text-sm font-bold text-navy-900 mb-3">Informations hôtel</h2>
    <div class="bg-white rounded-xl p-4 border border-gray-100 space-y-2 text-sm">
      <?php if ($room['wifi_ssid']): ?>
        <div class="flex items-center justify-between">
          <span class="text-gray-500">📶 WiFi</span>
          <span class="font-medium"><?= e($room['wifi_ssid']) ?> <?= $room['wifi_password'] ? '· ' . e($room['wifi_password']) : '' ?></span>
        </div>
      <?php endif; ?>
      <div class="flex items-center justify-between">
        <span class="text-gray-500">🛎️ Check-in</span>
        <span class="font-medium"><?= e($room['checkin_time']) ?></span>
      </div>
      <div class="flex items-center justify-between">
        <span class="text-gray-500">🚪 Check-out</span>
        <span class="font-medium"><?= e($room['checkout_time']) ?></span>
      </div>
      <?php if ($room['hotel_phone']): ?>
        <div class="flex items-center justify-between">
          <span class="text-gray-500">📞 Réception</span>
          <a href="tel:<?= e($room['hotel_phone']) ?>" class="font-medium text-navy-700"><?= e($room['hotel_phone']) ?></a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <p class="text-center text-xs text-gray-400 mt-8">Propulsé par RoomScan</p>
</body>
</html>
