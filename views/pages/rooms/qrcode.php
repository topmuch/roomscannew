<?php /** @var array $room, $qrUrl, $title */ 
$publicUrl = APP_URL . '/room/' . $room['qr_token'];
?>
<div class="mb-6">
  <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
    <a href="/rooms" class="hover:text-navy-700">Chambres</a>
    <span>/</span>
    <span>QR Code</span>
  </div>
  <h1 class="text-2xl font-bold text-navy-900">QR Code — Chambre N° <?= e($room['number']) ?></h1>
  <p class="text-sm text-gray-500 mt-1">À imprimer et à afficher dans la chambre pour que les clients accèdent aux services.</p>
</div>

<div class="grid lg:grid-cols-2 gap-6">
  <!-- QR Card -->
  <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
    <div class="text-center">
      <div class="inline-block p-4 bg-white border-2 border-gray-100 rounded-2xl mb-4">
        <img src="<?= e($qrUrl) ?>" alt="QR Code Chambre <?= e($room['number']) ?>" width="300" height="300" class="rounded-lg" id="qr-image">
      </div>
      <div class="text-3xl font-bold text-navy-900 mb-1"><?= e($room['hotel_name']) ?></div>
      <div class="text-sm text-gray-500">Chambre N° <?= e($room['number']) ?> · <?= e($room['type_name'] ?? 'Sans type') ?></div>
      <div class="mt-2 text-xs text-gray-400">Étage <?= (int) $room['floor'] ?> · <?= format_price((float) $room['price']) ?> /nuit</div>
    </div>

    <div class="mt-6 flex gap-3">
      <button onclick="window.print()" class="flex-1 py-2.5 rounded-lg bg-gold-400 text-white text-sm font-semibold hover:bg-gold-500 transition flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        Imprimer
      </button>
      <a href="<?= e($qrUrl) ?>" download class="flex-1 py-2.5 rounded-lg bg-white border border-gray-200 text-navy-700 text-sm font-semibold hover:bg-gray-50 transition flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Télécharger
      </a>
    </div>
  </div>

  <!-- Info -->
  <div class="space-y-4">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-4">URL publique</h2>
      <div class="bg-gray-50 border border-gray-100 rounded-lg p-3 text-sm">
        <div class="text-xs text-gray-400 uppercase mb-1">Lien scanné par le QR code</div>
        <a href="<?= e($publicUrl) ?>" target="_blank" class="text-navy-700 hover:underline break-all"><?= e($publicUrl) ?></a>
      </div>
      <button onclick="navigator.clipboard.writeText('<?= e($publicUrl) ?>'); this.innerText='✓ Copié'" class="mt-3 w-full py-2 rounded-lg bg-navy-700 text-white text-sm font-medium hover:bg-navy-800 transition">Copier le lien</button>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-3">Token</h2>
      <code class="block bg-gray-50 border border-gray-100 rounded p-2 text-xs text-navy-700 break-all"><?= e($room['qr_token']) ?></code>
      <p class="mt-3 text-xs text-gray-500">Ce token unique est généré automatiquement et ne peut pas être deviné. Le QR code pointe vers la page publique de la chambre.</p>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-sm font-bold text-navy-900 mb-3">Services accessibles via QR</h2>
      <ul class="space-y-2 text-sm text-gray-600">
        <li class="flex items-center gap-2"><span>🛎️</span> Réception</li>
        <li class="flex items-center gap-2"><span>🧹</span> Ménage / serviettes</li>
        <li class="flex items-center gap-2"><span>🔧</span> Maintenance</li>
        <li class="flex items-center gap-2"><span>🍔</span> Room Service (menu)</li>
        <li class="flex items-center gap-2"><span>📦</span> Objets perdus / trouvés</li>
        <li class="flex items-center gap-2"><span>📶</span> Infos WiFi</li>
      </ul>
    </div>

    <!-- QR Code Housekeeping -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gold-400/30">
      <h2 class="text-sm font-bold text-navy-900 mb-2">🧹 QR Code Nettoyage (Staff)</h2>
      <p class="text-xs text-gray-500 mb-3">À scanner par les femmes de ménage pour démarrer le nettoyage.</p>
      <div class="text-center">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode(APP_URL . '/clean/' . $room['qr_token']) ?>"
             alt="QR Nettoyage" class="mx-auto rounded-lg border-2 border-gray-100" width="200" height="200">
      </div>
      <div class="mt-2 text-xs text-gray-400 break-all text-center"><?= e(APP_URL . '/clean/' . $room['qr_token']) ?></div>
      <a href="<?= e(APP_URL . '/clean/' . $room['qr_token']) ?>" target="_blank"
         class="mt-2 block text-center py-2 rounded-lg bg-gold-400 text-white text-sm font-medium hover:bg-gold-500">Voir la page</a>
    </div>

    <!-- QR Code Lost & Found -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-blue-200">
      <h2 class="text-sm font-bold text-navy-900 mb-2">🔍 QR Code Objets Trouvés</h2>
      <p class="text-xs text-gray-500 mb-3">À scanner pour signaler un objet perdu ou trouvé.</p>
      <div class="text-center">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode(APP_URL . '/found/' . $room['qr_token']) ?>"
             alt="QR Objets trouvés" class="mx-auto rounded-lg border-2 border-gray-100" width="200" height="200">
      </div>
      <div class="mt-2 text-xs text-gray-400 break-all text-center"><?= e(APP_URL . '/found/' . $room['qr_token']) ?></div>
      <a href="<?= e(APP_URL . '/found/' . $room['qr_token']) ?>" target="_blank"
         class="mt-2 block text-center py-2 rounded-lg bg-navy-700 text-white text-sm font-medium hover:bg-navy-800">Voir la page</a>
    </div>

    <div class="flex gap-3">
      <a href="/rooms/edit/<?= (int) $room['id'] ?>" class="flex-1 text-center py-2.5 rounded-lg bg-navy-700 text-white text-sm font-medium hover:bg-navy-800">Modifier</a>
      <a href="/rooms" class="flex-1 text-center py-2.5 rounded-lg border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-50">Retour</a>
    </div>
  </div>
</div>

<style media="print">
  body * { visibility: hidden; }
  #qr-image, #qr-image * { visibility: visible; }
  #qr-image { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); }
</style>
